<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionWage extends Model
{
    protected $table = 'production_wages';

    protected $fillable = [
        'store_id',
        'production_history_id',
        'employee_id',
        'recipe_product_id',
        'recipe_sfp_id',
        'production_quantity',
        'wage_per_unit',
        'total_wage',
        'production_date',
        'journal_id',
    ];

    protected $casts = [
        'production_quantity' => 'decimal:3',
        'wage_per_unit'       => 'decimal:2',
        'total_wage'          => 'decimal:2',
        'production_date'     => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function productionHistory()
    {
        return $this->belongsTo(ProductionHistory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'recipe_product_id');
    }

    public function semiFinishedProduct()
    {
        return $this->belongsTo(SemiFinishedProduct::class, 'recipe_sfp_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('production_date', [$from, $to]);
    }
}
