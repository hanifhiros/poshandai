<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\ForStoreScope;

class StockBatch extends Model
{
    use ForStoreScope;
    protected $table = 'stock_batches';

    protected $fillable = [
        'stock_name', 'stock_id', 'unit_qty', 'unit_id', 'cost', 'buy_date', 'store_id', 'nota_url',
        'purchase_group', 'supplier_name', 'supplier_id', 'invoice_ref', 'payment_method', 'due_date',
        'discount', 'tax', 'purchase_notes', 'expired_duration', 'isStored', 'paid_at',
    ];

    protected $casts = [
        'buy_date'  => 'date',
        'due_date'  => 'date',
        'paid_at'   => 'datetime',
        'cost'      => 'decimal:2',
        'discount'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'unit_qty'  => 'decimal:3',
        'expired_duration' => 'integer',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
