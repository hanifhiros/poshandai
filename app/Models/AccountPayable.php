<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    protected $table = 'accounts_payable';

    protected $fillable = [
        'store_id', 'supplier_id', 'description', 'total_amount',
        'paid_amount', 'due_date', 'status', 'source_type',
        'source_id', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    const STATUS_UNPAID = 'unpaid';
    const STATUS_PARTIAL = 'partially_paid';
    const STATUS_PAID = 'paid';

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments()
    {
        return $this->hasMany(ApPayment::class, 'accounts_payable_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOutstandingAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->paid_amount;
    }

    public function recalculateStatus(): void
    {
        $this->paid_amount = $this->payments()->sum('amount');

        if ($this->paid_amount >= $this->total_amount) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
        } else {
            $this->status = self::STATUS_UNPAID;
        }

        $this->save();
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', [self::STATUS_UNPAID, self::STATUS_PARTIAL]);
    }
}
