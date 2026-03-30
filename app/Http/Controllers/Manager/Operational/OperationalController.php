<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\ProductionHistory;
use App\Models\Unit;
Use App\Helpers\ConversionHelper;
use Illuminate\Http\Request;
use App\Models\ProductVariants;
use App\Models\Employee;
use App\Models\Stock;
use App\Models\Bom;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionStockUsage;
use App\Services\InventoryService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Log;
class OperationalController extends Controller
{
    public function produksi(Request $request)
{
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

    $query = ProductionHistory::with([
            'pic',
            'productVariants.sku',
            'productVariants.product',
            'productVariants.options',
            'semiFinishedProduct',
            'usages.stock',
            'usages.unit',
            'wages.employee',
        ])
        ->where('store_id', $selected_store_id);

    // Search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->whereHas('productVariants.product', function ($pq) use ($search) {
                $pq->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('semiFinishedProduct', function ($sq) use ($search) {
                $sq->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('pic', function ($eq) use ($search) {
                $eq->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('wages.employee', function ($ew) use ($search) {
                $ew->where('name', 'LIKE', "%{$search}%");
            });
        });
    }
    // Date range filter
    if ($request->filled('from')) {
        $query->whereDate('production_date', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('production_date', '<=', $request->to);
    }

    // Type filter: finished vs semi
    if ($request->filled('type')) {
        if ($request->type === 'finished') {
            $query->whereNull('semi_finished_product_id');
        } elseif ($request->type === 'semi') {
            $query->whereNotNull('semi_finished_product_id');
        }
    }

    // Clone the query (after filters) to calculate wage totals without affecting pagination
    $totalsQuery = (clone $query);

    $productions = $query->orderBy('production_date', 'desc')->paginate(10)->appends($request->query());

    // Summary stats (PIC count uses pic_ids array for accuracy)
    $allPicIds = ProductionHistory::where('store_id', $selected_store_id)
        ->pluck('pic_ids')
        ->map(function ($value) {
            if (is_array($value)) {
                return $value;
            }
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        })
        ->flatten()
        ->filter()
        ->unique();

    $prodStats = (object) [
        'total' => ProductionHistory::where('store_id', $selected_store_id)->count(),
        'total_qty' => ProductionHistory::where('store_id', $selected_store_id)->sum('quantity_produced'),
        'total_pic' => $allPicIds->count(),
    ];

    // Wage totals by type (finished / semi) based on current filter
    $totalWageFinished = 0;
    $totalWageSemi = 0;
    $totalWageAll = 0;

    $allFiltered = $totalsQuery->get();
    foreach ($allFiltered as $prod) {
        if ($prod->semi_finished_product_id) {
            $unitCost = 0;
            if ($prod->semiFinishedProduct) {
                $outQty = max(0.001, (float) ($prod->semiFinishedProduct->output_qty ?: 1));
                $unitCost = round((float) ($prod->semiFinishedProduct->labor_cost ?? 0) / $outQty, 2);
            }
            $total = round($unitCost * $prod->quantity_produced, 2);
            $totalWageSemi += $total;
            $totalWageAll += $total;
        } else {
            $unitCost = (float) ($prod->productVariants?->product->wage_per_unit ?? 0);
            $total = round($unitCost * $prod->quantity_produced, 2);
            $totalWageFinished += $total;
            $totalWageAll += $total;
        }
    }

    $employees = Employee::where('store_id', $selected_store_id)->orderBy('name')->get();
    $employeeMap = $employees->pluck('name', 'id')->toArray();

    return view('handai-manager.operational.produksi', compact('productions', 'selected_store', 'prodStats', 'employees', 'employeeMap'));
}

public function createProduksi()
{
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

    // ✅ Filter varian berdasarkan toko dari relasi product
    $productVariants = \App\Models\ProductVariants::with(['product', 'options' => function($query) {
        $query->orderBy('sort_order');
    }])
        ->whereHas('product', function ($query) use ($selected_store_id) {
            $query->where('store_id', $selected_store_id);
        })
        ->get()
        ->sortBy(function ($variant) {
            // Sort by product name, then by first option's sort_order
            return $variant->product->name . '|' . ($variant->options->first()?->sort_order ?? 0);
        });

    $employees = Employee::where('store_id', $selected_store_id)->get();
    $units = Unit::all();
    $stocks = Stock::with('unit')->where('store_id', $selected_store_id)->get();

    // Build wage map: variant_id => wage_per_unit (from parent product)
    $wageMap = [];
    foreach ($productVariants as $pv) {
        $wageMap[$pv->id] = (float) ($pv->product->wage_per_unit ?? 0);
    }

    // Build semi-finished wage map: sfp_id => { labor_cost_per_unit }
    $sfpWageMap = [];
    $sfpList = \App\Models\SemiFinishedProduct::where('store_id', $selected_store_id)->get();
    foreach ($sfpList as $sfp) {
        $outQty = max(0.001, (float) ($sfp->output_qty ?: 1));
        $sfpWageMap[$sfp->id] = [
            'labor_cost_per_unit' => round((float) ($sfp->labor_cost ?? 0) / $outQty, 2),
        ];
    }

    return view('handai-manager.operational.create-produksi', compact(
        'productVariants',
        'employees',
        'stocks',
        'selected_store',
        'units',
        'wageMap',
        'sfpWageMap'
    ));
}

public function produksiStore(Request $request)
{
    $request->validate([
        'production_date' => 'required|date',
        'pic_ids' => 'required|array|min:1',
        'pic_ids.*' => 'required|exists:employee,id',
        'production_lines' => 'required|array|min:1',
        'production_lines.*.type' => 'required|in:finished,semi',
        'production_lines.*.quantity_produced' => 'required|numeric|min:0.001',
        'production_lines.*.use_bom' => 'required_if:production_lines.*.type,finished|in:yes,no',
        'production_lines.*.product_variants_id' => 'required_if:production_lines.*.type,finished|exists:product_variants,id',
        'production_lines.*.semi_finished_product_id' => 'required_if:production_lines.*.type,semi|exists:semi_finished_products,id',
    ]);

    $picIds = $request->input('pic_ids', []);
    $productionLines = $request->input('production_lines', []);
    $picId = $picIds[0] ?? null;

    DB::beginTransaction();
    try {
        foreach ($productionLines as $line) {
            $type = $line['type'] ?? null;
            $qtyProduced = (float) ($line['quantity_produced'] ?? 0);

            if ($type === 'semi') {
                $sfpId = $line['semi_finished_product_id'] ?? null;
                $sfp = \App\Models\SemiFinishedProduct::find($sfpId);
                if (!$sfp) continue;

                $production = ProductionHistory::create([
                    'production_date' => $request->production_date,
                    'pic_id' => $picId,
                    'pic_ids' => $picIds,
                    'semi_finished_product_id' => $sfp->id,
                    'quantity_produced' => $qtyProduced,
                    'store_id' => session('selected_store'),
                    'product_name' => $sfp->name,
                    'variant_option_summary' => '',
                ]);

                // Calculate wage for semi-finished product
                $outQty = max(0.001, (float) ($sfp->output_qty ?: 1));
            $laborCost = (float) ($sfp->labor_cost ?? 0);
            if ($laborCost <= 0) {
                // fallback: assume some % of material cost (if available) - adjust as needed
                $laborCost = max(0, (float) ($sfp->material_cost ?? 0) * 0.1);
            }
            $wagePerUnit = round($laborCost / $outQty, 2);

                foreach ($picIds as $pid) {
                    \App\Models\ProductionWage::create([
                        'store_id' => session('selected_store'),
                        'production_history_id' => $production->id,
                        'employee_id' => $pid,
                        'recipe_sfp_id' => $sfp->id,
                        'production_quantity' => $qtyProduced,
                        'wage_per_unit' => $wagePerUnit,
                        'total_wage' => $payPerPic,
                        'production_date' => $request->production_date,
                    ]);
                }

                continue;
            }

            $productVariantId = $line['product_variants_id'] ?? null;
            $prodVar = ProductVariants::find($productVariantId);
            if (!$prodVar) continue;

            $production = ProductionHistory::create([
                'production_date' => $request->production_date,
                'pic_id' => $picId,
                'pic_ids' => $picIds,
                'product_variants_id' => $productVariantId,
                'quantity_produced' => $qtyProduced,
                'store_id' => session('selected_store'),
                'product_name' => $prodVar->product->name,
                'variant_option_summary' => $prodVar->options->pluck('name')->join(', '),
            ]);

            // Calculate wage for finished product
            $wagePerUnit = (float) ($prodVar->product->wage_per_unit ?? 0);
            if ($wagePerUnit <= 0) {
                // Fallback: use a percentage of HPP if wage isn't explicitly set
                $wagePerUnit = max(0, (float) ($prodVar->product->hpp ?? 0) * 0.1);
            }
            $totalWage = round($wagePerUnit * $qtyProduced, 2);
            $payPerPic = $picIds ? round($totalWage / count($picIds), 2) : 0;

            foreach ($picIds as $pid) {
                \App\Models\ProductionWage::create([
                    'store_id' => session('selected_store'),
                    'production_history_id' => $production->id,
                    'employee_id' => $pid,
                    'recipe_product_id' => $prodVar->product_id,
                    'production_quantity' => $qtyProduced,
                    'wage_per_unit' => $wagePerUnit,
                    'total_wage' => $payPerPic,
                    'production_date' => $request->production_date,
                ]);
            }
        }

        DB::commit();
        return redirect()->route('manager.operational.produksi')->with('success', 'Produksi berhasil disimpan!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}


}

