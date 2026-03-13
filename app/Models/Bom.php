<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    protected $table = 'bom';

    protected $fillable = [
        'product_id',
        'product_variants_id',
        'output_semi_finished_product_id',
        'stock_id',
        'semi_finished_product_id',
        'quantity_required','store_id','unit_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * A BOM line can reference a semi-finished product instead of (or in addition to) a raw stock.
     */
    public function semiFinishedProduct()
    {
        return $this->belongsTo(\App\Models\SemiFinishedProduct::class, 'semi_finished_product_id');
    }

    public function ProductVariants()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variants_id');
    }

    /**
     * Check if this BOM line is a semi-finished product ingredient.
     */
    public function isSemiFinished(): bool
    {
        return !is_null($this->semi_finished_product_id);
    }

    /**
     * Get the ingredient name (stock or semi-finished product).
     */
    public function getIngredientNameAttribute(): string
    {
        if ($this->isSemiFinished()) {
            return $this->semiFinishedProduct?->name ?? 'Produk Setengah Jadi';
        }
        return $this->stock?->name ?? 'Bahan Tidak Diketahui';
    }

    
}
