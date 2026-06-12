<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\MaterialRequirement;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Models\SemiFinishedMaterial;
use App\Models\SemiFinishedProduct;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class ProductionPlanningService
{
    /**
     * Calculate Material Requirements Planning for a production plan.
     * Explodes BOM for each plan item and checks stock availability.
     */
    public static function calculateMRP(ProductionPlan $plan): void
    {
        $storeId = $plan->store_id;

        // Clear previous MRP results
        MaterialRequirement::where('production_plan_id', $plan->id)->delete();

        $items = $plan->items()->get();

        foreach ($items as $item) {
            $bomLines = collect();

            if ($item->product_variants_id) {
                // Finished goods → BOM from bom table
                $bomLines = Bom::where('store_id', $storeId)
                    ->where('product_variants_id', $item->product_variants_id)
                    ->get();
            } elseif ($item->semi_finished_product_id) {
                // Semi-finished → BOM from semi_finished_materials
                $materials = SemiFinishedMaterial::where('semi_finished_product_id', $item->semi_finished_product_id)->get();
                foreach ($materials as $mat) {
                    $bomLines->push((object)[
                        'stock_id'                => $mat->stock_id,
                        'semi_finished_product_id' => null,
                        'quantity_required'        => $mat->quantity_required,
                        'unit_id'                  => $mat->unit_id,
                    ]);
                }
            }

            foreach ($bomLines as $bom) {
                $requiredQty = $bom->quantity_required * $item->planned_quantity;

                $availableQty = 0;
                $materialName = 'Unknown';

                if (!empty($bom->semi_finished_product_id)) {
                    $sfp = SemiFinishedProduct::find($bom->semi_finished_product_id);
                    $availableQty = $sfp ? (float) $sfp->current_stock : 0;
                    $materialName = $sfp?->name ?? 'Produk Setengah Jadi';
                } elseif (!empty($bom->stock_id)) {
                    $stock = Stock::find($bom->stock_id);
                    $availableQty = $stock ? (float) $stock->quantity : 0;
                    $materialName = $stock?->name ?? 'Bahan';
                }

                $shortage = max(0, $requiredQty - $availableQty);

                MaterialRequirement::create([
                    'production_plan_id'       => $plan->id,
                    'production_plan_item_id'  => $item->id,
                    'store_id'                 => $storeId,
                    'stock_id'                 => $bom->stock_id ?? null,
                    'semi_finished_product_id' => $bom->semi_finished_product_id ?? null,
                    'material_name'            => $materialName,
                    'required_quantity'         => $requiredQty,
                    'available_quantity'        => $availableQty,
                    'shortage_quantity'         => $shortage,
                    'unit_id'                  => $bom->unit_id ?? null,
                    'status'                   => $shortage > 0 ? 'short' : 'sufficient',
                ]);
            }
        }
    }

    /**
     * Get a summary of material shortages for a plan.
     */
    public static function getShortages(ProductionPlan $plan)
    {
        return MaterialRequirement::where('production_plan_id', $plan->id)
            ->where('status', 'short')
            ->with(['stock', 'unit', 'planItem'])
            ->get();
    }

    /**
     * Aggregated material requirements grouped by stock_id.
     */
    public static function getAggregatedRequirements(ProductionPlan $plan)
    {
        return MaterialRequirement::where('production_plan_id', $plan->id)
            ->select(
                'stock_id',
                'semi_finished_product_id',
                'material_name',
                'unit_id',
                DB::raw('SUM(required_quantity) as total_required'),
                DB::raw('MIN(available_quantity) as available'),
                DB::raw('SUM(shortage_quantity) as total_shortage')
            )
            ->groupBy('stock_id', 'semi_finished_product_id', 'material_name', 'unit_id')
            ->with(['stock', 'unit'])
            ->get();
    }
}
