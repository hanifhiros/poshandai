<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\WasteLog;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Employee;
use App\Models\ProductVariants;
use App\Models\Unit;
use App\Services\InventoryService;
use App\Services\AccountingService;
use App\Helpers\ConversionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WasteController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $query = WasteLog::with(['stock', 'productVariant', 'unit', 'pic'])
            ->where('store_id', $storeId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('waste_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('waste_date', '<=', $request->date_to);
        }

        $wasteLogs = $query->orderByDesc('waste_date')->paginate(20)->withQueryString();

        // Stats
        $now = Carbon::now();
        $totalWasteMonth = WasteLog::where('store_id', $storeId)
            ->whereMonth('waste_date', $now->month)
            ->whereYear('waste_date', $now->year)
            ->sum('total_cost');
        $totalWasteCount = WasteLog::where('store_id', $storeId)
            ->whereMonth('waste_date', $now->month)
            ->whereYear('waste_date', $now->year)
            ->count();
        $topWasteReason = WasteLog::where('store_id', $storeId)
            ->whereMonth('waste_date', $now->month)
            ->selectRaw('reason, COUNT(*) as cnt')
            ->groupBy('reason')
            ->orderByDesc('cnt')
            ->first();

        $reasons = WasteLog::reasons();

        return view('handai-manager.operational.waste.index', compact(
            'selected_store', 'wasteLogs', 'totalWasteMonth', 'totalWasteCount',
            'topWasteReason', 'reasons'
        ));
    }

    public function create()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $stocks = Stock::where('store_id', $storeId)->orderBy('name')->get();
        $variants = ProductVariants::whereHas('product', fn($q) => $q->where('store_id', $storeId))
            ->with('product')
            ->get();
        $employees = Employee::where('store_id', $storeId)->orderBy('name')->get();
        $units = Unit::all();
        $reasons = WasteLog::reasons();

        return view('handai-manager.operational.waste.create', compact(
            'selected_store', 'stocks', 'variants', 'employees', 'units', 'reasons'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_type'  => 'required|in:stock,product',
            'quantity'   => 'required|numeric|min:0.001',
            'reason'     => 'required|string',
            'waste_date' => 'required|date',
        ]);

        $storeId = session('selected_store');

        DB::transaction(function () use ($request, $storeId) {
            $costPerUnit = 0;
            $itemName = '';
            $unitId = $request->unit_id;

            if ($request->item_type === 'stock') {
                $stock = Stock::findOrFail($request->stock_id);
                $itemName = $stock->name;
                $costPerUnit = $stock->price_per_unit ?? 0;
                $unitId = $unitId ?? $stock->unit_id;
            } else {
                $variant = ProductVariants::with('product')->findOrFail($request->product_variant_id);
                $itemName = $variant->product->name . ' - ' . ($variant->variantLabel ?? 'Default');
                $costPerUnit = $variant->hpp ?? 0;
            }

            $totalCost = $costPerUnit * $request->quantity;

            $waste = WasteLog::create([
                'store_id'            => $storeId,
                'waste_date'          => $request->waste_date,
                'item_type'           => $request->item_type,
                'stock_id'            => $request->stock_id,
                'product_variant_id'  => $request->product_variant_id,
                'item_name'           => $itemName,
                'quantity'            => $request->quantity,
                'unit_id'             => $unitId,
                'cost_per_unit'       => $costPerUnit,
                'total_cost'          => $totalCost,
                'reason'              => $request->reason,
                'notes'               => $request->notes,
                'pic_id'              => $request->pic_id,
                'created_by'          => Auth::id(),
            ]);

            // ── Deduct stock / product quantity ──
            if ($request->item_type === 'stock') {
                $stock = Stock::findOrFail($request->stock_id);

                // Convert waste qty to stock's base unit if different
                $wasteQtyInStockUnit = $request->quantity;
                if ($unitId && $unitId != $stock->unit_id) {
                    $rate = ConversionHelper::getConversionRate($unitId, $stock->unit_id);
                    if ($rate) {
                        $wasteQtyInStockUnit = $request->quantity * $rate;
                    }
                }

                $stock->unit_qty = max(0, $stock->unit_qty - $wasteQtyInStockUnit);
                $stock->save();

                // Record stock movement
                InventoryService::recordWasteStock(
                    $storeId, $stock, $wasteQtyInStockUnit, $waste->id, $request->reason
                );
            } else {
                $variant = ProductVariants::findOrFail($request->product_variant_id);
                $variant->quantity = max(0, $variant->quantity - $request->quantity);
                $variant->save();

                // Record product movement
                InventoryService::recordWasteProduct(
                    $storeId, $variant, $request->quantity, $waste->id, $request->reason
                );
            }

            // ── Accounting Journal: Waste expense ──
            if ($totalCost > 0) {
                try {
                    AccountingService::journalWaste(
                        $storeId,
                        $totalCost,
                        $itemName,
                        $request->item_type,
                        $waste->id
                    );
                } catch (\Exception $e) {
                    Log::warning('Waste journal failed: ' . $e->getMessage());
                }
            }
        });

        return redirect()->route('manager.operational.waste.index')
            ->with('success', 'Waste berhasil dicatat.');
    }

    public function destroy(WasteLog $waste)
    {
        abort_if($waste->store_id != session('selected_store'), 403, 'Unauthorized access.');

        $waste->delete();

        return redirect()->route('manager.operational.waste.index')
            ->with('success', 'Record waste berhasil dihapus.');
    }
}
