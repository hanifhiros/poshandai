<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    protected $table = 'stock_category';

    protected $fillable = ['stock_category_name'];

    public $timestamps = false;

    // Named constants for category IDs used across the app
    public const RAW_MATERIAL = 1;
    public const WIP = 3;
    public const FINISHED_GOODS = 4;

    public function getNameAttribute()
    {
        return $this->stock_category_name;
    }

    public function batches()
    {
        return $this->hasMany(StockBatch::class);
    }
}
