<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequirement extends Model
{
    use \App\Models\Traits\ForStoreScope;

    protected $fillable = [
        'production_plan_id', 'production_plan_item_id', 'store_id',
        'stock_id', 'semi_finished_product_id', 'material_name',
        'required_quantity', 'available_quantity', 'shortage_quantity',
        'unit_id', 'status',
    ];

    protected $casts = [
        'required_quantity'  => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'shortage_quantity'  => 'decimal:3',
    ];

    public function plan()
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function planItem()
    {
        return $this->belongsTo(ProductionPlanItem::class, 'production_plan_item_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function semiFinishedProduct()
    {
        return $this->belongsTo(SemiFinishedProduct::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
