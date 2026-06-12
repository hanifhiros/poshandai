<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemiFinishedProductionMaterial extends Model
{
    protected $table = 'semi_finished_production_materials';

    protected $fillable = [
        'semi_finished_production_id',
        'stock_id',
        'unit_id',
        'stock_name',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:3',
    ];

    public function production()
    {
        return $this->belongsTo(SemiFinishedProduction::class, 'semi_finished_production_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
