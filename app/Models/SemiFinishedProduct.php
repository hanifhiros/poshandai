<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;

class SemiFinishedProduct extends Model
{
    use ForStoreScope;

    protected $table = 'semi_finished_products';

    protected $fillable = [
        'name',
        'description',
        'store_id',
        'unit_id',
        'output_qty',
        'labor_cost',
        'current_qty',
        'price_per_unit',
        'expired_duration',
        'min_stock',
    ];

    protected $casts = [
        'output_qty'      => 'decimal:3',
        'labor_cost'      => 'decimal:2',
        'current_qty'     => 'decimal:3',
        'price_per_unit'  => 'decimal:2',
        'expired_duration' => 'integer',
        'min_stock'       => 'decimal:3',
    ];

    // ── Relationships ─────────────────────────────────

    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class);
    }

    /**
     * Recipe: raw materials required to produce one batch.
     */
    public function materials()
    {
        return $this->hasMany(SemiFinishedMaterial::class);
    }

    /**
     * Production history for this semi-finished product.
     */
    public function productions()
    {
        return $this->hasMany(SemiFinishedProduction::class);
    }

    /**
     * BOM lines in final products that use this semi-finished product.
     */
    public function bomUsages()
    {
        return $this->hasMany(Bom::class, 'semi_finished_product_id');
    }

    // ── Computed ───────────────────────────────────────

    /**
     * Recalculate price_per_unit (HPP) based on material costs + labor cost.
     */
    public function recalculateHpp(): void
    {
        \App\Helpers\ConversionHelper::preloadAll();

        $materialCost = 0;

        // if there are recipe BOMs defined for this semi product, use them instead of the old materials table
        $boms = Bom::where('output_semi_finished_product_id', $this->id)->get();
        if ($boms->isNotEmpty()) {
            foreach ($boms as $bom) {
                if ($bom->semi_finished_product_id) {
                    $sfp = $bom->semiFinishedProduct;
                    if (!$sfp) continue;
                    $rate = \App\Helpers\ConversionHelper::getConversionRate($bom->unit_id, $sfp->unit_id) ?: 1;
                    $materialCost += (float) $bom->quantity_required * $rate * (float) $sfp->price_per_unit;
                } else {
                    $stock = $bom->stock;
                    if (!$stock) continue;
                    $rate = \App\Helpers\ConversionHelper::getConversionRate($bom->unit_id, $stock->unit_id) ?: 1;
                    $materialCost += (float) $bom->quantity_required * $rate * (float) $stock->price_per_unit;
                }
            }
        } else {
            // legacy behaviour using materials table
            foreach ($this->materials as $mat) {
                $stock = $mat->stock;
                if (!$stock) continue;

                $rate = \App\Helpers\ConversionHelper::getConversionRate($mat->unit_id, $stock->unit_id);
                $rate = $rate ?: 1;
                $materialCost += (float) $mat->quantity_required * $rate * (float) $stock->price_per_unit;
            }
        }

        $totalCostPerBatch = $materialCost + (float) $this->labor_cost;
        $outputQty = (float) $this->output_qty ?: 1;

        $this->price_per_unit = round($totalCostPerBatch / $outputQty, 2);
        $this->save();
    }

    /**
     * Stock status label.
     */
    public function getStockStatusAttribute(): string
    {
        $qty = (float) $this->current_qty;
        $min = (float) $this->min_stock;

        if ($qty <= 0) return 'Habis';
        if ($min > 0 && $qty <= $min) return 'Hampir Habis';
        return 'Tersedia';
    }
}
