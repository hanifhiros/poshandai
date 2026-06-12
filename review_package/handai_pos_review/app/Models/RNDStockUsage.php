<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RNDStockUsage extends Model
{
    protected $table = "rnd_stock_usage";
    protected $fillable = ['rnd_id','stock_name','stock_id','unit_id','quantity_used','status','manual_name','cost'];
    public function stock()
{
    return $this->belongsTo(\App\Models\Stock::class, 'stock_id');
}

public function unit()
{
    return $this->belongsTo(\App\Models\Unit::class, 'unit_id');
}
public function rndHistory(){
    return $this->belongsTo(\App\Models\RNDHistory::class, 'rnd_id');
}
}
