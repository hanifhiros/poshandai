<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Unit;
use App\Models\Stock;
class ProductionStockUsage extends Model
{
    protected $table = 'production_stock_usage';

    protected $fillable = [
        'production_history_id',
        'stock_id',
        'unit_id',
        'stock_name', // tambahka
        'quantity',
    ];

    public function productionHistory()
    {
        return $this->belongsTo(ProductionHistory::class);
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
