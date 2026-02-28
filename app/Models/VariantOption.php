<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantOption extends Model
{
    protected $fillable = ['attribute_id', 'name', 'code','store_id'];

    public function attribute()
    {
        return $this->belongsTo(VariantAttribute::class, 'attribute_id');
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariants::class, 'product_variant_option');
    }
}
