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

    $productions = $query->orderBy('production_date', 'desc')->paginate(10)->appends($request->query());

    // Summary stats
    $prodStats = ProductionHistory::where('store_id', $selected_store_id)
        ->selectRaw("count(*) as total")
        ->selectRaw("sum(quantity_produced) as total_qty")
        ->selectRaw("count(distinct pic_id) as total_pic")
        ->first();

    $employees = Employee::where('store_id', $selected_store_id)->orderBy('name')->get();

    return view('handai-manager.operational.produksi', compact('productions', 'selected_store', 'prodStats', 'employees'));
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

    DB::beginTransaction();
    try {
        $storeId = session('selected_store');
        $picIds = $request->input('pic_ids', []);
        $productionLines = $request->input('production_lines', []);

        // legacy support: use first line as basis (TODO: support multiple lines)
        $firstLine = $productionLines[0] ?? [];
        $type = $firstLine['type'] ?? null;
        $qtyProduced = (float) ($firstLine['quantity_produced'] ?? 0);
        $productVariantId = $firstLine['product_variants_id'] ?? null;
        $semiFinishedProductId = $firstLine['semi_finished_product_id'] ?? null;
        $picId = $picIds[0] ?? null;

        // handle semi finished product production
        if ($type === 'semi') {
            $sfp = \App\Models\SemiFinishedProduct::with('materials.stock')
                ->where('store_id', session('selected_store'))
                ->findOrFail($semiFinishedProductId);

            ConversionHelper::preloadAll();
            $multiplier = $qtyProduced / $sfp->output_qty;

            // validate availability
            foreach ($sfp->materials as $mat) {
                $stock = $mat->stock;
                if (!$stock) continue;
                $rate = ConversionHelper::getConversionRate($mat->unit_id, $stock->unit_id);
                if ($rate === null) {
                    throw new \Exception("Konversi satuan tidak ditemukan untuk {$stock->name}.");
                }
                $requiredQty = (float) $mat->quantity_required * $multiplier * $rate;
                if ($stock->unit_qty < $requiredQty) {
                    throw new \Exception("Stok '{$stock->name}' tidak mencukupi. Dibutuhkan: " . round($requiredQty,3) . ", tersedia: {$stock->unit_qty}");
                }
            }

            $production = ProductionHistory::create([
                'production_date' => $request->production_date,
                'pic_id' => $request->pic_id,
                'semi_finished_product_id' => $sfp->id,
                'quantity_produced' => $qtyProduced,
                'store_id' => session('selected_store'),
                'product_name' => $sfp->name,
                'variant_option_summary' => '',
            ]);

            $totalCost = 0;
            foreach ($sfp->materials as $mat) {
                $stock = $mat->stock;
                if (!$stock) continue;
                $rate = ConversionHelper::getConversionRate($mat->unit_id, $stock->unit_id) ?: 1;
                $usedQty = (float) $mat->quantity_required * $multiplier * $rate;
                $usedQtyOriginal = (float) $mat->quantity_required * $multiplier;

                $stock->unit_qty -= $usedQty;
                $stock->save();

                ProductionStockUsage::create([
                    'production_history_id' => $production->id,
                    'stock_id' => $stock->id,
                    'unit_id' => $mat->unit_id,
                    'stock_name' => $stock->name,
                    'quantity' => $usedQtyOriginal,
                ]);

                InventoryService::recordSemiFinishedConsumption(
                    session('selected_store'), $stock, $usedQty, $mat->unit_id, $production->id
                );

                $totalCost += $usedQty * (float) $stock->price_per_unit;
            }

            $sfp->current_qty += $qtyProduced;
            $sfp->save();
            $sfp->recalculateHpp();

            // ── Semi-finished Production Wage ──
            $sfpLaborCost = (float) ($sfp->labor_cost ?? 0);
            $sfpOutputQty = max(0.001, (float) ($sfp->output_qty ?: 1));
            $sfpWagePerUnit = round($sfpLaborCost / $sfpOutputQty, 2);
            $sfpTotalWage = round($sfpWagePerUnit * $qtyProduced, 2);

            if ($sfpTotalWage > 0) {
                $wageJournal = null;
                try {
                    $wageJournal = AccountingService::journalProductionWage(
                        session('selected_store'), $sfpTotalWage, $production->id, $sfp->name
                    );
                } catch (\Exception $e) {
                    Log::warning('Semi Production Wage journal failed: ' . $e->getMessage());
                }

                \App\Models\ProductionWage::create([
                    'store_id'              => session('selected_store'),
                    'production_history_id' => $production->id,
                    'employee_id'           => $picId,
                    'recipe_sfp_id'         => $sfp->id,
                    'production_quantity'    => $qtyProduced,
                    'wage_per_unit'         => $sfpWagePerUnit,
                    'total_wage'            => $sfpTotalWage,
                    'production_date'       => $request->production_date,
                    'journal_id'            => $wageJournal?->id,
                ]);
            }

            DB::commit();
            return redirect()->route('manager.operational.produksi')->with('success', "Produksi setengah jadi '{$sfp->name}' berhasil! +{$qtyProduced} {$sfp->unit?->symbol}");
        }

        // finished goods path
        $productVariantId = $productVariantId;

        $prodVar = ProductVariants::find($productVariantId);
        $production = ProductionHistory::create([
            'production_date' => $request->production_date,
            'pic_id' => $picId,
            'product_variants_id' => $productVariantId,
            'quantity_produced' => $qtyProduced,
            'store_id'=>session('selected_store'),
            'product_name' => $prodVar?->product->name,
            'variant_option_summary' => $prodVar?->options->pluck('name')->join(', '),
        ]);

        $totalCost = 0;

        if ($request->use_bom === 'yes') {
            $boms = Bom::with(['stock', 'semiFinishedProduct'])->where('product_variants_id', $productVariantId)->get();

            if ($boms->isEmpty()) {
                throw new \Exception("Produk ini belum memiliki resep (BOM). Silakan input manual atau buat resep terlebih dahulu.");
            }

            // Phase 1: Validate availability
            foreach ($boms as $bom) {
                if ($bom->semi_finished_product_id) {
                    // Semi-finished product ingredient
                    $sfp = $bom->semiFinishedProduct;
                    if (!$sfp) throw new \Exception("Produk setengah jadi pada BOM tidak ditemukan.");
                    $sfpUnitId = $sfp->unit_id;
                    $bomUnitId = $bom->unit_id;
                    $conversionRate = ConversionHelper::getConversionRate($bomUnitId, $sfpUnitId);
                    if ($conversionRate === null) {
                        throw new \Exception("Konversi satuan dari unit ID $bomUnitId ke $sfpUnitId tidak ditemukan.");
                    }
                    $requiredQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                    if ($sfp->current_qty < $requiredQty) {
                        throw new \Exception("Stok produk setengah jadi '{$sfp->name}' tidak mencukupi. Dibutuhkan: $requiredQty, tersedia: {$sfp->current_qty}");
                    }
                } else {
                    // Raw material ingredient
                    $stock = $bom->stock;
                    $stockUnitId = $stock->unit_id;
                    $bomUnitId = $bom->unit_id;
                    $conversionRate = ConversionHelper::getConversionRate($bomUnitId, $stockUnitId);
                    if ($conversionRate === null) {
                        throw new \Exception("Konversi satuan dari unit ID $bomUnitId ke $stockUnitId tidak ditemukan.");
                    }
                    $requiredQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                    if ($stock->unit_qty < $requiredQty) {
                        throw new \Exception("Stok '{$stock->name}' tidak mencukupi. Dibutuhkan: $requiredQty, tersedia: {$stock->unit_qty}");
                    }
                }
            }

            // Phase 2: Deduct and record
            foreach ($boms as $bom) {
                if ($bom->semi_finished_product_id) {
                    // Semi-finished product consumption
                    $sfp = $bom->semiFinishedProduct;
                    $sfpUnitId = $sfp->unit_id;
                    $bomUnitId = $bom->unit_id;
                    $conversionRate = ConversionHelper::getConversionRate($bomUnitId, $sfpUnitId);

                    $usedQty = $bom->quantity_required * $conversionRate * $qtyProduced;
                    $sfp->current_qty -= $usedQty;
                    $sfp->save();

                    ProductionStockUsage::create([
                        'production_history_id' => $production->id,
                        'stock_id' => null,
                        'unit_id' => $bom->unit_id,
                        'stock_name' => $sfp->name,
                        'quantity' => $bom->quantity_required * $qtyProduced,
                    ]);

                    $totalCost += $bom->quantity_required * $conversionRate * $qtyProduced * $sfp->price_per_unit;
                } else {
                    // Raw material consumption
                    $stock = $bom->stock;
                    $stockUnitId = $stock->unit_id;
                    $bomUnitId = $bom->unit_id;
                    $conversionRate = ConversionHelper::getConversionRate($bomUnitId, $stockUnitId);
                
                    $usedQty = $bom->quantity_required*$conversionRate * $qtyProduced;
                    $stock->unit_qty -= $usedQty;
                    $stock->save();

                    ProductionStockUsage::create([
                        'production_history_id' => $production->id,
                        'stock_id' => $stock->id,
                        'unit_id' => $bom->unit_id,
                        'stock_name' => $stock->name,
                        'quantity' => $bom->quantity_required * $qtyProduced,
                    ]);

                    // Record PRODUCTION_OUT movement
                    InventoryService::recordProductionConsumption(
                        session('selected_store'), $stock, $usedQty, $bom->unit_id, $production->id
                    );

                    $totalCost += $bom->quantity_required * $conversionRate * $qtyProduced * $stock->price_per_unit;
                }
            }
        } else {
            // Pre-load all stocks for manual ingredients
            $manualIngredients = $request->manual_ingredients ?? [];
            $manualStockIds = collect($manualIngredients)->pluck('stock_id')->unique()->toArray();
            $manualStocksMap = Stock::whereIn('id', $manualStockIds)->get()->keyBy('id');

            foreach ($manualIngredients as $ingredient) {
                $stock = $manualStocksMap->get($ingredient['stock_id']);
                if (!$stock) continue;
                $inputQty = $ingredient['quantity'];
                $inputUnitId = $ingredient['unit_id'];
                $stockUnitId = $stock->unit_id;

                $conversionRate = \App\Helpers\ConversionHelper::getConversionRate($inputUnitId, $stockUnitId);

                if ($conversionRate === null) {
                    throw new \Exception("Tidak ada konversi satuan dari {$inputUnitId} ke {$stockUnitId} untuk '{$stock->name}'");
                }

                $convertedQty = $inputQty * $conversionRate;

                if ($stock->unit_qty < $convertedQty) {
                    throw new \Exception("Stok '{$stock->name}' tidak mencukupi. Dibutuhkan: $convertedQty, tersedia: {$stock->unit_qty}");
                }

                $stock->unit_qty -= $convertedQty;
                $stock->save();

                ProductionStockUsage::create([
                    'production_history_id' => $production->id,
                    'stock_id' => $stock->id,
                    'unit_id' => $inputUnitId,
                    'stock_name' => $stock->name,
                    'quantity' => $inputQty,
                ]);

                // Record PRODUCTION_OUT movement
                InventoryService::recordProductionConsumption(
                    session('selected_store'), $stock, $convertedQty, $inputUnitId, $production->id
                );

                $totalCost += $convertedQty * $stock->price_per_unit;
            }
        }

        $productVariant = ProductVariants::find($productVariantId);

        if (!$productVariant) {
            throw new \Exception("Varian produk tidak ditemukan.");
        }

        // Calculate production wage
        $wagePerUnit = (float) ($productVariant->product->wage_per_unit ?? 0);
        $totalWage = round($wagePerUnit * $qtyProduced, 2);

        // 💰 Hitung HPP baru (material cost + wage)
        $totalCostWithWage = $totalCost + $totalWage;
        $oldQty = $productVariant->quantity;
        $oldHpp = $productVariant->hpp;
        $newQty = $oldQty + $qtyProduced;
        $newHpp = $newQty > 0 ? round((($oldHpp * $oldQty) + ($totalCostWithWage)) / $newQty, 2) : $oldHpp;

        $productVariant->quantity = $newQty;
        $productVariant->hpp = $newHpp;
        $productVariant->save();

        // Record PRODUCTION_IN movement (finished goods increase)
        InventoryService::recordProductionOutput(
            session('selected_store'), $productVariant, $qtyProduced, $newHpp, $production->id
        );

        // ── Accounting Journal: Production (Raw → Finished Goods) ──
        try {
            AccountingService::journalProduction(
                session('selected_store'), $totalCost, $production->id, $productVariant->product?->name
            );
        } catch (\Exception $e) {
            Log::warning('Production Accounting journal failed: ' . $e->getMessage());
        }

        // ── Production Wage Record & Journal ──
        if ($totalWage > 0) {
            $wageJournal = null;
            try {
                $wageJournal = AccountingService::journalProductionWage(
                    session('selected_store'), $totalWage, $production->id, $productVariant->product?->name
                );
            } catch (\Exception $e) {
                Log::warning('Production Wage journal failed: ' . $e->getMessage());
            }

            \App\Models\ProductionWage::create([
                'store_id'              => session('selected_store'),
                'production_history_id' => $production->id,
                'employee_id'           => $request->pic_id,
                'recipe_product_id'     => $productVariant->product_id,
                'production_quantity'    => $qtyProduced,
                'wage_per_unit'         => $wagePerUnit,
                'total_wage'            => $totalWage,
                'production_date'       => $request->production_date,
                'journal_id'            => $wageJournal?->id,
            ]);
        }

        DB::commit();
        return redirect()->route('manager.operational.produksi')->with('success', 'Produksi berhasil disimpan!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}



}

