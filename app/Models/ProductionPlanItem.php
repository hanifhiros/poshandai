<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlanItem extends Model
{
    use \App\Models\Traits\ForStoreScope;

    protected $fillable = [
        'production_plan_id', 'store_id', 'product_variants_id', 'semi_finished_product_id',
        'item_name', 'planned_quantity', 'produced_quantity', 'target_date',
        'status', 'assigned_to', 'notes',
    ];

    protected $casts = [
        'planned_quantity'  => 'decimal:3',
        'produced_quantity' => 'decimal:3',
        'target_date'       => 'date',
    ];

    public function plan()
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variants_id');
    }

    public function semiFinishedProduct()
    {
        return $this->belongsTo(SemiFinishedProduct::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function materialRequirements()
    {
        return $this->hasMany(MaterialRequirement::class);
    }

    public function getCompletionPercentAttribute(): float
    {
        if ($this->planned_quantity == 0) return 0;
        return round(($this->produced_quantity / $this->planned_quantity) * 100, 1);
    }
}
