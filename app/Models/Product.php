<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\ProductVariants;
use App\Models\Traits\ForStoreScope;

class Product extends Model
{
    use ForStoreScope;
    use HasFactory;
    protected $table = 'product';
    protected $fillable = [
        'name',
        'category_id',
        'product_image',
        'is_promo',
        'price_discount',
        'store_id',
        'expired_duration', 
        'image_url',
        'hpp',// ⬅️ tambahkan ini
        'wage_per_unit',
    ];

    protected $casts = [
        'expired_duration' => 'integer',
        'is_promo'         => 'boolean',
        'price_discount'   => 'decimal:0',
        'wage_per_unit'    => 'decimal:2',
    ];
    public function getDefaultExpiredAt(Carbon $productionDate = null)
{
    $productionDate = $productionDate ?? now();
    return $productionDate->copy()->addDays($this->expired_duration ?? 0);
}
public function variants()
{
    return $this->hasMany(ProductVariants::class);
}

public function getExpiredDateAttribute()
{
    $latestProduction = $this->productionHistories->sortByDesc('production_date')->first();

    if (!$latestProduction || !$this->expired_duration) return null;

    return \Carbon\Carbon::parse($latestProduction->production_date)->addDays($this->expired_duration);
}


public function expiredProductions()
{
    return $this->productionHistories()->where('expired_at', '<', now());
}

public function activeProductions()
{
    return $this->productionHistories()->where('expired_at', '>', now());
}

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Alias for variants() — kept for backward compatibility in views/controllers
     * that reference sizePrices. Points to the same relation.
     */
    public function sizePrices()
    {
        return $this->variants();
    }
    public function productionHistories()
    {
        return $this->hasManyThrough(
            ProductionHistory::class,
            ProductVariants::class,
            'product_id',            // FK on product_variants
            'product_variants_id',   // FK on production_history
            'id',                    // local key on products
            'id'                     // local key on product_variants
        );
    }
    public function getTotalQuantityAttribute()
{
    return $this->sizePrices->sum('quantity');
}

    public function getStockStatusAttribute()
{
    $qty = $this->sizePrices->sum('quantity'); // hitung dari relasi yang benar

    if ($qty === 0) {
        return 'Out of Stock';
    } elseif ($qty < 25) {
        return 'Low Stock';
    } else {
        return 'Ready';
    }
}

/**
 * Scope: products that have at least one expired production batch.
 * Uses SQLite-compatible date arithmetic.
 */
public function scopeExpired($query)
{
    return $query->whereHas('productionHistories', function ($q) {
        $q->whereNotNull('expired_at')
          ->where('expired_at', '<', now());
    });
}

/**
 * Alias for variants() — kept for backward compatibility in DashboardPOS.
 */
public function variantsById()
{
    return $this->variants();
}

}
