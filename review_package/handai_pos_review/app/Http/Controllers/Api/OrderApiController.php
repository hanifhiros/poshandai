<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; // Pastikan model ini sudah ada


class OrderApiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $selected_store_id = $request->get('store_id');
       

        $orders = Order::with(['customer', 'invoices.product'])
        ->when($status === 'terkirim', fn($q) => $q->where('order_status', 'terkirim'))
        ->when($status === 'belum terkirim', fn($q) => $q->where('order_status', 'belum terkirim'))
        ->when($status === 'dibatalkan', fn($q) => $q->where('order_status', 'dibatalkan'))
        ->where('store_id', $selected_store_id)
        ->latest()
        ->paginate(7);



        return response()->json($orders);
    }
}
                