<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $fillable = [
        'return_id',
        'invoice_id',
        'product_variants_id',
        'product_name',
        'variant_name',
        'quantity_returned',
        'unit_price',
        'refund_amount',
        'condition',
        'restock',
        'notes',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'restock' => 'boolean',
    ];

    public function returnOrder()
    {
        return $this->belongsTo(ReturnOrder::class, 'return_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variants_id');
    }
}
