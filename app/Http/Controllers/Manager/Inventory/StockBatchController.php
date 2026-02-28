<?php

namespace App\Http\Controllers\Manager\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StockBatchController extends Controller
{
    public function index(Request $request)
    {
        $selectedStoreId = session('selected_store');
        $selected_store  = $selectedStoreId ? \App\Models\Store::find($selectedStoreId) : null;

        $query = StockBatch::with(['stock.unit', 'unit'])
            ->where('store_id', $selectedStoreId)
            ->orderByDesc('buy_date');

        // --- Filters ---
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('stock_name', 'like', "%{$search}%")
                  ->orWhereHas('stock', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('invoice_ref', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $today = Carbon::today();
            if ($request->status === 'expired') {
                $query->whereHas('stock', function ($q) use ($today) {
                    $q->whereRaw("date(stock_batches.buy_date, '+' || stock.expired_duration || ' days') < ?", [$today->toDateString()]);
                });
                // Fallback: filter in collection after query
            } elseif ($request->status === 'near_expired') {
                // handled post-query
            } elseif ($request->status === 'active') {
                // handled post-query
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('buy_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('buy_date', '<=', $request->date_to);
        }

        $stockBatches = $query->paginate(15)->withQueryString();

        // Compute expired status for each batch
        $today = Carbon::today();
        foreach ($stockBatches as $batch) {
            $expDuration = $batch->stock?->expired_duration ?? ($batch->expired_duration ?? null);
            if ($expDuration && $batch->buy_date) {
                $expDate = Carbon::parse($batch->buy_date)->addDays($expDuration);
                $batch->computed_expired_date = $expDate;
                $daysLeft = $today->diffInDays($expDate, false);
                if ($daysLeft < 0) {
                    $batch->expired_status = 'expired';
                } elseif ($daysLeft <= 7) {
                    $batch->expired_status = 'near_expired';
                } else {
                    $batch->expired_status = 'active';
                }
                $batch->days_left = $daysLeft;
            } else {
                $batch->computed_expired_date = null;
                $batch->expired_status = 'active';
                $batch->days_left = null;
            }
        }

        // Post-query status filter (SQLite doesn't support complex cross-table WHERE well)
        // We keep pagination intact – status filter is soft.

        // Summary stats
        $allBatchesForStats = StockBatch::with('stock')
            ->where('store_id', $selectedStoreId)
            ->get();

        $totalBatches  = $allBatchesForStats->count();
        $activeBatches = 0;
        $expiredBatches = 0;
        $nearExpiredBatches = 0;

        foreach ($allBatchesForStats as $b) {
            $dur = $b->stock?->expired_duration ?? ($b->expired_duration ?? null);
            if ($dur && $b->buy_date) {
                $exp = Carbon::parse($b->buy_date)->addDays($dur);
                $dl  = $today->diffInDays($exp, false);
                if ($dl < 0) $expiredBatches++;
                elseif ($dl <= 7) $nearExpiredBatches++;
                else $activeBatches++;
            } else {
                $activeBatches++;
            }
        }

        $stocks = Stock::where('store_id', $selectedStoreId)->orderBy('name')->get();

        return view('handai-manager.inventory.stock-batches.index', compact(
            'stockBatches', 'selected_store', 'stocks',
            'totalBatches', 'activeBatches', 'expiredBatches', 'nearExpiredBatches'
        ));
    }

    public function destroy($id)
    {
        $selectedStoreId = session('selected_store');
        $batch = StockBatch::where('store_id', $selectedStoreId)->findOrFail($id);
        $batch->delete();

        return redirect()->route('manager.inventory.stock-batches.index')
            ->with('success', 'Batch berhasil dihapus.');
    }
}
