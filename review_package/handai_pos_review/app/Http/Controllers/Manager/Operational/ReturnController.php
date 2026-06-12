<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\ReturnOrder;
use App\Models\ReturnItem;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');

        $query = ReturnOrder::where('store_id', $storeId)
            ->with('order', 'customer', 'processor')
            ->latest('return_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('return_type')) {
            $query->where('return_type', $request->return_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $returns = $query->paginate(20)->withQueryString();

        $stats = ReturnService::getStatistics($storeId);

        return view('handai-manager.operational.returns.index', compact('returns', 'stats'));
    }

    public function create(Request $request)
    {
        $storeId = session('selected_store');
        $order = null;
        $orderItems = [];

        if ($request->filled('order_id')) {
            $order = Order::where('id', $request->order_id)
                ->where('store_id', $storeId)
                ->with('customer')
                ->first();

            if ($order) {
                $orderItems = Invoice::where('order_id', $order->id)
                    ->with('variant')
                    ->get();
            }
        }

        return view('handai-manager.operational.returns.create', compact('order', 'orderItems'));
    }

    public function store(Request $request)
    {
        $storeId = session('selected_store');

        $request->validate([
            'order_id'              => 'required|exists:orders,id',
            'return_type'           => 'required|in:refund,exchange,store_credit',
            'reason'                => 'required|string|max:500',
            'notes'                 => 'nullable|string|max:1000',
            'items'                 => 'required|array|min:1',
            'items.*.invoice_id'    => 'required|exists:invoice,id',
            'items.*.quantity'      => 'required|numeric|min:0.001',
            'items.*.condition'     => 'required|in:good,damaged,expired',
            'items.*.restock'       => 'nullable|boolean',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('store_id', $storeId)
            ->firstOrFail();

        DB::transaction(function () use ($request, $order, $storeId) {
            $return = ReturnOrder::create([
                'store_id'      => $storeId,
                'return_number' => ReturnOrder::generateNumber($storeId),
                'order_id'      => $order->id,
                'customer_id'   => $order->customer_id,
                'return_type'   => $request->return_type,
                'reason'        => $request->reason,
                'notes'         => $request->notes,
                'return_date'   => now()->toDateString(),
                'status'        => 'pending',
            ]);

            $totalRefund = 0;

            foreach ($request->items as $itemData) {
                $invoice = Invoice::findOrFail($itemData['invoice_id']);
                $qty = (float) $itemData['quantity'];
                $refundAmount = $invoice->price * $qty;
                $totalRefund += $refundAmount;

                ReturnItem::create([
                    'return_id'          => $return->id,
                    'invoice_id'         => $invoice->id,
                    'product_variants_id' => $invoice->variant_id,
                    'product_name'       => $invoice->product_name,
                    'variant_name'       => $invoice->variant_name,
                    'quantity_returned'  => $qty,
                    'unit_price'         => $invoice->price,
                    'refund_amount'      => $refundAmount,
                    'condition'          => $itemData['condition'],
                    'restock'            => !empty($itemData['restock']),
                ]);
            }

            $return->update(['total_refund_amount' => $totalRefund]);
        });

        return redirect()->route('manager.operational.returns.index')
            ->with('success', 'Retur berhasil dibuat.');
    }

    public function show($id)
    {
        $storeId = session('selected_store');
        $return = ReturnOrder::where('store_id', $storeId)
            ->with('order', 'customer', 'processor', 'items.productVariant')
            ->findOrFail($id);

        return view('handai-manager.operational.returns.show', compact('return'));
    }

    public function approve($id)
    {
        $storeId = session('selected_store');
        $return = ReturnOrder::where('store_id', $storeId)->findOrFail($id);
        abort_if($return->status !== 'pending', 400, 'Retur tidak dalam status pending.');

        $return->update(['status' => 'approved']);

        return back()->with('success', 'Retur disetujui.');
    }

    public function reject($id)
    {
        $storeId = session('selected_store');
        $return = ReturnOrder::where('store_id', $storeId)->findOrFail($id);
        abort_if($return->status !== 'pending', 400, 'Retur tidak dalam status pending.');

        $return->update(['status' => 'rejected']);

        return back()->with('success', 'Retur ditolak.');
    }

    public function process($id)
    {
        $storeId = session('selected_store');
        $return = ReturnOrder::where('store_id', $storeId)->findOrFail($id);
        abort_if($return->status !== 'approved', 400, 'Retur harus disetujui terlebih dahulu.');

        DB::transaction(function () use ($return) {
            ReturnService::processReturn($return);
        });

        return back()->with('success', 'Retur berhasil diproses. Stok & refund telah dicatat.');
    }

    public function complete($id)
    {
        $storeId = session('selected_store');
        $return = ReturnOrder::where('store_id', $storeId)->findOrFail($id);
        abort_if($return->status !== 'processed', 400, 'Retur harus diproses terlebih dahulu.');

        $return->update(['status' => 'completed']);

        return back()->with('success', 'Retur selesai.');
    }
}
