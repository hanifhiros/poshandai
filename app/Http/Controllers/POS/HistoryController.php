<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $dateFilter = $request->get('date', Carbon::today()->toDateString());
        $statusFilter = $request->get('status', '');
        $search = $request->get('search', '');

        $query = Order::with(['customer', 'invoices.product', 'invoices.variant.options.attribute'])
            ->where('store_id', $storeId)
            ->whereDate('created_at', $dateFilter);

        if ($statusFilter) {
            $query->where('order_status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15);

        // Summary stats for the selected date
        $summaryQuery = Order::where('store_id', $storeId)
            ->whereDate('created_at', $dateFilter);

        $totalRevenue = (clone $summaryQuery)->where('order_status', 'terkirim')->sum('gross_amount');
        $totalOrders = (clone $summaryQuery)->count();
        $completedOrders = (clone $summaryQuery)->where('order_status', 'terkirim')->count();
        $cancelledOrders = (clone $summaryQuery)->where('order_status', 'dibatalkan')->count();

        return view('handai-pos.history.index', compact(
            'selected_store',
            'orders',
            'dateFilter',
            'statusFilter',
            'search',
            'totalRevenue',
            'totalOrders',
            'completedOrders',
            'cancelledOrders'
        ));
    }
}
