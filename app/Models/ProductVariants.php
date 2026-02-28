<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariants extends Model
{
    use HasFactory;
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'size',
        'price',
        'quantity',
        'price_discount',
        'is_promo','store_id', 'hpp',
        'product_name',
        'variant_option_summary',
    ];
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
