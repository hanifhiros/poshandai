<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'stock_id',
        'unit_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
