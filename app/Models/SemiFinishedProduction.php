<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemiFinishedProduction extends Model
{
    protected $table = 'semi_finished_productions';

    protected $fillable = [
        'semi_finished_product_id',
        'store_id',
        'pic_id',
        'quantity_produced',
        'production_date',
        'labor_cost',
        'material_cost',
        'notes',
    ];

    protected $casts = [
        'quantity_produced' => 'decimal:3',
        'production_date'   => 'date',
        'labor_cost'        => 'decimal:2',
        'material_cost'     => 'decimal:2',
    ];

    public function semiFinishedProduct()
    {
        return $this->belongsTo(SemiFinishedProduct::class);
    }

    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    public function pic()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'pic_id');
    }

    /**
     * Actual material usage for this production run.
     */
    public function materialUsages()
    {
        return $this->hasMany(SemiFinishedProductionMaterial::class);
    }

    /**
     * Total cost = material_cost + labor_cost
     */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->material_cost + (float) $this->labor_cost;
    }
}
