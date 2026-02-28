<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    protected $table = 'bom';

    protected $fillable = [
        'product_id',
        'product_variants_id',
        'stock_id',
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

    public function sizePrice()
    {
        return $this->belongsTo(ProductVariants::class, 'size_price_id');
    }
    // App\Models\Bom.php

public function ProductVariants()
{
    return $this->belongsTo(ProductVariants::class, 'product_variants_id');
}

    
}
