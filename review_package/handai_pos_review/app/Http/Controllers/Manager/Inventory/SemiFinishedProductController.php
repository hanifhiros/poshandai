<?php

namespace App\Http\Controllers\Manager\Inventory;

use App\Http\Controllers\Controller;
use App\Helpers\ConversionHelper;
use App\Models\Employee;
use App\Models\SemiFinishedMaterial;
use App\Models\SemiFinishedProduct;
use App\Models\SemiFinishedProduction;
use App\Models\SemiFinishedProductionMaterial;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Services\InventoryService;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SemiFinishedProductController extends Controller
{
    // ══════════════════════════════════════════════════
    //  INDEX — List all semi-finished products
    // ══════════════════════════════════════════════════

    public function index(Request $request)
    {
        // Redirect to the unified products page with the setengah_jadi tab
        return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi']);
    }

    // ══════════════════════════════════════════════════
    //  CREATE / STORE — Define a new semi-finished product + recipe
    // ══════════════════════════════════════════════════

    public function create()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? \App\Models\Store::find($storeId) : null;

        $stocks = Stock::with('unit')->where('store_id', $storeId)->orderBy('name')->get();
        $units = Unit::all();

        $stockPrices = [];
        foreach ($stocks as $s) {
            $stockPrices[$s->id] = [
                'price_per_unit' => (float) $s->price_per_unit,
                'unit_id'        => $s->unit_id,
                'name'           => $s->name,
                'unit_type'      => $s->unit?->unit_type ?? '',
            ];
        }

        $conversions = UnitConversion::all()->map(fn($c) => [
            'from' => $c->from_unit_id,
            'to'   => $c->to_unit_id,
            'rate'  => (float) $c->conversion_rate,
        ]);

        return view('handai-manager.inventory.semi-finished.create', compact(
            'selected_store', 'stocks', 'units', 'stockPrices', 'conversions'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'unit_id'    => 'required|exists:units,id',
        ]);

        $storeId = session('selected_store');

        DB::beginTransaction();
        try {
            $sfp = SemiFinishedProduct::create([
                'name'             => $request->name,
                'description'      => $request->description,
                'store_id'         => $storeId,
                'unit_id'          => $request->unit_id,
                'output_qty'       => 1,   // will be set when a recipe is defined
                'labor_cost'       => 0,   // will be set when a recipe is defined
                'expired_duration' => $request->expired_duration,
                'min_stock'        => $request->min_stock ?? 0,
            ]);

            // NOTE: recipe and HPP are now managed via the Recipes page
            // old material records are left untouched for historical data

            // optionally recalc using existing method if any materials exist
            if ($sfp->materials()->exists()) {
                $sfp->load('materials.stock');
                $sfp->recalculateHpp();
            }

            DB::commit();
            return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi'])
                ->with('success', 'Produk setengah jadi berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SemiFinished store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()])->withInput();
        }
    }

    // ══════════════════════════════════════════════════
    //  EDIT / UPDATE — Modify a semi-finished product + recipe
    // ══════════════════════════════════════════════════

    public function edit($id)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? \App\Models\Store::find($storeId) : null;

        $sfp = SemiFinishedProduct::with(['materials.stock.unit', 'materials.unit', 'unit'])
            ->where('store_id', $storeId)
            ->findOrFail($id);

        $stocks = Stock::with('unit')->where('store_id', $storeId)->orderBy('name')->get();
        $units = Unit::all();

        $stockPrices = [];
        foreach ($stocks as $s) {
            $stockPrices[$s->id] = [
                'price_per_unit' => (float) $s->price_per_unit,
                'unit_id'        => $s->unit_id,
                'name'           => $s->name,
                'unit_type'      => $s->unit?->unit_type ?? '',
            ];
        }

        $conversions = UnitConversion::all()->map(fn($c) => [
            'from' => $c->from_unit_id,
            'to'   => $c->to_unit_id,
            'rate'  => (float) $c->conversion_rate,
        ]);

        $existingMaterials = $sfp->materials->map(fn($m) => [
            'stock_id' => $m->stock_id,
            'quantity' => (float) $m->quantity_required,
            'unit_id'  => $m->unit_id,
        ])->toArray();

        return view('handai-manager.inventory.semi-finished.edit', compact(
            'selected_store', 'sfp', 'stocks', 'units',
            'stockPrices', 'conversions', 'existingMaterials'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'unit_id'    => 'required|exists:units,id',
        ]);

        $storeId = session('selected_store');

        DB::beginTransaction();
        try {
            $sfp = SemiFinishedProduct::where('store_id', $storeId)->findOrFail($id);

            $sfp->update([
                'name'             => $request->name,
                'description'      => $request->description,
                'unit_id'          => $request->unit_id,
                'expired_duration' => $request->expired_duration,
                'min_stock'        => $request->min_stock ?? 0,
            ]);

            // recipe and HPP will be handled on the Recipes page; do not touch materials here
            // keep existing material records intact in case used elsewhere
            
            // recalc if there are materials (legacy)
            if ($sfp->materials()->exists()) {
                $sfp->load('materials.stock');
                $sfp->recalculateHpp();
            }

            DB::commit();
            return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi'])
                ->with('success', 'Produk setengah jadi berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SemiFinished update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memperbarui: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $storeId = session('selected_store');
        $sfp = SemiFinishedProduct::where('store_id', $storeId)->findOrFail($id);

        // Check if used in any BOM
        $bomUsageCount = \App\Models\Bom::where('semi_finished_product_id', $sfp->id)->count();
        if ($bomUsageCount > 0) {
            return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi'])
                ->withErrors(['error' => 'Tidak bisa dihapus. Produk setengah jadi ini digunakan di ' . $bomUsageCount . ' resep produk jadi.']);
        }

        $sfp->delete();

        return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi'])
            ->with('success', 'Produk setengah jadi berhasil dihapus.');
    }

    // ══════════════════════════════════════════════════
    //  PRODUCTION — Produce semi-finished product (consume raw materials)
    // ══════════════════════════════════════════════════

    public function createProduction($id)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? \App\Models\Store::find($storeId) : null;

        $sfp = SemiFinishedProduct::with(['materials.stock.unit', 'materials.unit', 'unit'])
            ->where('store_id', $storeId)
            ->findOrFail($id);

        $employees = Employee::where('store_id', $storeId)->orderBy('name')->get();
        $units = Unit::all();

        return view('handai-manager.inventory.semi-finished.produce', compact(
            'selected_store', 'sfp', 'employees', 'units'
        ));
    }

    public function storeProduction(Request $request, $id)
    {
        $request->validate([
            'production_date'   => 'required|date',
            'pic_id'            => 'required|exists:employee,id',
            'batch_multiplier'  => 'required|numeric|min:0.001',
            'labor_cost'        => 'required|numeric|min:0',
        ]);

        $storeId = session('selected_store');

        DB::beginTransaction();
        try {
            ConversionHelper::preloadAll();

            $sfp = SemiFinishedProduct::with(['materials.stock'])
                ->where('store_id', $storeId)
                ->findOrFail($id);

            $multiplier = (float) $request->batch_multiplier;
            $quantityProduced = (float) $sfp->output_qty * $multiplier;

            // Phase 1: Validate all stock availability
            foreach ($sfp->materials as $mat) {
                $stock = $mat->stock;
                if (!$stock) continue;

                $rate = ConversionHelper::getConversionRate($mat->unit_id, $stock->unit_id);
                if ($rate === null) {
                    throw new \Exception("Konversi satuan dari unit #{$mat->unit_id} ke #{$stock->unit_id} tidak ditemukan untuk '{$stock->name}'.");
                }

                $requiredQty = (float) $mat->quantity_required * $multiplier * $rate;
                if ($stock->unit_qty < $requiredQty) {
                    throw new \Exception("Stok '{$stock->name}' tidak mencukupi. Dibutuhkan: " . round($requiredQty, 3) . ", tersedia: {$stock->unit_qty}");
                }
            }

            // Phase 2: Create production record
            $totalMaterialCost = 0;
            $production = SemiFinishedProduction::create([
                'semi_finished_product_id' => $sfp->id,
                'store_id'                 => $storeId,
                'pic_id'                   => $request->pic_id,
                'quantity_produced'         => $quantityProduced,
                'production_date'          => $request->production_date,
                'labor_cost'               => $request->labor_cost,
                'material_cost'            => 0, // will be updated
                'notes'                    => $request->notes,
            ]);

            // Phase 3: Deduct stocks
            foreach ($sfp->materials as $mat) {
                $stock = $mat->stock;
                if (!$stock) continue;

                $rate = ConversionHelper::getConversionRate($mat->unit_id, $stock->unit_id);
                $rate = $rate ?: 1;

                $usedQty = (float) $mat->quantity_required * $multiplier * $rate;
                $usedQtyOriginal = (float) $mat->quantity_required * $multiplier;

                // Deduct from stock
                $stock->unit_qty -= $usedQty;
                $stock->save();

                // Record material usage
                SemiFinishedProductionMaterial::create([
                    'semi_finished_production_id' => $production->id,
                    'stock_id'                     => $stock->id,
                    'unit_id'                      => $mat->unit_id,
                    'stock_name'                   => $stock->name,
                    'quantity_used'                => $usedQtyOriginal,
                ]);

                // Record stock movement
                InventoryService::recordSemiFinishedConsumption(
                    $storeId, $stock, $usedQty, $production->id
                );

                $totalMaterialCost += $usedQty * (float) $stock->price_per_unit;
            }

            // Update material cost
            $production->material_cost = $totalMaterialCost;
            $production->save();

            // Phase 4: Increase semi-finished product stock
            $sfp->current_qty += $quantityProduced;
            $sfp->save();

            // Recalculate HPP based on weighted average
            $sfp->recalculateHpp();

            DB::commit();

            return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi'])
                ->with('success', "Produksi \"{$sfp->name}\" berhasil! +{$quantityProduced} {$sfp->unit?->symbol}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SemiFinished production error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // ══════════════════════════════════════════════════
    //  PRODUCTION HISTORY
    // ══════════════════════════════════════════════════

    public function productionHistory(Request $request)
    {
        // Redirect to the unified products page
        return redirect()->route('manager.inventory.products', ['tab' => 'setengah_jadi']);
    }
}
