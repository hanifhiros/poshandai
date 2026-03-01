<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'store_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'payment_terms',   // e.g. "NET30", "COD"
        'bank_name',
        'bank_account',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'payment_terms' => 'string',
    ];

    // ── Relationships ──

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    // ── Accessors ──

    /**
     * Total pembelian dari supplier ini.
     */
    public function getTotalPurchasesAttribute()
    {
        return $this->stockBatches()->sum('cost');
    }

    /**
     * Hutang outstanding (purchase via hutang yang belum lunas).
     */
    public function getOutstandingDebtAttribute()
    {
        return $this->stockBatches()
            ->where('payment_method', 'hutang')
            ->where(function ($q) {
                $q->whereNull('paid_at');
            })
            ->sum('cost');
    }
}
