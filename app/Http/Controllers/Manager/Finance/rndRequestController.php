<?php

namespace App\Http\Controllers\Manager\Finance;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RNDHistory;
use App\Models\RNDStockUsage;

class RnDRequestController extends Controller
{
    public function index()
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

        $rndRequests = RNDHistory::with(['stockUsages.stock.unit', 'pic'])
        ->where('status', 'pending') // Tambahkan ini
        ->orderByDesc('rnd_date')
        ->get();
    

       
        return view('handai-manager.finance.rnd-request', compact('rndRequests','selected_store'));
    }
    

    public function approve($usageId)
    {
        $usage = RNDStockUsage::findOrFail($usageId);
        $usage->status = 'approved';
        $usage->save();

        return back()->with('success', 'Bahan telah disetujui.');
    }

    public function approveAll($rndId)
{
    \App\Models\RNDHistory::where('id', $rndId)
        ->update(['status' => 'approved']);

    return back()->with('success', 'R&D telah disetujui.');
}

public function rejectAll($rndId)
{
    \App\Models\RNDHistory::where('id', $rndId)
        ->update(['status' => 'rejected']);

    return back()->with('success', 'R&D telah ditolak.');
}
public function markAsFinished($rndId)
{
    $rnd = \App\Models\RNDHistory::with(['stockUsages.stock'])->findOrFail($rndId);

    foreach ($rnd->stockUsages as $usage) {
        $stock = $usage->stock;

        if ($stock) {
            // Pastikan ada konversi jika satuan berbeda
            $conversionRate = \App\Helpers\ConversionHelper::getConversionRate($usage->unit_id, $stock->unit_id);
            if ($conversionRate !== null) {
                $adjustedQty = $usage->quantity_used * $conversionRate;

                // Kurangi stok
                $stock->unit_qty = max(0, $stock->unit_qty - $adjustedQty);
                $stock->save();
            }
        }
    }

    $rnd->progress = 'Finished';
    $rnd->save();

    return back()->with('success', 'Proyek R&D telah diselesaikan dan stok terkait telah dikurangi.');
}



}

