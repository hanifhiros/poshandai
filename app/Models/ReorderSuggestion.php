<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;

class ReorderSuggestion extends Model
{
    use ForStoreScope;

    protected $table = 'reorder_suggestions';

    protected $fillable = [
        'stock_id',
        'suggested_quantity',
        'supplier_id',
        'estimated_cost',
        'status',
        'store_id',
    ];

    protected $casts = [
        'suggested_quantity' => 'decimal:3',
        'estimated_cost'     => 'decimal:2',
    ];

    // ── Relationships ──

    public function stock()
    {
        return $this->belongsTo(Stock::class);
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
