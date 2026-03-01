<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductVariants extends Model
{
    use HasFactory;
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'size',
        'price',
        'quantity',
        'min_stock',
        'price_discount',
        'is_promo','store_id', 'hpp',
        'product_name',
        'variant_option_summary',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'hpp'            => 'decimal:2',
        'price_discount' => 'decimal:2',
        'quantity'       => 'integer',
        'min_stock'      => 'integer',
    ];

    // ── Accessors ─────────────────────────────────────

    /**
     * Margin percentage:  (price - hpp) / price * 100
     */
    public function getMarginPercentAttribute(): float
    {
        if ((float) $this->price <= 0) return 0;
        return round(((float) $this->price - (float) $this->hpp) / (float) $this->price * 100, 1);
    }

    /**
     * Inventory value = quantity × hpp
     */
    public function getInventoryValueAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->hpp, 2);
    }

    /**
     * Shelf life in days (from parent product).
     * Uses loaded relation to avoid extra query.
     */
    public function getShelfLifeDaysAttribute(): int
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product;
        return (int) ($product->expired_duration ?? 0);
    }

    /**
     * Latest production date for this variant.
     * Uses eager-loaded collection to avoid N+1 queries.
     */
    public function getLastProductionDateAttribute(): ?Carbon
    {
        // Use loaded relation (collection) if available, otherwise fall back to query
        $histories = $this->relationLoaded('productionHistories')
            ? $this->productionHistories
            : $this->productionHistories()->get();

        $latest = $histories->sortByDesc('production_date')->first();
        return $latest ? Carbon::parse($latest->production_date) : null;
    }

    /**
     * Estimated expired date = last_production_date + shelf_life_days
     */
    public function getExpiredEstimateAttribute(): ?Carbon
    {
        $lastProd = $this->last_production_date;
        if (!$lastProd || $this->shelf_life_days <= 0) return null;
        return $lastProd->copy()->addDays($this->shelf_life_days);
    }

    /**
     * Freshness status: Fresh / Hampir Expired / Expired / -
     */
    public function getFreshnessStatusAttribute(): string
    {
        $expiredEstimate = $this->expired_estimate;
        if (!$expiredEstimate) return '-';

        $daysLeft = now()->startOfDay()->diffInDays($expiredEstimate->startOfDay(), false);

        if ($daysLeft < 0) return 'Expired';
        if ($daysLeft <= 3) return 'Hampir Expired';
        return 'Fresh';
    }

    /**
     * Days until expiry (negative = already expired)
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        $expiredEstimate = $this->expired_estimate;
        if (!$expiredEstimate) return null;
        return (int) now()->startOfDay()->diffInDays($expiredEstimate->startOfDay(), false);
    }

    /**
     * Stock status for finished goods
     */
    public function getFgStatusAttribute(): string
    {
        if ($this->quantity <= 0) return 'Habis';
        if ($this->min_stock > 0 && $this->quantity <= $this->min_stock) return 'Low Stock';
        return 'Ready';
    }

    public function variantSummary()
{
    // Use already-loaded relation if available, to avoid N+1 queries
    $options = $this->relationLoaded('options')
        ? $this->options
        : $this->options()->with('attribute')->get();

    $optionNames = $options->map(function ($opt) {
        $attribute = $opt->relationLoaded('attribute') ? $opt->attribute : $opt->attribute;
        return $attribute->name . ': ' . $opt->name;
    });

    return $optionNames->implode(', ') ?: 'Tanpa Varian';
}

    public function variantOptions()
    {
        return $this->belongsToMany(
            \App\Models\VariantOption::class,
            'product_variant_option',           // Nama tabel pivot
            'product_variant_id',               // Foreign key ke model ini (ProductVariant)
            'variant_option_id'                 // Foreign key ke model tujuan (VariantOption)
        );
    }
    
    public function options()
    {
        return $this->belongsToMany(
            VariantOption::class,
            'product_variant_option', // nama tabel pivot
            'product_variant_id',
            'variant_option_id'
        );
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function sku()
{
    return $this->hasOne(Sku::class, 'product_variant_id');
}
public function productionHistories()
{
    return $this->hasMany(\App\Models\ProductionHistory::class, 'product_variants_id');
}



}
