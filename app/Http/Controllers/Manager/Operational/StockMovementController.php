<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Store;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $query = StockMovement::with(['stock', 'productVariant.product', 'unit', 'creator'])
            ->where('store_id', $storeId);

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('stock', fn($sq) => $sq->where('name', 'like', "%{$s}%"))
                  ->orWhere('notes', 'like', "%{$s}%")
                  ->orWhere('reference_type', 'like', "%{$s}%");
            });
        }

        // Movement type filter
        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        // Summary stats (single grouped query instead of 4 separate ones)
        $now = Carbon::now();
        $monthlySummary = StockMovement::where('store_id', $storeId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->selectRaw("movement_type, COUNT(*) as cnt, COALESCE(SUM(total_cost), 0) as cost")
            ->groupBy('movement_type')
            ->pluck('cost', 'movement_type');

        $purchaseInMonth = $monthlySummary->get('PURCHASE_IN', 0);
        $productionOutMonth = $monthlySummary->get('PRODUCTION_OUT', 0);
        $saleOutMonth = $monthlySummary->get('SALE_OUT', 0);
        $totalMovementsMonth = StockMovement::where('store_id', $storeId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Unique movement types for filter dropdown
        $movementTypes = StockMovement::where('store_id', $storeId)
            ->select('movement_type')
            ->distinct()
            ->pluck('movement_type');

        return view('handai-manager.operational.stock-movements.index', compact(
            'selected_store', 'movements', 'purchaseInMonth', 'productionOutMonth',
            'saleOutMonth', 'totalMovementsMonth', 'movementTypes'
        ));
    }
}
