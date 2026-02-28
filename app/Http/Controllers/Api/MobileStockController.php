<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\Unit;
use App\Models\Order;
class MobileStockController extends Controller
{


    public function getStockCategories()
    {
        $categories = StockCategory::select('id', 'stock_category_name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }


    public function getUnits()
    {
        $units = Unit::select('id', 'symbol', 'name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $units,
        ]);
    }

    public function apiIndex(Request $request)
    {
        $selected_store_id = $request->input('store_id');
        if (!$selected_store_id) {
            return response()->json(['message' => 'store_id is required'], 400);
        }

        $stocks = Stock::with(['unit', 'stockCategory'])
            ->where('store_id', $selected_store_id)
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'name' => $stock->name,
                    'unit_qty' => $stock->unit_qty,
                    'price_per_unit' => $stock->price_per_unit,
                    'status' => $this->getStockStatus($stock->unit_qty),
                    'expired_duration' => $stock->expired_duration,
                    'almost_expired' => $this->getAlmostExpiredCount($stock),
                    'unit' => [
                        'symbol' => $stock->unit->symbol ?? '',
                        'name' => $stock->unit->name ?? '',
                        'type' => $stock->unit->unit_type ?? '',
                    ],
                    'stock_category' => [
                        'name' => $stock->stockCategory->name ?? '',
                    ],
                ];
            });

        return response()->json($stocks);
    }

    private function getStockStatus($qty)
    {
        if ($qty == 0)
            return 'Out of Stock';
        if ($qty < 10)
            return 'Low Stock';
        return 'Ready';
    }

    private function getAlmostExpiredCount($stock)
    {
        return $stock->batches()
            ->where('store_id', $stock->store_id)
            ->whereDate('buy_date', '<=', now()->subDays($stock->expired_duration - 3))
            ->count();
    }



    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'expired_duration' => 'required|integer',
            'stock_category_id' => 'required|exists:stock_category,id',
            'store_id' => 'required|exists:store,id',
        ]);
        if ($request->hasFile('image_url')) {
            $imagePath = $request->file('image_url')->store('stocks', 'public');
        } else {
            $imagePath = null;
        }



        if ($request->hasFile('image_url')) {
            $imagePath = $request->file('image_url')->store('stocks', 'public');
        } else {
            $imagePath = null;
        }

        $stock = Stock::create([
            'name' => $validated['name'],
            'unit_id' => $validated['unit_id'],
            'stock_category_id' => $validated['stock_category_id'],
            'expired_duration' => $validated['expired_duration'],
            'store_id' => $validated['store_id'],
            'unit_qty' => 0,
            'price_per_unit' => 0,
            'image_url' => $imagePath,
        ]);

        return response()->json(['message' => 'Stock created successfully', 'stock' => $stock]);
    }


    public function OrderData(Request $request)
    {
        $status = $request->get('status', 'all');
        $storeId = $request->get('store_id');

        if (!$storeId) {
            return response()->json(['message' => 'store_id is required'], 400);
        }

        $query = \App\Models\Order::with(['customer', 'invoices.product', 'invoices.variant']);

        if ($status !== 'all') {
            $query->where('order_status', $status);
        }

        $query->where('store_id', $storeId)
            ->latest();

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate(10)
        ]);
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:terkirim,dibatalkan',
        ]);

        $order = \App\Models\Order::find($id);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order tidak ditemukan'], 404);
        }

        $order->order_status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => "Status pesanan berhasil diubah menjadi {$request->status}",
            'order' => $order
        ]);
    }


}



