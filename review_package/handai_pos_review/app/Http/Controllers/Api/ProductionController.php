<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ConversionHelper;
use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionHistory;
use App\Models\ProductionStockUsage;
use App\Models\ProductVariants;
use App\Models\Stock;
use App\Models\Unit;
use App\Services\AccountingService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionController extends Controller
{
    public function apiProductions(Request $request)
    {
        $storeId = $request->input('store_id') ?? session('selected_store');
        if (!$storeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'store_id tidak ditemukan'
            ], 400);
        }

        // Build query with filters
        $query = ProductionHistory::with([
            'pic',
            'productVariants.sku',
            'productVariants.product',
            'productVariants.options'
        ])
            ->where('store_id', $storeId);

        // Filter by PIC ID (changed from name)
        if ($request->has('pic_id') && $request->pic_id) {
            $query->where('pic_id', $request->pic_id);
        }

        // Filter by product ID (changed from name)
        if ($request->has('product_id') && $request->product_id) {
            $query->whereHas('productVariants.product', function ($q) use ($request) {
                $q->where('id', $request->product_id);
            });
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('production_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('production_date', '<=', $request->end_date);
        }

        // Filter by type of production (finished or semi)
        if ($request->has('prod_type') && $request->prod_type) {
            $query->where('prod_type', $request->prod_type);
        }

        $productions = $query->orderBy('production_date', 'desc')
            ->paginate($request->input('per_page', 50));

        $data = $productions->map(function ($p) {
            return [
                'id' => $p->id,
                'production_date' => date('Y-m-d', strtotime($p->production_date)),
                'employee_name' => $p->pic->name ?? null,
                'prod_type' => $p->prod_type,
                'product_name' => $p->product_name ?? $p->productVariants?->product?->name ?? null,
                'variant_options' => $p->productVariants?->options?->pluck('name')->toArray() ?? [],
                'sku_code' => $p->productVariants?->sku?->sku_code ?? null,
                'quantity_produced' => $p->quantity_produced,
                'ingredients' => $p->usages->map(fn($u) => [
                    'name' => $u->stock->name ?? $u->stock_name ?? null,
                    'quantity' => $u->quantity,
                    'unit_symbol' => $u->unit->symbol ?? '',
                ])->toArray()
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    // New endpoint to get filter dropdown data
    public function apiProductionFilters(Request $request)
    {
        $storeId = $request->input('store_id') ?? session('selected_store');
        if (!$storeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'store_id tidak ditemukan'
            ], 400);
        }

        // Get all PICs for this store
        $pics = Employee::where('store_id', $storeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get all products for this store
        $products = Product::where('store_id', $storeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'pics' => $pics,
                'products' => $products,
                'prod_types' => [
                    ['value' => 'finished', 'label' => 'Produk Jadi'],
                    ['value' => 'semi', 'label' => 'Setengah Jadi'],
                ]
            ]
        ]);
    }

    public function apiProduksiForm(Request $request)
    {
        $storeId = $request->input('store_id') ?? session('selected_store');

        $employees = Employee::where('store_id', $storeId)->get(['id', 'name']);

        $variants = ProductVariants::with(['product', 'options'])
            ->whereHas('product', fn($q) => $q->where('store_id', $storeId))
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'product_name' => $v->product->name,
                'variant_options' => $v->options->pluck('name')->toArray(),
            ]);

        // Get stocks + unit_id
        $stocks = Stock::where('store_id', $storeId)
            ->get(['id', 'name', 'unit_id']);

        // Get units + unit_type
        $units = Unit::all(['id', 'symbol', 'unit_type']);

        // prepare semi finished products for mobile form
        $semiFinished = \App\Models\SemiFinishedProduct::where('store_id', $storeId)
            ->get(['id', 'name', 'unit_id']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'employees' => $employees,
                'product_variants' => $variants,
                'stocks' => $stocks,
                'units' => $units,
                'semi_finished_products' => $semiFinished,
            ]
        ]);
    }


    public function produksiStore(Request $request)
    {
        $request->validate([
            'production_date' => 'required|date',
            'pic_id' => 'required|exists:employee,id',
            'prod_type' => 'required|in:finished,semi',
            'product_variants_id' => 'required_if:prod_type,finished|exists:product_variants,id',
            'semi_finished_product_id' => 'required_if:prod_type,semi|exists:semi_finished_products,id',
            'quantity_produced' => 'required|numeric|min:0.001',
            'use_bom' => 'required_if:prod_type,finished|in:yes,no',
            'store_id' => 'required|exists:store,id',
        ]);

        DB::beginTransaction();

        try {
            $type = $request->prod_type;
            $qtyProduced = $request->quantity_produced;

            if ($type === 'semi') {
                $sfp = \App\Models\SemiFinishedProduct::with('materials.stock')
                    ->where('store_id', $request->store_id)
                    ->findOrFail($request->semi_finished_product_id);
                ConversionHelper::preloadAll();
                $multiplier = $qtyProduced / $sfp->output_qty;
                // validate and consume similar to manager version
                $production = ProductionHistory::create([
                    'production_date' => $request->production_date,
                    'pic_id' => $request->pic_id,
                    'semi_finished_product_id' => $sfp->id,
                    'quantity_produced' => $qtyProduced,
                    'store_id' => $request->store_id,
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
                        $request->store_id, $stock, $usedQty, $mat->unit_id, $production->id
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
                            $request->store_id, $sfpTotalWage, $production->id, $sfp->name
                        );
                    } catch (\Exception $e) {
                        Log::warning('API Semi Production Wage journal failed: ' . $e->getMessage());
                    }

                    \App\Models\ProductionWage::create([
                        'store_id'              => $request->store_id,
                        'production_history_id' => $production->id,
                        'employee_id'           => $request->pic_id,
                        'recipe_sfp_id'         => $sfp->id,
                        'production_quantity'    => $qtyProduced,
                        'wage_per_unit'         => $sfpWagePerUnit,
                        'total_wage'            => $sfpTotalWage,
                        'production_date'       => $request->production_date,
                        'journal_id'            => $wageJournal?->id,
                    ]);
                }
            } else {
                $productVariantId = $request->product_variants_id;
                $production = ProductionHistory::create([
                    'production_date' => $request->production_date,
                    'pic_id' => $request->pic_id,
                    'product_variants_id' => $productVariantId,
                    'quantity_produced' => $qtyProduced,
                    'store_id' => $request->store_id,
                ]);
                $totalCost = 0;
            }
            if ($type === 'finished') {
                if ($request->use_bom === 'no') {
                    $manualIngredients = $request->input('manual_ingredients', []);

                    // Pre-load stocks to avoid N+1
                    $stockIds = collect($manualIngredients)->pluck('stock_id')->unique()->toArray();
                    $stocksMap = Stock::whereIn('id', $stockIds)->get()->keyBy('id');

                    foreach ($manualIngredients as $ingredient) {
                        $stock = $stocksMap->get($ingredient['stock_id']);
                        if (!$stock) continue;
                        $inputQty = $ingredient['quantity'];
                        $inputUnitId = $ingredient['unit_id'];
                        $stockUnitId = $stock->unit_id;

                        $conversionRate = ConversionHelper::getConversionRate($inputUnitId, $stockUnitId);

                        if ($conversionRate === null) {
                            throw new \Exception("Tidak ada konversi satuan dari unit ID $inputUnitId ke $stockUnitId.");
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
                            $request->store_id, $stock, $convertedQty, $inputUnitId, $production->id
                        );

                        $totalCost += $convertedQty * $stock->price_per_unit;
                    }
                }

                if ($request->use_bom === 'yes') {
                    $boms = Bom::with(['stock', 'semiFinishedProduct'])->where('product_variants_id', $productVariantId)->get();

                    if ($boms->isEmpty()) {
                        throw new \Exception("Produk ini belum memiliki resep (BOM).");
                    }

                    // Validate availability
                    foreach ($boms as $bom) {
                        if ($bom->semi_finished_product_id) {
                            $sfp = $bom->semiFinishedProduct;
                            if (!$sfp) throw new \Exception("Produk setengah jadi pada BOM tidak ditemukan.");
                            $conversionRate = ConversionHelper::getConversionRate($bom->unit_id, $sfp->unit_id);
                            if ($conversionRate === null) throw new \Exception("Konversi satuan tidak ditemukan.");
                            $requiredQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                            if ($sfp->current_qty < $requiredQty) {
                                throw new \Exception("Stok produk setengah jadi '{$sfp->name}' tidak mencukupi.");
                            }
                        } else {
                            $stock = $bom->stock;
                            $conversionRate = ConversionHelper::getConversionRate($bom->unit_id, $stock->unit_id);
                            if ($conversionRate === null) throw new \Exception("Konversi satuan tidak ditemukan.");
                            $requiredQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                            if ($stock->unit_qty < $requiredQty) {
                                throw new \Exception("Stok '{$stock->name}' tidak mencukupi.");
                            }
                        }
                    }

                    // Deduct and record
                    foreach ($boms as $bom) {
                        if ($bom->semi_finished_product_id) {
                            $sfp = $bom->semiFinishedProduct;
                            $conversionRate = ConversionHelper::getConversionRate($bom->unit_id, $sfp->unit_id);
                            $usedQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                            $sfp->current_qty -= $usedQty;
                            $sfp->save();

                            ProductionStockUsage::create([
                                'production_history_id' => $production->id,
                                'stock_id' => null,
                                'unit_id' => $bom->unit_id,
                                'stock_name' => $sfp->name,
                                'quantity' => $bom->quantity_required * $qtyProduced,
                            ]);

                            $totalCost += $usedQty * $sfp->price_per_unit;
                        } else {
                            $stock = $bom->stock;
                            $conversionRate = ConversionHelper::getConversionRate($bom->unit_id, $stock->unit_id);
                            $usedQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                            $stock->unit_qty -= $usedQty;
                            $stock->save();

                            ProductionStockUsage::create([
                                'production_history_id' => $production->id,
                                'stock_id' => $stock->id,
                                'unit_id' => $bom->unit_id,
                                'stock_name' => $stock->name,
                                'quantity' => $bom->quantity_required * $qtyProduced,
                            ]);

                            InventoryService::recordProductionConsumption(
                                $request->store_id, $stock, $usedQty, $bom->unit_id, $production->id
                            );

                            $totalCost += $usedQty * $stock->price_per_unit;
                        }
                    }
                }

                $productVariant = ProductVariants::find($productVariantId);

            if (!$productVariant) {
                throw new \Exception("Varian produk tidak ditemukan.");
            }

            // Calculate production wage
            $wagePerUnit = (float) ($productVariant->product->wage_per_unit ?? 0);
            $totalWage = round($wagePerUnit * $qtyProduced, 2);

            $totalCostWithWage = $totalCost + $totalWage;
            $oldQty = $productVariant->quantity;
            $oldHpp = $productVariant->hpp;
            $newQty = $oldQty + $qtyProduced;
            $newHpp = $newQty > 0
                ? round((($oldHpp * $oldQty) + $totalCostWithWage) / $newQty, 2)
                : $oldHpp;

            $productVariant->quantity = $newQty;
            $productVariant->hpp = $newHpp;
            $productVariant->save();

            // Record PRODUCTION_IN movement (finished goods increase)
            InventoryService::recordProductionOutput(
                $request->store_id, $productVariant, $qtyProduced, $newHpp, $production->id
            );

            // ── Accounting Journal: Production (Raw → Finished Goods) ──
            try {
                AccountingService::journalProduction(
                    $request->store_id, $totalCost, $production->id, $productVariant->product?->name
                );
            } catch (\Exception $e) {
                Log::warning('API Production Accounting journal failed: ' . $e->getMessage());
            }

            // ── Production Wage Record & Journal ──
            if ($totalWage > 0) {
                $wageJournal = null;
                try {
                    $wageJournal = AccountingService::journalProductionWage(
                        $request->store_id, $totalWage, $production->id, $productVariant->product?->name
                    );
                } catch (\Exception $e) {
                    Log::warning('API Production Wage journal failed: ' . $e->getMessage());
                }

                \App\Models\ProductionWage::create([
                    'store_id'              => $request->store_id,
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
        }

        // commit outside of type-specific logic so semi/finished both commit
        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Produksi berhasil disimpan!',
            'data' => $production,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}
}