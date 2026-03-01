<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoice';
    protected $casts = [
        'price'           => 'decimal:0',
        'total_price'     => 'decimal:0',
        'quantity_bought'  => 'integer',
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'quantity_bought',
        'price',
        'total_price',
        'product_name',
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
