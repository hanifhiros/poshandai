<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionHistory;
use App\Models\ProductVariants;
use App\Models\Bom;
use App\Models\ProductionStockUsage;
use App\Models\Employee;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryService;
use App\Services\AccountingService;
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

        $productions = $query->orderBy('production_date', 'desc')->get();

        $data = $productions->map(function ($p) {
            return [
                'id' => $p->id,
                'production_date' => date('Y-m-d', strtotime($p->production_date)),
                'employee_name' => $p->pic->name ?? null,
                'product_name' => $p->productVariants?->product?->name ?? null,
                'variant_options' => $p->productVariants?->options?->pluck('name')->toArray() ?? [],
                'sku_code' => $p->productVariants?->sku?->sku_code ?? null,
                'quantity_produced' => $p->quantity_produced,
                'ingredients' => $p->usages->map(fn($u) => [
                    'name' => $u->stock->name ?? null,
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
        $stocks = \App\Models\Stock::where('store_id', $storeId)
            ->get(['id', 'name', 'unit_id']);

        // Get units + unit_type
        $units = \App\Models\Unit::all(['id', 'symbol', 'unit_type']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'employees' => $employees,
                'product_variants' => $variants,
                'stocks' => $stocks,
                'units' => $units,
            ]
        ]);
    }


    public function produksiStore(Request $request)
    {
        $request->validate([
            'production_date' => 'required|date',
            'pic_id' => 'required|exists:employee,id',
            'product_variants_id' => 'required|exists:product_variants,id',
            'quantity_produced' => 'required|integer|min:1',
            'use_bom' => 'required|in:yes,no',
            'store_id' => 'required|exists:store,id',
        ]);

        DB::beginTransaction();

        try {
            $productVariantId = $request->product_variants_id;
            $qtyProduced = $request->quantity_produced;

            $production = ProductionHistory::create([
                'production_date' => $request->production_date,
                'pic_id' => $request->pic_id,
                'product_variants_id' => $productVariantId,
                'quantity_produced' => $qtyProduced,
                'store_id' => $request->store_id,
            ]);

            $totalCost = 0;
            if ($request->use_bom === 'no') {
                $manualIngredients = $request->input('manual_ingredients', []);

                foreach ($manualIngredients as $ingredient) {
                    $stock = \App\Models\Stock::find($ingredient['stock_id']);
                    $inputQty = $ingredient['quantity'];
                    $inputUnitId = $ingredient['unit_id'];
                    $stockUnitId = $stock->unit_id;

                    $conversionRate = \App\Helpers\ConversionHelper::getConversionRate($inputUnitId, $stockUnitId);

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
                $boms = Bom::where('product_variants_id', $productVariantId)->get();

                if ($boms->isEmpty()) {
                    throw new \Exception("Produk ini belum memiliki resep (BOM).");
                }

                foreach ($boms as $bom) {
                    $stock = $bom->stock;
                    $conversionRate = \App\Helpers\ConversionHelper::getConversionRate(
                        $bom->unit_id,
                        $stock->unit_id
                    );

                    if ($conversionRate === null) {
                        throw new \Exception("Konversi satuan tidak ditemukan.");
                    }

                    $requiredQty = $bom->quantity_required * $qtyProduced * $conversionRate;

                    if ($stock->unit_qty < $requiredQty) {
                        throw new \Exception("Stok '{$stock->name}' tidak mencukupi.");
                    }
                }

                foreach ($boms as $bom) {
                    $stock = $bom->stock;
                    $conversionRate = \App\Helpers\ConversionHelper::getConversionRate(
                        $bom->unit_id,
                        $stock->unit_id
                    );

                    $usedQty = $bom->quantity_required * $qtyProduced * $conversionRate;
                    $stock->unit_qty -= $usedQty;
                    $stock->save();

                    ProductionStockUsage::create([
                        'production_history_id' => $production->id,
                        'stock_id' => $stock->id,
                        'unit_id' => $bom->unit_id,
                        'quantity' => $bom->quantity_required * $qtyProduced,
                    ]);

                    // Record PRODUCTION_OUT movement
                    InventoryService::recordProductionConsumption(
                        $request->store_id, $stock, $usedQty, $bom->unit_id, $production->id
                    );

                    $totalCost += $usedQty * $stock->price_per_unit;
                }
            }

            $productVariant = \App\Models\ProductVariants::find($productVariantId);

            if (!$productVariant) {
                throw new \Exception("Varian produk tidak ditemukan.");
            }

            $oldQty = $productVariant->quantity;
            $oldHpp = $productVariant->hpp;
            $newQty = $oldQty + $qtyProduced;
            $newHpp = $newQty > 0
                ? round((($oldHpp * $oldQty) + $totalCost) / $newQty, 2)
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