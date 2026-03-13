<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemiFinishedMaterial extends Model
{
    protected $table = 'semi_finished_materials';

    protected $fillable = [
        'semi_finished_product_id',
        'stock_id',
        'unit_id',
        'quantity_required',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:3',
    ];

    public function semiFinishedProduct()
    {
        return $this->belongsTo(SemiFinishedProduct::class);
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
