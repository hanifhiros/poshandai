<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; // Pastikan model ini sudah ada
use App\Models\Customer;
use App\Services\InventoryService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class OrderController extends Controller
{
    public function index(Request $request)
{
    $status = $request->get('status', 'all');
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

    $query = Order::with(['customer', 'invoices.product'])
        ->when($status === 'terkirim', fn($q) => $q->where('order_status', 'terkirim'))
        ->when($status === 'belum terkirim', fn($q) => $q->where('order_status', 'belum terkirim'))
        ->when($status === 'dibatalkan', fn($q) => $q->where('order_status', 'dibatalkan'))
        ->where('store_id', $selected_store_id);

    // Search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('id', 'LIKE', "%{$search}%")
              ->orWhereHas('customer', function ($cq) use ($search) {
                  $cq->where('name', 'LIKE', "%{$search}%");
              });
        });
    }

    $ordersPaginated = $query->latest()->paginate(10)->appends($request->query());

    // Data pending tanpa pagination (utk card bawah)
    $pendingOrders = Order::with(['customer', 'invoices.product', 'invoices.variant.options.attribute'])
            ->where('order_status', 'belum terkirim')->where('store_id', $selected_store_id)
            ->latest()
            ->paginate(6, ['*'], 'pending_page');

    // Summary stats
    $orderStats = Order::where('store_id', $selected_store_id)
        ->selectRaw("count(*) as total")
        ->selectRaw("sum(case when order_status = 'terkirim' then 1 else 0 end) as terkirim")
        ->selectRaw("sum(case when order_status = 'belum terkirim' then 1 else 0 end) as belum_terkirim")
        ->selectRaw("sum(case when order_status = 'dibatalkan' then 1 else 0 end) as dibatalkan")
        ->first();

    return view('handai-manager.operational.orders.index', [
        'orders' => $ordersPaginated,
        'pendingOrders' => $pendingOrders,
        'selectedStatus' => $status,
        'selected_store' => $selected_store,
        'orderStats' => $orderStats,
    ]);
}
public function markAsShipped($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('invoices.variant.product')->findOrFail($id);

            // Validate & deduct stock via InventoryService (records SALE_OUT movements)
            InventoryService::validateAndDeductOnShip($order);

            // ── Accounting Journal: Kasir Sale ──
            try {
                AccountingService::journalSale(
                    $order->store_id, $order->gross_amount, $order->total_hpp_orders ?? 0, $order->id, 'KASIR'
                );
            } catch (\Exception $e) {
                Log::warning('Kasir Accounting journal failed: ' . $e->getMessage());
            }

            $order->order_status = 'terkirim';
            $order->save();

            DB::commit();
            return redirect()->back()->with('success', 'Pesanan ditandai sebagai selesai.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('invoices.variant.product')->findOrFail($id);
            $previousStatus = $order->order_status;

            $order->order_status = 'dibatalkan';
            $order->save();

            // Restore stock if order was already shipped (stock was deducted)
            InventoryService::restoreStockOnCancel($order, $previousStatus);

            // ── Accounting Journal: Sale Return (reverse) ──
            if ($previousStatus === 'terkirim') {
                try {
                    AccountingService::journalSaleReturn(
                        $order->store_id, $order->gross_amount, $order->total_hpp_orders ?? 0, $order->id
                    );
                } catch (\Exception $e) {
                    Log::warning('Cancel Accounting journal failed: ' . $e->getMessage());
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }


    

}
