<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockCategory;
use App\Helpers\ConversionHelper;

use Carbon\Carbon;

class Stock extends Model
{
    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'price_per_unit',
        'unit_qty',
        'unit_id',
        
        'expired_duration',
        'stock_category_id',
        'store_id',
    ];
    // The attributes that should be cast to native types
        // The table associated with the model
        protected $table = 'stock';

        // The primary key associated with the table (if different from "id")
        protected $primaryKey = 'id';
    
        // Indicates if the IDs are auto-incrementing
        public $incrementing = true;
    
        // The data type of the primary key
        protected $keyType = 'int';
    
    public function batches()
    {
        return $this->hasMany(StockBatch::class, 'stock_id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function stockCategory()
{
    return $this->belongsTo(\App\Models\StockCategory::class, 'stock_category_id');
}

    /**
     * Alias for stockCategory() - kept for backward compatibility.
     */
     public function category()
    {
        return $this->stockCategory();
    }
    public function getAlmostExpiredAttribute()
{

    return $this->attributes['almost_expired'];
}

    public function getPricePerUnitAttribute()
    {
        $today = Carbon::today();
        $startDate = $today->copy()->subDays($this->expired_duration ?? 0);
        return $this->attributes['price_per_unit'];
        }
    public function recalculateStockSummary()
    {
        $today = now();
        $startDate = $today->copy()->subDays($this->expired_duration ?? 0);

        $validBatches = $this->batches()
            ->whereDate('buy_date', '>=', $startDate)
            ->whereDate('buy_date', '<=', $today)
            ->get();

        $totalCost = 0;
        $totalQty = 0;

        foreach ($validBatches as $batch) {
            $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $this->unit_id);
            if ($conversionRate === null) continue;

            $qtyInStockUnit = $batch->unit_qty * $conversionRate;
            $totalQty += $qtyInStockUnit;
            $totalCost += $batch->cost;
        }

        $this->unit_qty = $totalQty;
        $this->price_per_unit = $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0;
        $this->save();
    }
   // app/Models/Stock.php

public function availableUnits()
{
    return $this->belongsToMany(
        \App\Models\Unit::class,     // Relasi ke model Unit
        'stock_unit',                 // Nama tabel pivot
        'stock_id',                   // Foreign key di tabel pivot
        'unit_id'                     // Related key di tabel pivot
    );
}


    public static function updateStockValues($stockId)
    {
        $stock = self::with('batches')->find($stockId);
        if (!$stock) return;

        $duration = $stock->expired_duration ?? 30;
        $today = now();
        $startDate = $today->copy()->subDays($duration);

        $validBatches = $stock->batches()
            ->whereDate('buy_date', '>=', $startDate)
            ->get();

        $totalCost = 0;
        $totalQtyInStockUnit = 0;

        foreach ($validBatches as $batch) {
            $conversionRate = \App\Helpers\ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
            if ($conversionRate === null) continue;

            $qtyInStockUnit = $batch->unit_qty * $conversionRate;
            $totalQtyInStockUnit += $qtyInStockUnit;
            $totalCost += $batch->cost;
        }

        $stock->unit_qty = $totalQtyInStockUnit;
        $stock->price_per_unit = $totalQtyInStockUnit > 0
            ? round($totalCost / $totalQtyInStockUnit, 2)
            : 0;

        $stock->save();
    }
    public static function updatePricePerUnit($stockId)
    {
            $stock = Stock::find($stockId);
            if (!$stock) return;
        
            $today = now();
            $startDate = $today->copy()->subDays($stock->expired_duration ?? 0);
        
            $validBatches = $stock->batches()
                ->whereDate('buy_date', '>=', $startDate)
                ->whereDate('buy_date', '<=', $today)
                ->get();
        
            $totalCost = 0;
            $totalQty = 0;
        
            foreach ($validBatches as $batch) {
                $conversionRate = ConversionHelper::getConversionRate($batch->unit_id, $stock->unit_id);
                if ($conversionRate === null) continue;
        
                $qtyInStockUnit = $batch->unit_qty * $conversionRate;
                $totalQty += $qtyInStockUnit;
                $totalCost += $batch->cost;
            }
        
            $stock->price_per_unit = $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0;
            $stock->save();
        }
    public function getStatusAttribute()
        {
            if ($this->unit_qty === 0) {
                return 'Out of Stock';
            } elseif ($this->unit_qty < 10) {
                return 'Low Stock';
            } else {
                return 'Ready';
            }
        }
}
