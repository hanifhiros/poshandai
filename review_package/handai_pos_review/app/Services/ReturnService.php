<?php

namespace App\Services;

use App\Models\ReturnOrder;
use App\Models\ReturnItem;
use App\Models\StockMovement;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    /**
     * Process approved return — restock items and create accounting journal.
     */
    public static function processReturn(ReturnOrder $return): void
    {
        $return->loadMissing('items', 'order');
        $storeId = $return->store_id;
        $totalRefund = 0;

        foreach ($return->items as $item) {
            // Restock good-condition items
            if ($item->restock && $item->product_variants_id) {
                $variant = ProductVariant::find($item->product_variants_id);
                if ($variant) {
                    $variant->quantity += $item->quantity_returned;
                    $variant->save();

                    StockMovement::create([
                        'store_id'           => $storeId,
                        'stock_id'           => null,
                        'product_variant_id' => $variant->id,
                        'movement_type'      => StockMovement::SALE_RETURN,
                        'quantity'           => $item->quantity_returned,
                        'unit_id'            => null,
                        'cost_per_unit'      => $variant->hpp ?? 0,
                        'total_cost'         => ($variant->hpp ?? 0) * $item->quantity_returned,
                        'reference_type'     => 'returns',
                        'reference_id'       => $return->id,
                        'notes'              => "Retur #{$return->return_number} — {$item->quantity_returned}x {$item->product_name} dikembalikan ke stok",
                        'created_by'         => Auth::id(),
                    ]);
                }
            }

            $totalRefund += $item->refund_amount;
        }

        // Create refund accounting journal
        if ($totalRefund > 0) {
            AccountingService::createJournal(
                $storeId,
                "Refund retur #{$return->return_number}",
                'CANCEL',
                [
                    ['account_sub_type' => 'penjualan', 'debit' => $totalRefund, 'credit' => 0, 'memo' => 'Pengembalian penjualan'],
                    ['account_sub_type' => 'kas',       'debit' => 0,            'credit' => $totalRefund, 'memo' => 'Refund ke pelanggan'],
                ],
                'returns',
                $return->id
            );
        }

        $return->update([
            'total_refund_amount' => $totalRefund,
            'status' => 'processed',
            'processed_by' => Auth::id(),
        ]);
    }

    /**
     * Get return statistics for dashboard.
     */
    public static function getStatistics(int $storeId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ReturnOrder::where('store_id', $storeId);

        if ($startDate && $endDate) {
            $query->whereBetween('return_date', [$startDate, $endDate]);
        }

        $returns = $query->get();

        return [
            'total_returns'    => $returns->count(),
            'pending_count'    => $returns->where('status', 'pending')->count(),
            'completed_count'  => $returns->whereIn('status', ['processed', 'completed'])->count(),
            'total_refunded'   => $returns->whereIn('status', ['processed', 'completed'])->sum('total_refund_amount'),
        ];
    }
}
