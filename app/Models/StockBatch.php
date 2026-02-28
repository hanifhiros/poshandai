<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $table = 'stock_batches';

    protected $fillable = [
        'stock_name', 'stock_id', 'unit_qty', 'unit_id', 'cost', 'buy_date', 'store_id', 'nota_url',
        'purchase_group', 'supplier_name', 'invoice_ref', 'payment_method', 'due_date',
        'discount', 'tax', 'purchase_notes', 'expired_duration', 'isStored',
    ];

    protected $casts = [
        'buy_date'  => 'date',
        'due_date'  => 'date',
        'cost'      => 'decimal:2',
        'discount'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'unit_qty'  => 'decimal:3',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    
}
