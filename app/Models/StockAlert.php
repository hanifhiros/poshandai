<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;

class StockAlert extends Model
{
    use ForStoreScope;

    protected $table = 'stock_alerts';

    protected $fillable = [
        'alertable_type',
        'alertable_id',
        'alert_type',
        'current_quantity',
        'threshold_quantity',
        'status',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_at',
        'store_id',
    ];

    protected $casts = [
        'current_quantity'   => 'decimal:3',
        'threshold_quantity' => 'decimal:3',
        'acknowledged_at'    => 'datetime',
        'resolved_at'        => 'datetime',
    ];

    const TYPE_LOW_STOCK     = 'low_stock';
    const TYPE_REORDER_POINT = 'reorder_point';
    const TYPE_OUT_OF_STOCK  = 'out_of_stock';
    const TYPE_EXPIRING_SOON = 'expiring_soon';

    const STATUS_ACTIVE       = 'active';
    const STATUS_ACKNOWLEDGED = 'acknowledged';
    const STATUS_RESOLVED     = 'resolved';

    public static function alertTypes(): array
    {
        return [
            self::TYPE_OUT_OF_STOCK  => 'Stok Habis',
            self::TYPE_LOW_STOCK     => 'Stok Rendah',
            self::TYPE_REORDER_POINT => 'Titik Reorder',
            self::TYPE_EXPIRING_SOON => 'Segera Kadaluarsa',
        ];
    }

    // ── Relationships ──

    public function alertable()
    {
        return $this->morphTo();
    }

    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
