<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\ProductVariants;
class Product extends Model
{
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

    public function sizePrices()
    {
        return $this->hasMany(ProductVariants::class, 'product_id');
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

public function scopeExpired($query)
{
    return $query->whereHas('product', function ($q) {
        $q->whereNotNull('expired_duration');
    })->whereRaw('DATE_ADD(production_date, INTERVAL expired_duration DAY) < NOW()');
}

public function variantsById()
{
    return $this->hasMany(ProductVariants::class, 'product_id');
}

}
