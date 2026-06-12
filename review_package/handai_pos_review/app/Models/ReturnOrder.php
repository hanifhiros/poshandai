<?php

namespace App\Models;

use App\Models\Traits\ForStoreScope;
use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
    use ForStoreScope;

    protected $table = 'returns';

    protected $fillable = [
        'store_id',
        'return_number',
        'order_id',
        'customer_id',
        'return_type',
        'status',
        'reason',
        'notes',
        'total_refund_amount',
        'processed_by',
        'return_date',
    ];

    protected $casts = [
        'return_date' => 'date',
        'total_refund_amount' => 'decimal:2',
    ];

    const STATUSES = ['pending', 'approved', 'processed', 'rejected', 'completed'];
    const TYPES = ['refund', 'exchange', 'store_credit'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public static function generateNumber($storeId): string
    {
        $today = now()->format('Ymd');
        $prefix = "RET-{$today}-";
        $last = static::where('store_id', $storeId)
            ->where('return_number', 'like', $prefix . '%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $seq = $last ? (int) substr($last, -3) + 1 : 1;
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
