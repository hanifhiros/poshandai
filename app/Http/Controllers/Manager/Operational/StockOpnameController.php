<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Employee;
use App\Models\ProductVariants;
use App\Models\Unit;
use App\Models\StockBatch;
use App\Services\InventoryService;
use App\Services\AccountingService;
use App\Helpers\ConversionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $query = StockAdjustment::with(['stock', 'productVariant', 'unit', 'pic'])
            ->where('store_id', $storeId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%{$s}%")
                  ->orWhere('adjustment_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $adjustments = $query->orderByDesc('adjustment_date')->paginate(20)->withQueryString();

        // Stats
        $now = Carbon::now();
        $totalAdjMonth = StockAdjustment::where('store_id', $storeId)
            ->whereMonth('adjustment_date', $now->month)
            ->count();
        $surplusMonth = StockAdjustment::where('store_id', $storeId)
            ->whereMonth('adjustment_date', $now->month)
            ->where('difference', '>', 0)
            ->sum('total_cost_impact');
        $deficitMonth = StockAdjustment::where('store_id', $storeId)
            ->whereMonth('adjustment_date', $now->month)
            ->where('difference', '<', 0)
            ->sum('total_cost_impact');

        return view('handai-manager.operational.stock-opname.index', compact(
            'selected_store', 'adjustments', 'totalAdjMonth', 'surplusMonth', 'deficitMonth'
        ));
    }

    public function create()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $stocks = Stock::where('store_id', $storeId)->with('unit')->orderBy('name')->get();
        $employees = Employee::where('store_id', $storeId)->orderBy('name')->get();
        $units = Unit::all();

        return view('handai-manager.operational.stock-opname.create', compact(
            'selected_store', 'stocks', 'employees', 'units'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.stock_id'   => 'required|exists:stock,id',
            'items.*.actual_qty' => 'required|numeric|min:0',
            'adjustment_date' => 'required|date',
        ]);

        $storeId = session('selected_store');

        DB::transaction(function () use ($request, $storeId) {
            $adjustmentNumber = 'ADJ-' . date('Ymd') . '-' . str_pad(
                StockAdjustment::whereDate('adjustment_date', today())
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->count() + 1,
                3, '0', STR_PAD_LEFT
            );

            // Pre-load all stocks to avoid N+1
            $stockIds = collect($request->items)->pluck('stock_id')->unique()->toArray();
            $stocksMap = Stock::whereIn('id', $stockIds)->with('unit')->get()->keyBy('id');

            foreach ($request->items as $item) {
                $stock = $stocksMap->get($item['stock_id']);
                if (!$stock) continue;

                // Use stock.unit_qty as system qty (already in base unit, accounts for all movements)
                $systemQty = $stock->unit_qty;
                $actualQty = (float) $item['actual_qty'];
                $difference = $actualQty - $systemQty;

                if (abs($difference) < 0.001) continue; // No adjustment needed

                $costPerUnit = $stock->price_per_unit ?? 0;
                $totalCostImpact = $difference * $costPerUnit;

                $adjustment = StockAdjustment::create([
                    'store_id'           => $storeId,
                    'adjustment_date'    => $request->adjustment_date,
                    'adjustment_number'  => $adjustmentNumber,
                    'stock_id'           => $stock->id,
                    'item_type'          => 'stock',
                    'item_name'          => $stock->name,
                    'system_qty'         => $systemQty,
                    'actual_qty'         => $actualQty,
                    'difference'         => $difference,
                    'unit_id'            => $stock->unit_id,
                    'cost_per_unit'      => $costPerUnit,
                    'total_cost_impact'  => $totalCostImpact,
                    'reason'             => $item['reason'] ?? null,
                    'notes'              => $request->notes,
                    'pic_id'             => $request->pic_id,
                    'created_by'         => Auth::id(),
                    'status'             => 'completed',
                ]);

                // ── Actually update stock quantity ──
                $stock->unit_qty = $actualQty;
                $stock->save();

                // ── Record stock movement ──
                $reason = $item['reason'] ?? 'Stock opname adjustment';
                InventoryService::recordAdjustment(
                    $storeId,
                    $stock->id,
                    null,
                    $difference, // positive = surplus, negative = deficit
                    $stock->unit_id,
                    "Opname {$adjustmentNumber}: {$reason}",
                    $costPerUnit
                );

                // ── Accounting journal ──
                $absValue = abs($totalCostImpact);
                if ($absValue > 0) {
                    try {
                        AccountingService::journalAdjustment(
                            $storeId,
                            $absValue,
                            $difference > 0, // isPositive
                            "Opname {$adjustmentNumber}: {$stock->name}",
                            $stock->id
                        );
                    } catch (\Exception $e) {
                        Log::warning('Opname journal failed: ' . $e->getMessage());
                    }
                }
            }
        });

        return redirect()->route('manager.operational.stock-opname.index')
            ->with('success', 'Stock opname berhasil dicatat.');
    }
}
