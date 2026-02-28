<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\ProductVariants;
use App\Models\Order;
use App\Models\Invoice;
use App\Helpers\ConversionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * InventoryService — Central service for all inventory operations.
 *
 * Every stock change in the system MUST go through this service to ensure:
 * 1. Stock quantities stay consistent
 * 2. Every movement is recorded in stock_movements
 * 3. HPP/COGS is calculated correctly
 * 4. Stock validations are enforced
 *
 * Flow Integration:
 *   Purchase  → recordPurchaseIn()
 *   Production → recordProductionConsumption() + recordProductionOutput()
 *   Sales (POS) → validateCartStock() + processSaleDeduction()
 *   Sales (Kasir) → validateAndDeductOnShip()
 *   Cancel → restoreStockOnCancel()
 *   Expired → recordExpiredReduction()
 *   R&D → recordRndConsumption()
 */
class InventoryService
{
    // ══════════════════════════════════════════════════════════
    //  1. PURCHASE FLOW
    //  Called when a stock batch is created (pembelian bahan baku)
    // ══════════════════════════════════════════════════════════

    /**
     * Record a purchase-in movement after a StockBatch is created and stock is recalculated.
     */
    public static function recordPurchaseIn(
        int $storeId,
        Stock $stock,
        StockBatch $batch,
        float $convertedQtyInStockUnit
    ): StockMovement {
        return StockMovement::create([
            'store_id'           => $storeId,
            'stock_id'           => $stock->id,
            'product_variant_id' => null,
            'movement_type'      => StockMovement::PURCHASE_IN,
            'quantity'           => $convertedQtyInStockUnit,
            'unit_id'            => $stock->unit_id,
            'cost_per_unit'      => $convertedQtyInStockUnit > 0
                ? round($batch->cost / $convertedQtyInStockUnit, 2)
                : 0,
            'total_cost'         => $batch->cost,
            'reference_type'     => 'stock_batches',
            'reference_id'       => $batch->id,
            'notes'              => "Pembelian batch #{$batch->id} — {$stock->name}",
            'created_by'         => Auth::id(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  2. PRODUCTION FLOW
    //  Called when production uses raw materials & produces finished goods
    // ══════════════════════════════════════════════════════════

    /**
     * Record raw material consumption during production.
     * The actual stock deduction should already have been done by the caller.
     */
    public static function recordProductionConsumption(
        int $storeId,
        Stock $stock,
        float $usedQtyInStockUnit,
        int $bomUnitId,
        int $productionHistoryId
    ): StockMovement {
        return StockMovement::create([
            'store_id'           => $storeId,
            'stock_id'           => $stock->id,
            'product_variant_id' => null,
            'movement_type'      => StockMovement::PRODUCTION_OUT,
            'quantity'           => -abs($usedQtyInStockUnit),
            'unit_id'            => $stock->unit_id,
            'cost_per_unit'      => $stock->price_per_unit,
            'total_cost'         => $usedQtyInStockUnit * $stock->price_per_unit,
            'reference_type'     => 'production_history',
            'reference_id'       => $productionHistoryId,
            'notes'              => "Bahan '{$stock->name}' digunakan untuk produksi #{$productionHistoryId}",
            'created_by'         => Auth::id(),
        ]);
    }

    /**
     * Record finished goods increase after production.
     * The actual qty/hpp update should already have been done by the caller.
     */
    public static function recordProductionOutput(
        int $storeId,
        ProductVariants $variant,
        int $quantityProduced,
        float $hppPerUnit,
        int $productionHistoryId
    ): StockMovement {
        $productName = $variant->product?->name ?? 'Unknown';

        return StockMovement::create([
            'store_id'           => $storeId,
            'stock_id'           => null,
            'product_variant_id' => $variant->id,
            'movement_type'      => StockMovement::PRODUCTION_IN,
            'quantity'           => $quantityProduced,
            'unit_id'            => null,
            'cost_per_unit'      => $hppPerUnit,
            'total_cost'         => $hppPerUnit * $quantityProduced,
            'reference_type'     => 'production_history',
            'reference_id'       => $productionHistoryId,
            'notes'              => "Produksi {$quantityProduced} unit — {$productName}",
            'created_by'         => Auth::id(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  3. SALES FLOW
    //  Stock validation, deduction, and COGS calculation
    // ══════════════════════════════════════════════════════════

    /**
     * Validate that all product variants in the cart have sufficient stock.
     *
     * @param array $cart Session cart array with 'variant_id' and 'quantity' per item
     * @return array Error messages. Empty array = all stock sufficient.
     */
    public static function validateCartStock(array $cart): array
    {
        $errors = [];

        // Aggregate required quantities per variant
        $requiredQty = [];
        foreach ($cart as $item) {
            $vid = $item['variant_id'];
            $requiredQty[$vid] = ($requiredQty[$vid] ?? 0) + ($item['quantity'] ?? 0);
        }

        $variantIds = array_keys($requiredQty);
        $variants = ProductVariants::with('product')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($requiredQty as $variantId => $qty) {
            $variant = $variants->get($variantId);

            if (!$variant) {
                $errors[] = "Produk varian #{$variantId} tidak ditemukan.";
                continue;
            }

            if ($variant->quantity < $qty) {
                $name = $variant->product?->name ?? 'Unknown';
                $summary = $variant->variantSummary();
                $errors[] = "Stok '{$name} ({$summary})' tidak mencukupi. Dibutuhkan: {$qty}, tersedia: {$variant->quantity}";
            }
        }

        return $errors;
    }

    /**
     * Deduct product stock for a POS sale order.
     * Creates invoice entries, deducts variant quantities, records movements.
     * Must be called inside a DB transaction.
     *
     * @param array $cart Session cart
     * @param int $orderId The created order's ID
     * @param int $storeId The store ID
     * @return float Total HPP for the order
     */
    public static function processSaleDeduction(array $cart, int $orderId, int $storeId): float
    {
        $totalHpp = 0;

        $variantIds = array_column($cart, 'variant_id');
        $variants = ProductVariants::with('product')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($cart as $item) {
            $variant = $variants->get($item['variant_id']);
            if (!$variant) continue;

            $quantity = $item['quantity'] ?? 0;
            $variantHpp = $variant->hpp ?? 0;
            $lineHpp = $variantHpp * $quantity;
            $totalHpp += $lineHpp;

            // Deduct product variant stock
            $variant->quantity = max(0, $variant->quantity - $quantity);
            $variant->save();

            // Record SALE_OUT movement
            StockMovement::create([
                'store_id'           => $storeId,
                'stock_id'           => null,
                'product_variant_id' => $variant->id,
                'movement_type'      => StockMovement::SALE_OUT,
                'quantity'           => -$quantity,
                'unit_id'            => null,
                'cost_per_unit'      => $variantHpp,
                'total_cost'         => $lineHpp,
                'reference_type'     => 'orders',
                'reference_id'       => $orderId,
                'notes'              => "Penjualan {$quantity}x {$variant->product?->name}",
                'created_by'         => Auth::id(),
            ]);
        }

        return $totalHpp;
    }

    /**
     * Validate stock and deduct when an order is marked as shipped (Kasir flow).
     * Called from OrderController::markAsShipped().
     *
     * @param Order $order The order being shipped
     * @throws \Exception if stock is insufficient
     */
    public static function validateAndDeductOnShip(Order $order): void
    {
        $order->loadMissing('invoices.variant.product');

        // First pass: validate all stock
        foreach ($order->invoices as $invoice) {
            if (!$invoice->variant) continue;

            $variant = $invoice->variant;
            $required = $invoice->quantity_bought;

            if ($variant->quantity < $required) {
                $name = $variant->product?->name ?? 'Unknown';
                throw new \Exception(
                    "Stok '{$name}' tidak mencukupi untuk pengiriman. " .
                    "Dibutuhkan: {$required}, tersedia: {$variant->quantity}"
                );
            }
        }

        // Second pass: deduct and record movements
        foreach ($order->invoices as $invoice) {
            if (!$invoice->variant) continue;

            $variant = $invoice->variant;
            $quantity = $invoice->quantity_bought;
            $variantHpp = $variant->hpp ?? 0;

            $variant->quantity = max(0, $variant->quantity - $quantity);
            $variant->save();

            StockMovement::create([
                'store_id'           => $order->store_id,
                'stock_id'           => null,
                'product_variant_id' => $variant->id,
                'movement_type'      => StockMovement::SALE_OUT,
                'quantity'           => -$quantity,
                'unit_id'            => null,
                'cost_per_unit'      => $variantHpp,
                'total_cost'         => $variantHpp * $quantity,
                'reference_type'     => 'orders',
                'reference_id'       => $order->id,
                'notes'              => "Pengiriman order #{$order->id} — {$quantity}x {$variant->product?->name}",
                'created_by'         => Auth::id(),
            ]);
        }
    }

    /**
     * Restore stock when an order that was already 'terkirim' gets cancelled.
     * Only restores if the order was previously shipped (stock was deducted).
     *
     * @param Order $order The order being cancelled
     * @param string $previousStatus The order's status before cancellation
     */
    public static function restoreStockOnCancel(Order $order, string $previousStatus): void
    {
        // Only restore stock if it was already deducted (order was 'terkirim')
        if ($previousStatus !== 'terkirim') {
            return;
        }

        $order->loadMissing('invoices.variant.product');

        foreach ($order->invoices as $invoice) {
            if (!$invoice->variant) continue;

            $variant = $invoice->variant;
            $quantity = $invoice->quantity_bought;
            $variantHpp = $variant->hpp ?? 0;

            // Restore quantity
            $variant->quantity += $quantity;
            $variant->save();

            // Record SALE_RETURN movement
            StockMovement::create([
                'store_id'           => $order->store_id,
                'stock_id'           => null,
                'product_variant_id' => $variant->id,
                'movement_type'      => StockMovement::SALE_RETURN,
                'quantity'           => $quantity, // positive = stock returned
                'unit_id'            => null,
                'cost_per_unit'      => $variantHpp,
                'total_cost'         => $variantHpp * $quantity,
                'reference_type'     => 'orders',
                'reference_id'       => $order->id,
                'notes'              => "Pembatalan order #{$order->id} — {$quantity}x {$variant->product?->name} dikembalikan",
                'created_by'         => Auth::id(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════
    //  4. EXPIRED FLOW
    //  Called when expired stock is removed
    // ══════════════════════════════════════════════════════════

    /**
     * Record expired stock reduction.
     */
    public static function recordExpiredReduction(
        int $storeId,
        Stock $stock,
        float $expiredQtyInStockUnit
    ): StockMovement {
        return StockMovement::create([
            'store_id'           => $storeId,
            'stock_id'           => $stock->id,
            'product_variant_id' => null,
            'movement_type'      => StockMovement::EXPIRED_OUT,
            'quantity'           => -abs($expiredQtyInStockUnit),
            'unit_id'            => $stock->unit_id,
            'cost_per_unit'      => $stock->price_per_unit,
            'total_cost'         => $expiredQtyInStockUnit * $stock->price_per_unit,
            'reference_type'     => 'stock',
            'reference_id'       => $stock->id,
            'notes'              => "Stok expired: {$stock->name} (-{$expiredQtyInStockUnit})",
            'created_by'         => Auth::id(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  5. R&D FLOW
    //  Called when R&D uses stock
    // ══════════════════════════════════════════════════════════

    /**
     * Record R&D stock consumption.
     */
    public static function recordRndConsumption(
        int $storeId,
        Stock $stock,
        float $quantityInStockUnit,
        int $rndHistoryId
    ): StockMovement {
        return StockMovement::create([
            'store_id'           => $storeId,
            'stock_id'           => $stock->id,
            'product_variant_id' => null,
            'movement_type'      => StockMovement::RND_OUT,
            'quantity'           => -abs($quantityInStockUnit),
            'unit_id'            => $stock->unit_id,
            'cost_per_unit'      => $stock->price_per_unit,
            'total_cost'         => $quantityInStockUnit * $stock->price_per_unit,
            'reference_type'     => 'rnd_history',
            'reference_id'       => $rndHistoryId,
            'notes'              => "R&D #{$rndHistoryId} — {$stock->name}",
            'created_by'         => Auth::id(),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  6. ADJUSTMENT FLOW
    //  For manual stock corrections
    // ══════════════════════════════════════════════════════════

    /**
     * Record a manual stock adjustment.
     */
    public static function recordAdjustment(
        int $storeId,
        ?int $stockId,
        ?int $productVariantId,
        float $quantityChange,
        ?int $unitId,
        string $reason
    ): StockMovement {
        return StockMovement::create([
            'store_id'           => $storeId,
            'stock_id'           => $stockId,
            'product_variant_id' => $productVariantId,
            'movement_type'      => StockMovement::ADJUSTMENT,
            'quantity'           => $quantityChange,
            'unit_id'            => $unitId,
            'cost_per_unit'      => null,
            'total_cost'         => null,
            'reference_type'     => null,
            'reference_id'       => null,
            'notes'              => "Adjustment: {$reason}",
            'created_by'         => Auth::id(),
        ]);
    }
}
