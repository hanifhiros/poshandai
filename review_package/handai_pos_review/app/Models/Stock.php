<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockCategory;
use App\Helpers\ConversionHelper;

use Carbon\Carbon;

use App\Models\Traits\ForStoreScope;

class Stock extends Model
{
    use ForStoreScope;
    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'price_per_unit',
        'unit_qty',
        'unit_id',
        'min_stock',
        'reorder_point',
        'expired_duration',
        'stock_category_id',
        'store_id',
        'default_supplier_id',
        'is_active',
    ];
    // The attributes that should be cast to native types
    protected $casts = [
        'price_per_unit'   => 'decimal:2',
        'unit_qty'         => 'decimal:3',
        'min_stock'        => 'decimal:3',
        'reorder_point'    => 'decimal:3',
        'expired_duration' => 'integer',
        'is_active'        => 'boolean',
    ];

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

    public function defaultSupplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'default_supplier_id');
    }

    public function movements()
    {
        return $this->hasMany(\App\Models\StockMovement::class, 'stock_id');
    }
    public function getAlmostExpiredAttribute()
    {
        return $this->attributes['almost_expired'] ?? 0;
    }

    public function getPricePerUnitAttribute()
    {
        return $this->attributes['price_per_unit'] ?? 0;
    }
    /**
     * Recalculate stock quantity and price from valid batches.
     * Delegates to the static updateStockValues method.
     */
    public function recalculateStockSummary()
    {
        static::updateStockValues($this->id);
        $this->refresh();
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
    /**
     * @deprecated Use updateStockValues() instead.
     */
    public static function updatePricePerUnit($stockId)
    {
        static::updateStockValues($stockId);
    }
    public function getStatusAttribute()
    {
        $minStock = (float) ($this->min_stock ?? 0);
        if ($this->unit_qty <= 0) {
            return 'Out of Stock';
        } elseif ($minStock > 0 && $this->unit_qty <= $minStock) {
            return 'Low Stock';
        } elseif ($minStock <= 0 && $this->unit_qty < 10) {
            return 'Low Stock';
        } else {
            return 'Ready';
        }
    }

    /**
     * Check if stock needs reorder.
     */
    public function getNeedsReorderAttribute(): bool
    {
        $reorder = (float) ($this->reorder_point ?? 0);
        return $reorder > 0 && $this->unit_qty <= $reorder;
    }

    /**
     * Inventory value = qty × HPP.
     */
    public function getInventoryValueAttribute(): float
    {
        return round((float) $this->unit_qty * (float) $this->price_per_unit, 2);
    }

    /**
     * Scope: only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
