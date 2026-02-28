<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoice';
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'quantity_bought',
        'price',
        'total_price',
        'product_name',         // ✅ Tambahan
        'variant_name',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariants::class, 'variant_id');
    }
}
