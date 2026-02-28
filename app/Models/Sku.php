<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    protected $table = 'sku';
    protected $fillable = ['product_variant_id', 'sku_code', 'barcode'];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class);
    }
    
}
