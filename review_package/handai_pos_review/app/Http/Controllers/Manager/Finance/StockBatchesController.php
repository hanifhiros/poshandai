<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockBatch;

class StockBatchesController extends Controller
{
    public function index(Request $request)
    {
        $selectedStoreId = session('selected_store');
        $selected_store = $selectedStoreId ? \App\Models\Store::find($selectedStoreId) : null;

        // Ambil filter
        $search = $request->input('search');
        $sortDate = $request->input('sort_date', 'desc'); // default desc

        $query = StockBatch::with(['stock', 'unit'])
            ->where('store_id', $selectedStoreId);

        // Kalau ada search nama stock
        if ($search) {
            $query->whereHas('stock', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Urutkan berdasarkan tanggal beli
        $query->orderBy('buy_date', $sortDate);

        $stockBatches = $query->paginate(10);

        return view('handai-manager.finance.stock-batch-finance', compact('stockBatches', 'selected_store'));
    }
}
