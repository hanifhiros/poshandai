<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionHistory;
use App\Models\Order;
use App\Models\WasteLog;
use App\Models\StockMovement;
use App\Models\RNDHistory;
use App\Models\Stock;

class OperationalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $store_id = session('selected_store');
        if (!$store_id) {
            return redirect()->route('manager.outlet')->withErrors(['store' => 'Silakan pilih outlet terlebih dahulu.']);
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Produksi Hari Ini vs Kemarin
        $prodToday = ProductionHistory::where('store_id', $store_id)
            ->whereDate('production_date', $today)
            ->count();
        $prodYest = ProductionHistory::where('store_id', $store_id)
            ->whereDate('production_date', $yesterday)
            ->count();
        $prodGrowth = $prodYest > 0 ? (($prodToday - $prodYest) / $prodYest) * 100 : ($prodToday > 0 ? 100 : 0);

        // 2. Pesanan Menunggu Diproses (Pending / Need Action)
        $pendingOrders = Order::where('store_id', $store_id)
            ->whereIn('status', ['pending', 'processing', 'Dapur Pengerjaan'])
            ->count();

        // 3. Waste Bulan Ini
        $wasteThisMonth = WasteLog::where('store_id', $store_id)
            ->whereBetween('loss_date', [$startOfMonth, $endOfMonth])
            ->sum('loss_value') ?? 0;

        // 4. Pergerakan Stok Terkini
        $recentMovements = StockMovement::with(['stock', 'productVariant', 'creator'])
            ->where('store_id', $store_id)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // 5. Permintaan R&D Aktif (Using DB for safety if Model is named differently like RndHistory)
        $activeRnD = DB::table('rnd_history')->where('store_id', $store_id)
            ->whereIn('status', ['pending', 'in_progress', 'review'])
            ->count();

        // 6. Stok Menipis (Raw Materials)
        $lowStocks = Stock::where('store_id', $store_id)
            ->whereRaw('CAST(unit_qty AS DECIMAL) <= min_stock')
            ->take(5)
            ->get();

        return view('handai-manager.operational.dashboard.index', compact(
            'prodToday', 'prodGrowth',
            'pendingOrders', 'wasteThisMonth', 
            'recentMovements', 'activeRnD', 'lowStocks'
        ));
    }
}
