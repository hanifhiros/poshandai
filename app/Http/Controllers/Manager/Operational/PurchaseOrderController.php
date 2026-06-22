<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\StockBatch;
use App\Helpers\ConversionHelper;
use App\Services\InventoryService;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $query = PurchaseOrder::with(['supplier', 'creator'])
            ->where('store_id', $storeId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('handai-manager.operational.purchase-order.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $storeId = session('selected_store');
        $suppliers = Supplier::orderBy('name')->get();
        $stocks = Stock::where('store_id', $storeId)->with('unit')->get();
        $units = Unit::all();

        return view('handai-manager.operational.purchase-order.create', compact('suppliers', 'stocks', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.stock_id' => 'required|exists:stock,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        $storeId = session('selected_store');

        DB::beginTransaction();
        try {
            $poNumber = PurchaseOrder::generatePoNumber($storeId);
            $totalAmount = 0;

            $itemsData = [];
            foreach ($request->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $totalPrice = $qty * $price;
                $totalAmount += $totalPrice;

                $itemsData[] = [
                    'stock_id' => $item['stock_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $totalPrice,
                ];
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'store_id' => $storeId,
                'status' => 'pending', // Draft
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsData as $item) {
                $po->items()->create($item);
            }

            DB::commit();
            return redirect()->route('manager.operational.po.index')
                ->with('success', 'Purchase Order ' . $poNumber . ' berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Purchase Order: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan Purchase Order: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $storeId = session('selected_store');
        $po = PurchaseOrder::with(['supplier', 'items.stock.unit', 'items.unit', 'creator'])
            ->where('store_id', $storeId)
            ->findOrFail($id);

        return view('handai-manager.operational.purchase-order.show', compact('po'));
    }

    public function approve($id)
    {
        $storeId = session('selected_store');
        $po = PurchaseOrder::where('store_id', $storeId)->findOrFail($id);

        if ($po->status !== 'pending') {
            return back()->with('error', 'Hanya PO dengan status Pending yang bisa disetujui.');
        }

        $po->update(['status' => 'approved']);

        return redirect()->route('manager.operational.po.show', $po->id)
            ->with('success', 'Purchase Order disetujui.');
    }

    public function receive($id)
    {
        $storeId = session('selected_store');
        $po = PurchaseOrder::with(['supplier', 'items.stock'])->where('store_id', $storeId)->findOrFail($id);

        if ($po->status !== 'approved') {
            return back()->with('error', 'Hanya PO dengan status Approved yang bisa diterima.');
        }

        DB::beginTransaction();
        try {
            foreach ($po->items as $item) {
                $stock = $item->stock;

                // Create stock batch
                $batch = StockBatch::create([
                    'stock_id'       => $item->stock_id,
                    'stock_name'     => $stock->name,
                    'unit_id'        => $item->unit_id,
                    'unit_qty'       => $item->quantity,
                    'cost'           => $item->total_price,
                    'buy_date'       => now()->toDateString(),
                    'store_id'       => $storeId,
                    'purchase_group' => $po->po_number,
                    'supplier_id'    => $po->supplier_id,
                    'supplier_name'  => $po->supplier->name,
                    'payment_method' => 'cash',
                    'expired_duration' => $stock->expired_duration ?? 30,
                    'paid_at'        => now(),
                ]);

                // Record inventory movement
                $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
                $batchConvertedQty = $conversionRate ? ($batch->unit_qty * $conversionRate) : $batch->unit_qty;

                InventoryService::recordPurchaseIn(
                    $storeId, $stock, $batch, $batchConvertedQty
                );

                // Recalculate stock values
                Stock::updateStockValues($item->stock_id);

                // Accounting Journal entry
                try {
                    AccountingService::journalPurchaseCash(
                        $storeId, $batch->cost, $batch->id, $stock->name
                    );
                } catch (\Throwable $e) {
                    Log::warning('PO Receive Accounting journal failed: ' . $e->getMessage());
                }
            }

            $po->update(['status' => 'received']);

            DB::commit();
            return redirect()->route('manager.operational.po.show', $po->id)
                ->with('success', 'Barang dari PO berhasil diterima dan dimasukkan ke stok.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to receive PO items: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menerima PO: ' . $e->getMessage()]);
        }
    }

    public function cancel($id)
    {
        $storeId = session('selected_store');
        $po = PurchaseOrder::where('store_id', $storeId)->findOrFail($id);

        if (in_array($po->status, ['received', 'cancelled'])) {
            return back()->with('error', 'PO yang sudah diterima atau dibatalkan tidak bisa diubah statusnya.');
        }

        $po->update(['status' => 'cancelled']);

        return redirect()->route('manager.operational.po.show', $po->id)
            ->with('success', 'Purchase Order berhasil dibatalkan.');
    }
}
