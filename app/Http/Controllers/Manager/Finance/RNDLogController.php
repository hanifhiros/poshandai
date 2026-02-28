<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RNDHistory;
class RNDLogController extends Controller
{
    public function index(Request $request)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;
        $from = $request->input('from');
        $to = $request->input('to');

        $query = RNDHistory::with(['pic', 'stockUsages.stock', 'stockUsages.unit'])->where('store_id', $selected_store_id)
            ->orderByDesc('rnd_date');

        // Filter berdasarkan tanggal jika ada
        if ($from) {
            $query->whereDate('rnd_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('rnd_date', '<=', $to);
        }

        $rndHistories = $query->paginate(10);

        // Ambil proyek dengan progress "Ready" untuk ditampilkan di bawah
        $readyProjects = RNDHistory::with(['stockUsages.stock', 'stockUsages.unit'])
            ->where('progress', 'Ready')
            ->orderByDesc('rnd_date')
            ->get();

        return view('handai-manager.finance.rnd-log', compact('rndHistories', 'readyProjects','selected_store'));
    }
}
