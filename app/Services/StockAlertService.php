<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockAlert;
use App\Models\StockBatch;
use App\Models\ProductVariants;
use App\Models\SemiFinishedProduct;
use App\Models\ReorderSuggestion;
use App\Models\ProductionHistory;
use Carbon\Carbon;

class StockAlertService
{
    /**
     * Check all stock levels for a store and create/update alerts.
     */
    public static function checkAllStockLevels(int $storeId): void
    {
        // ── Raw Materials (Stock) ──
        $stocks = Stock::where('store_id', $storeId)->get();
        foreach ($stocks as $stock) {
            self::evaluateStockItem($storeId, $stock, 'App\\Models\\Stock', $stock->unit_qty, $stock->min_stock, $stock->reorder_point);
        }

        // ── Finished Goods (ProductVariants) ──
        $variants = ProductVariants::whereHas('product', fn($q) => $q->where('store_id', $storeId))
            ->with('product')
            ->get();
        foreach ($variants as $variant) {
            self::evaluateStockItem($storeId, $variant, 'App\\Models\\ProductVariants', $variant->quantity, $variant->min_stock, null);
        }

        // ── Semi-Finished Products ──
        $semiFinished = SemiFinishedProduct::where('store_id', $storeId)->get();
        foreach ($semiFinished as $sf) {
            self::evaluateStockItem($storeId, $sf, 'App\\Models\\SemiFinishedProduct', $sf->current_qty, $sf->min_stock, null);
        }
    }

    /**
     * Evaluate a single stock item and create/resolve alerts.
     */
    private static function evaluateStockItem(int $storeId, $model, string $type, float $currentQty, ?float $minStock, ?float $reorderPoint): void
    {
        $existingAlerts = StockAlert::where('store_id', $storeId)
            ->where('alertable_type', $type)
            ->where('alertable_id', $model->id)
            ->whereIn('status', ['active', 'acknowledged'])
            ->get()
            ->keyBy('alert_type');

        $alertsNeeded = [];

        if ($currentQty <= 0) {
            $alertsNeeded[StockAlert::TYPE_OUT_OF_STOCK] = $minStock ?? 1;
        } elseif ($minStock && $currentQty <= $minStock) {
            $alertsNeeded[StockAlert::TYPE_LOW_STOCK] = $minStock;
        }

        if ($reorderPoint && $currentQty > 0 && $currentQty <= $reorderPoint) {
            $alertsNeeded[StockAlert::TYPE_REORDER_POINT] = $reorderPoint;
        }

        // Create new alerts
        foreach ($alertsNeeded as $alertType => $threshold) {
            if (!$existingAlerts->has($alertType)) {
                StockAlert::create([
                    'alertable_type'     => $type,
                    'alertable_id'       => $model->id,
                    'alert_type'         => $alertType,
                    'current_quantity'   => $currentQty,
                    'threshold_quantity' => $threshold,
                    'status'             => 'active',
                    'store_id'           => $storeId,
                ]);
            } else {
                // Update current quantity on existing alert
                $existingAlerts[$alertType]->update(['current_quantity' => $currentQty]);
            }
        }

        // Resolve alerts that no longer apply
        foreach ($existingAlerts as $alertType => $alert) {
            if (!isset($alertsNeeded[$alertType])) {
                $alert->update([
                    'status'      => 'resolved',
                    'resolved_at' => now(),
                ]);
            }
        }
    }

    /**
     * Check for items expiring soon.
     */
    public static function checkExpiringItems(int $storeId, int $daysAhead = 7): void
    {
        $threshold = Carbon::now()->addDays($daysAhead);

        // Stock Batches expiring
        $batches = StockBatch::where('store_id', $storeId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $threshold)
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->where('quantity', '>', 0)
            ->get();

        foreach ($batches as $batch) {
            $exists = StockAlert::where('store_id', $storeId)
                ->where('alertable_type', 'App\\Models\\StockBatch')
                ->where('alertable_id', $batch->id)
                ->where('alert_type', StockAlert::TYPE_EXPIRING_SOON)
                ->whereIn('status', ['active', 'acknowledged'])
                ->exists();

            if (!$exists) {
                StockAlert::create([
                    'alertable_type'     => 'App\\Models\\StockBatch',
                    'alertable_id'       => $batch->id,
                    'alert_type'         => StockAlert::TYPE_EXPIRING_SOON,
                    'current_quantity'   => $batch->quantity,
                    'threshold_quantity' => 0,
                    'status'             => 'active',
                    'store_id'           => $storeId,
                ]);
            }
        }
    }

    /**
     * Generate reorder suggestions for items below reorder point.
     */
    public static function generateReorderSuggestions(int $storeId): void
    {
        $stocks = Stock::where('store_id', $storeId)
            ->whereNotNull('reorder_point')
            ->whereColumn('unit_qty', '<=', 'reorder_point')
            ->get();

        foreach ($stocks as $stock) {
            $exists = ReorderSuggestion::where('store_id', $storeId)
                ->where('stock_id', $stock->id)
                ->where('status', 'pending')
                ->exists();

            if ($exists) continue;

            // Find last supplier from most recent batch
            $lastBatch = StockBatch::where('stock_id', $stock->id)
                ->where('store_id', $storeId)
                ->latest('buy_date')
                ->first();

            $suggestedQty = ($stock->reorder_point * 2) - $stock->unit_qty;
            $estimatedCost = $lastBatch ? $lastBatch->cost_per_unit * $suggestedQty : null;

            ReorderSuggestion::create([
                'stock_id'           => $stock->id,
                'suggested_quantity' => max(0, $suggestedQty),
                'supplier_id'        => $lastBatch?->supplier_id,
                'estimated_cost'     => $estimatedCost,
                'status'             => 'pending',
                'store_id'           => $storeId,
            ]);
        }
    }

    /**
     * Acknowledge an alert.
     */
    public static function acknowledgeAlert(int $alertId, int $userId): void
    {
        $alert = StockAlert::findOrFail($alertId);
        $alert->update([
            'status'          => 'acknowledged',
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Get alert summary counts for a store (for navbar badge).
     */
    public static function getAlertSummary(int $storeId): array
    {
        $counts = StockAlert::where('store_id', $storeId)
            ->where('status', 'active')
            ->selectRaw('alert_type, COUNT(*) as total')
            ->groupBy('alert_type')
            ->pluck('total', 'alert_type')
            ->toArray();

        return [
            'out_of_stock'  => $counts['out_of_stock'] ?? 0,
            'low_stock'     => $counts['low_stock'] ?? 0,
            'reorder_point' => $counts['reorder_point'] ?? 0,
            'expiring_soon' => $counts['expiring_soon'] ?? 0,
            'total'         => array_sum($counts),
        ];
    }
}
