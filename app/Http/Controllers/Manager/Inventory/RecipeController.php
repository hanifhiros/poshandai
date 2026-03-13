<?php

namespace App\Http\Controllers\Manager\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\Stock;
use App\Models\Bom;
use App\Models\Unit;
use App\Models\SemiFinishedProduct;
use App\Helpers\ConversionHelper;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Helper: calculate HPP per variant from BOMs.
     */
    private function calculateHppPerVariant($boms)
    {
        ConversionHelper::preloadAll();
        $hppMap = [];

        foreach ($boms as $bom) {
            $vid = $bom->product_variants_id;
            if (!isset($hppMap[$vid])) $hppMap[$vid] = 0;

            // BOM line can be raw material (stock) or semi-finished product
            if ($bom->semi_finished_product_id) {
                $sfp = $bom->semiFinishedProduct;
                if (!$sfp) continue;
                $rate = ConversionHelper::getConversionRate($bom->unit_id, $sfp->unit_id);
                $rate = $rate ?: 1;
                $cost = (float) $bom->quantity_required * $rate * (float) $sfp->price_per_unit;
            } else {
                $stock = $bom->stock;
                if (!$stock) continue;
                $rate = ConversionHelper::getConversionRate($bom->unit_id, $stock->unit_id);
                $rate = $rate ?: 1;
                $cost = (float) $bom->quantity_required * $rate * (float) $stock->price_per_unit;
            }

            $hppMap[$vid] += $cost;
        }

        return $hppMap;
    }

    public function index()
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

        $boms = Bom::with([
            'stock.unit',
            'semiFinishedProduct.unit',
            'unit',
            'productVariants.product',
            'productVariants.options.attribute',
        ])->where('store_id', $selected_store_id)->get();

        // Group by output (product or semi) then variant id
        $groupedBoms = [];
        foreach ($boms as $bom) {
            if ($bom->output_semi_finished_product_id) {
                $key = 'semi:' . $bom->output_semi_finished_product_id;
            } else {
                $key = 'prod:' . $bom->product_id;
            }
            if (!isset($groupedBoms[$key])) {
                $groupedBoms[$key] = [];
            }
            $vid = $bom->product_variants_id;
            $groupedBoms[$key][$vid][] = $bom;
        }

        // Build name map for each output key
        $outputNames = [];
        foreach ($groupedBoms as $key => $group) {
            [$type, $id] = explode(':', $key);
            if ($type === 'semi') {
                $sfp = SemiFinishedProduct::find($id);
                $outputNames[$key] = $sfp?->name ?? 'Produk Setengah Jadi';
            } else {
                // finished products
                // use first bom in group to get product name
                $first = collect($group)->flatten(1)->first();
                $outputNames[$key] = $first?->productVariants?->product?->name ?? 'Produk Tidak Diketahui';
            }
        }

        // Variant labels (same as before)
        $sizesInfo = $boms->mapWithKeys(function ($bom) {
            $variant = $bom->productVariants;
            if (!$variant) return [$bom->product_variants_id => 'Varian Tidak Diketahui'];
            $combinations = $variant->options->map(function ($option) {
                return ($option->attribute?->name ?? '?') . ": {$option->name}";
            })->implode(', ');
            return [$bom->product_variants_id => $combinations ?: 'Varian Tidak Diketahui'];
        })->toArray();

        // Variant sell prices
        $variantPrices = [];
        foreach ($boms as $bom) {
            $v = $bom->productVariants;
            if ($v) $variantPrices[$v->id] = (float) $v->price;
        }

        // Variant sell prices
        $variantPrices = [];
        foreach ($boms as $bom) {
            $v = $bom->productVariants;
            if ($v) $variantPrices[$v->id] = (float) $v->price;
        }

        // HPP per variant
        $hppPerVariant = $this->calculateHppPerVariant($boms);
        // remove any entries without a numeric variant id (used by semi outputs)
        $hppPerVariant = array_filter($hppPerVariant, function($val, $key) {
            return is_numeric($key) && $key !== '' && $key !== null;
        }, ARRAY_FILTER_USE_BOTH);

        return view('handai-manager.inventory.recipes.index', compact(
            'groupedBoms', 'sizesInfo', 'selected_store', 'boms',
            'hppPerVariant', 'variantPrices', 'outputNames'
        ));
    }

    public function destroy($variantId)
    {
        $storeId = session('selected_store');
        $deleted = Bom::where('product_variants_id', $variantId)
            ->where('store_id', $storeId)
            ->delete();

        if ($deleted === 0) {
            return redirect()->route('manager.inventory.recipes.index')
                ->withErrors(['error' => 'Resep tidak ditemukan atau bukan milik outlet ini.']);
        }

        return redirect()->route('manager.inventory.recipes.index')
            ->with('success', 'Resep berhasil dihapus.');
    }

    public function destroyProduct($outputId)
    {
        $storeId = session('selected_store');
        $type = request('output_type','finished');
        if ($type === 'finished') {
            $deleted = Bom::where('product_id', $outputId)
                ->where('store_id', $storeId)
                ->delete();
        } else {
            $deleted = Bom::where('output_semi_finished_product_id', $outputId)
                ->where('store_id', $storeId)
                ->delete();
        }

        if ($deleted === 0) {
            return redirect()->route('manager.inventory.recipes.index')
                ->withErrors(['error' => 'Resep tidak ditemukan atau bukan milik outlet ini.']);
        }

        return redirect()->route('manager.inventory.recipes.index')
            ->with('success', 'Semua resep produk ini berhasil dihapus.');
    }

    public function create()
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

        $products = Product::where('store_id', $selected_store_id)->get();
        $stocks = Stock::with('unit')->where('store_id', $selected_store_id)->get();
        $units = Unit::all();

        // Semi-finished products available as ingredients
        $semiFinishedProducts = SemiFinishedProduct::with('unit')
            ->where('store_id', $selected_store_id)->orderBy('name')->get();
        $sfpPrices = [];
        foreach ($semiFinishedProducts as $sfp) {
            $sfpPrices[$sfp->id] = [
                'price_per_unit' => (float) $sfp->price_per_unit,
                'unit_id'        => $sfp->unit_id,
                'name'           => $sfp->name,
                'unit_type'      => $sfp->unit?->unit_type ?? '',
                'labor_cost'     => (float) ($sfp->labor_cost ?? 0),
                'output_qty'     => (float) ($sfp->output_qty ?: 1),
            ];
        }

        // All variants grouped by product with variant summary and price
        $allVariants = ProductVariants::with(['product', 'options.attribute'])
            ->whereHas('product', fn($q) => $q->where('store_id', $selected_store_id))
            ->get();

        $variantsByProduct = [];
        foreach ($allVariants as $v) {
            $variantsByProduct[$v->product_id][] = [
                'id'    => $v->id,
                'label' => $v->variantSummary() ?? 'Tanpa Varian',
                'price' => (float) $v->price,
            ];
        }

        // Stock prices info for HPP calculation on frontend
        $stockPrices = [];
        foreach ($stocks as $s) {
            $stockPrices[$s->id] = [
                'price_per_unit' => (float) $s->price_per_unit,
                'unit_id'        => $s->unit_id,
                'name'           => $s->name,
                'unit_type'      => $s->unit?->unit_type ?? '',
            ];
        }

        // Unit conversions for frontend HPP
        $conversions = \App\Models\UnitConversion::all()->map(fn($c) => [
            'from' => $c->from_unit_id,
            'to'   => $c->to_unit_id,
            'rate'  => (float) $c->conversion_rate,
        ]);

        return view('handai-manager.inventory.recipes.create', compact(
            'products', 'stocks', 'units', 'variantsByProduct',
            'selected_store', 'stockPrices', 'conversions',
            'semiFinishedProducts', 'sfpPrices'
        ));
    }

    public function edit($outputId)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

        $type = request('output_type', 'finished');
        $isSemi = $type === 'semi';

        $stocks = Stock::with('unit')->where('store_id', $selected_store_id)->get();
        $units = Unit::all();

        // Semi-finished products (for ingredient or output lists)
        $semiFinishedProducts = SemiFinishedProduct::with('unit')
            ->where('store_id', $selected_store_id)->orderBy('name')->get();
        $sfpPrices = [];
        foreach ($semiFinishedProducts as $sfp) {
            $sfpPrices[$sfp->id] = [
                'price_per_unit' => (float) $sfp->price_per_unit,
                'unit_id'        => $sfp->unit_id,
                'name'           => $sfp->name,
                'unit_type'      => $sfp->unit?->unit_type ?? '',
            ];
        }

        // Determine product variants depending on type
        $variants = [];
        if (!$isSemi) {
            $product = Product::findOrFail($outputId);
            $variants = ProductVariants::with(['options.attribute'])
                ->where('product_id', $outputId)
                ->get()
                ->map(fn($v) => [
                    'id'    => $v->id,
                    'label' => $v->variantSummary() ?? 'Tanpa Varian',
                    'price' => (float) $v->price,
                ]);
        } else {
            // for semi output we don't load product (no variants)
            $product = null;
        }
        // Load the semi-finished product so the view can use its output_qty / labor_cost
        $sfpOutput = $isSemi ? \App\Models\SemiFinishedProduct::with('unit')->find($outputId) : null;

        // Existing BOMs grouped by variant. If editing semi output, filter by output_semi_finished_product_id
        $bomsQuery = Bom::with(['stock.stockCategory', 'semiFinishedProduct']);
        // if a store has been selected in session, restrict to it; otherwise issue a warning
        if ($selected_store_id) {
            $bomsQuery->where('store_id', $selected_store_id);
        } else {
            try {
                \Log::warning('RecipeController::edit no selected_store in session, skipping store_id filter', ['output_id'=>$outputId]);
            } catch (\Throwable $e) { }
        }
        if ($isSemi) {
            $bomsQuery->where('output_semi_finished_product_id', $outputId);
        } else {
            $bomsQuery->where('product_id', $outputId);
        }
        $boms = $bomsQuery->get();

        // always log store id and variant list for diagnostics
        try {
            \Log::info('RecipeController::edit store_id', ['selected_store_id' => $selected_store_id]);
            \Log::info('RecipeController::edit variants_sent', ['isSemi'=>$isSemi,'variants'=>$variants]);
            \Log::info('RecipeController::edit boms_loaded', ['count'=>$boms->count()]);
        } catch (\Throwable $e) {}

        // Debug: log what BOMs were loaded for this edit request
        try {
            \Log::info('RecipeController::edit boms_loaded', [
                'output_id' => $outputId,
                'is_semi' => $isSemi,
                'store_id' => $selected_store_id,
                'boms_count' => $boms->count(),
            ]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        $existingBoms = [];
        foreach ($boms as $bom) {
            $type = 'bahan';
            // Check if this BOM line uses a semi-finished product
            if ($bom->semi_finished_product_id) {
                $type = 'semi_finished';
            } else {
                try {
                    $catName = $bom->stock->stockCategory?->stock_category_name ?? '';
                    if ($catName && stripos($catName, 'kemasan') !== false) {
                        $type = 'kemasan';
                    }
                } catch (\Throwable $e) {
                    // ignore and default to 'bahan'
                }
            }

            // For semi outputs there are no product variant ids; normalize to key 0
            $vidKey = $bom->product_variants_id;
            if ($isSemi) $vidKey = 0;

            $existingBoms[$vidKey][] = [
                'stock_id' => $bom->stock_id,
                'semi_finished_product_id' => $bom->semi_finished_product_id,
                'quantity' => (float) $bom->quantity_required,
                'unit_id'  => $bom->unit_id,
                'type'     => $type,
            ];
        }

        // Stock prices info for HPP calculation on frontend
        $stockPrices = [];
        foreach ($stocks as $s) {
            $stockPrices[$s->id] = [
                'price_per_unit' => (float) $s->price_per_unit,
                'unit_id'        => $s->unit_id,
                'name'           => $s->name,
                'unit_type'      => $s->unit?->unit_type ?? '',
            ];
        }

        $conversions = \App\Models\UnitConversion::all()->map(fn($c) => [
            'from' => $c->from_unit_id,
            'to'   => $c->to_unit_id,
            'rate'  => (float) $c->conversion_rate,
        ]);

        return view('handai-manager.inventory.recipes.edit', compact(
            'selected_store', 'product', 'stocks', 'units',
            'variants', 'existingBoms', 'stockPrices', 'conversions',
            'semiFinishedProducts', 'sfpPrices', 'outputId', 'type',
            'isSemi', 'sfpOutput'
        ));
    }

    public function update(Request $request, $outputId)
    {
        // validate inputs conditionally to avoid exists on unused field
        $rules = [
            'output_type' => 'required|in:finished,semi',
        ];
        if ($request->input('output_type') === 'finished') {
            $rules['product_id'] = 'required|exists:product,id';
        } else {
            $rules['semi_finished_output_id'] = 'required|exists:semi_finished_products,id';
        }
        $request->validate($rules);
        // no explicit output_id since route provides it, but we could verify matches

        $storeId = session('selected_store');
        $type = $request->output_type;

        // Debug logging
        try {
            \Log::info('RecipeController::update incoming', ['output_type'=>$type,'output_id'=>$outputId,'user_id' => auth()->id() ?? null, 'payload' => $request->all()]);
        } catch (\Throwable $e) {
            // ignore
        }

        DB::beginTransaction();
        try {
            // Delete old BOMs for this output
            if ($type === 'finished') {
                Bom::where('product_id', $outputId)
                    ->where('store_id', $storeId)
                    ->delete();
            } else {
                Bom::where('output_semi_finished_product_id', $outputId)
                    ->where('store_id', $storeId)
                    ->delete();
            }

            $variants = $request->input('variants', []);

            foreach ($variants as $variantId => $data) {
                $ingredients = $data['ingredients'] ?? [];
                foreach ($ingredients as $ingredient) {
                    $stockId = $ingredient['stock_id'] ?? null;
                    $sfpId = $ingredient['semi_finished_product_id'] ?? null;
                    $qty = $ingredient['quantity'] ?? null;
                    $unitId = $ingredient['unit_id'] ?? null;

                    \Log::debug('RecipeController::update ingredient check', ['variant' => $variantId, 'stock_id' => $stockId, 'sfp_id' => $sfpId, 'quantity' => $qty, 'unit_id' => $unitId]);

                    $qtyValid = is_numeric($qty) && $qty !== '';
                    $hasSource = $stockId || $sfpId;
                    if ($hasSource && $unitId && $qtyValid) {
                        $bomData = [
                            'stock_id'                => $sfpId ? null : $stockId,
                            'semi_finished_product_id'=> $sfpId ?: null,
                            'quantity_required'        => $qty,
                            'unit_id'                 => $unitId,
                            'store_id'                => $storeId,
                        ];
                        if ($type === 'finished') {
                            $bomData['product_id'] = $outputId;
                            // avoid storing 0 as variant id (no variant)
                            if (is_numeric($variantId) && intval($variantId) > 0) {
                                $bomData['product_variants_id'] = $variantId;
                            }
                        } else {
                            $bomData['output_semi_finished_product_id'] = $outputId;
                        }
                        Bom::create($bomData);
                        \Log::debug('RecipeController::update bom created', ['variant' => $variantId, 'stock_id' => $stockId, 'sfp_id' => $sfpId, 'quantity' => $qty, 'unit_id' => $unitId]);
                    } else {
                        \Log::debug('RecipeController::update bom skipped', ['variant' => $variantId, 'stock_id' => $stockId, 'sfp_id' => $sfpId, 'quantity' => $qty, 'unit_id' => $unitId]);
                    }
                }
            }

            // recalc HPP for semi if needed
            if ($type === 'semi') {
                $sfp = \App\Models\SemiFinishedProduct::find($outputId);
                if ($sfp) {
                    // save standardised batch fields from the recipe page
                    $sfp->output_qty = max(0.001, (float) $request->input('semi_output_qty', $sfp->output_qty ?: 1));
                    $sfp->labor_cost = max(0, (float) $request->input('semi_labor_cost', $sfp->labor_cost ?: 0));
                    $sfp->save();
                    $sfp->load('materials.stock');
                    $sfp->recalculateHpp();
                }
            } else {
                // save wage per unit for finished products
                $product = \App\Models\Product::find($outputId);
                if ($product) {
                    $product->wage_per_unit = max(0, (float) $request->input('wage_per_unit', 0));
                    $product->save();
                }
            }

            DB::commit();
            return redirect()->route('manager.inventory.recipes.index')->with('success', 'Resep berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('RecipeController update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui resep: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        // validate depending on output type so that exists rules don't fire for the unused field
        $rules = [
            'output_type' => 'required|in:finished,semi',
        ];
        if ($request->input('output_type') === 'finished') {
            $rules['product_id'] = 'required|exists:product,id';
        } else {
            $rules['semi_finished_output_id'] = 'required|exists:semi_finished_products,id';
        }
        $request->validate($rules);

        $storeId = session('selected_store');
        $type = $request->output_type;
        $outputId = $type === 'finished' ? $request->product_id : $request->semi_finished_output_id;

        DB::beginTransaction();
        try {
            $variants = $request->input('variants', []);

            foreach ($variants as $variantId => $data) {
                $ingredients = $data['ingredients'] ?? [];
                foreach ($ingredients as $ingredient) {
                    // ignore packaging entries – they are shown on the form but not stored in the BOM
                    if (!empty($ingredient['type']) && $ingredient['type'] === 'kemasan') {
                        continue;
                    }
                    $hasSource = !empty($ingredient['stock_id']) || !empty($ingredient['semi_finished_product_id']);
                    if ($hasSource && !empty($ingredient['quantity']) && !empty($ingredient['unit_id'])) {
                        $sfpId = $ingredient['semi_finished_product_id'] ?? null;
                        $bomData = [
                            'stock_id'                => $sfpId ? null : ($ingredient['stock_id'] ?? null),
                            'semi_finished_product_id'=> $sfpId ?: null,
                            'quantity_required'        => $ingredient['quantity'],
                            'unit_id'                 => $ingredient['unit_id'],
                            'store_id'                => $storeId,
                        ];
                        if ($type === 'finished') {
                            $bomData['product_id'] = $outputId;
                            if (is_numeric($variantId) && intval($variantId) > 0) {
                                $bomData['product_variants_id'] = $variantId;
                            }
                        } else {
                            $bomData['output_semi_finished_product_id'] = $outputId;
                        }

                        Bom::create($bomData);
                    }
                }
            }

            // recalc hpp for semi output if needed
            if ($type === 'semi') {
                $sfp = \App\Models\SemiFinishedProduct::find($outputId);
                if ($sfp) {
                    // save standardised batch fields from the recipe page
                    $sfp->output_qty = max(0.001, (float) $request->input('semi_output_qty', $sfp->output_qty ?: 1));
                    $sfp->labor_cost = max(0, (float) $request->input('semi_labor_cost', $sfp->labor_cost ?: 0));
                    $sfp->save();
                    $sfp->load('materials.stock');
                    $sfp->recalculateHpp();
                }
            } else {
                // save wage per unit for finished products
                $product = \App\Models\Product::find($outputId);
                if ($product) {
                    $product->wage_per_unit = max(0, (float) $request->input('wage_per_unit', 0));
                    $product->save();
                }
            }

            DB::commit();
            return redirect()->route('manager.inventory.recipes.index')->with('success', 'Resep berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan resep: ' . $e->getMessage()]);
        }
    }
}
