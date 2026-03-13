<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionHistory extends Model
{

    use HasFactory;
    protected $table = 'production_history';
    protected $casts = [
        'quantity_produced' => 'integer',
        'production_date'   => 'date',
    ];

    protected $fillable = [
        'quantity_produced',
        'production_date',
        'pic_id',
        'product_variants_id',
        'semi_finished_product_id',
        'store_id',
        'product_name',
        'variant_option_summary',
    ];

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            ProductVariants::class,
            'id',                    // FK on product_variants
            'id',                    // FK on products
            'product_variants_id',   // local key on production_history
            'product_id'             // local key on product_variants
        );
    }
    public function usages()
    {
        return $this->hasMany(\App\Models\ProductionStockUsage::class, 'production_history_id');
    }

    public function isExpired()
    {
        $expiredDuration = $this->productVariants?->product?->expired_duration ?? 0;
        $expiredDate = Carbon::parse($this->production_date)
            ->addDays($expiredDuration);

        return now()->greaterThan($expiredDate);
    }
    public function productVariants()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variants_id');
    }
    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variants_id');
    }

    public function variant()
    {
        return $this->belongsTo(\App\Models\ProductVariants::class, 'product_variants_id');
    }

    public function productSizePrice()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variants_id');
    }

    public function pic()
    {
        return $this->belongsTo(Employee::class, 'pic_id');
    }

    public function semiFinishedProduct()
    {
        return $this->belongsTo(\App\Models\SemiFinishedProduct::class, 'semi_finished_product_id');
    }

}
