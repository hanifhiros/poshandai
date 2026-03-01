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
            })->orWhereHas('pic', function ($eq) use ($search) {
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

    return view('handai-manager.operational.create-produksi', compact(
        'productVariants',
        'employees',
        'stocks',
        'selected_store',
        'units'
    ));
}

public function produksiStore(Request $request)
{
    $request->validate([
        'production_date' => 'required|date',
        'pic_id' => 'required|exists:employee,id',
        'product_variants_id' => 'required|exists:product_variants,id',
        'quantity_produced' => 'required|integer|min:1',
        'use_bom' => 'required|in:yes,no',
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
            'store_id'=>session('selected_store'),
        ]);

        $totalCost = 0;

        if ($request->use_bom === 'yes') {
            $boms = Bom::where('product_variants_id', $productVariantId)->get();

            if ($boms->isEmpty()) {
                throw new \Exception("Produk ini belum memiliki resep (BOM). Silakan input manual atau buat resep terlebih dahulu.");
            }

            foreach ($boms as $bom) {
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

            foreach ($boms as $bom) {
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
                    'quantity' => $bom->quantity_required * $qtyProduced, // dalam satuan BOM (user)
                ]);

                // Record PRODUCTION_OUT movement
                InventoryService::recordProductionConsumption(
                    session('selected_store'), $stock, $usedQty, $bom->unit_id, $production->id
                );

                $totalCost += $bom->quantity_required * $conversionRate * $qtyProduced * $stock->price_per_unit;
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

        // 💰 Hitung HPP baru
        $oldQty = $productVariant->quantity;
        $oldHpp = $productVariant->hpp;
        $newQty = $oldQty + $qtyProduced;
        $newHpp = $newQty > 0 ? round((($oldHpp * $oldQty) + ($totalCost)) / $newQty, 2) : $oldHpp;

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

        DB::commit();
        return redirect()->route('manager.operational.produksi')->with('success', 'Produksi berhasil disimpan!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()]);
    }
}



}

