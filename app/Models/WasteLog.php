<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    protected $table = 'waste_logs';

    protected $fillable = [
        'store_id',
        'waste_date',
        'item_type',        // 'stock' or 'product'
        'stock_id',
        'product_variant_id',
        'item_name',
        'quantity',
        'unit_id',
        'cost_per_unit',
        'total_cost',
        'reason',           // expired, spillage, quality_reject, damaged, other
        'notes',
        'pic_id',           // employee who reported
        'created_by',       // user who logged
    ];

    protected $casts = [
        'waste_date'    => 'date',
        'quantity'      => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'total_cost'    => 'decimal:2',
    ];

    // Reason constants
    const REASON_EXPIRED        = 'expired';
    const REASON_SPILLAGE       = 'spillage';
    const REASON_QUALITY_REJECT = 'quality_reject';
    const REASON_DAMAGED        = 'damaged';
    const REASON_OTHER          = 'other';

    public static function reasons(): array
    {
        return [
            self::REASON_EXPIRED        => 'Kadaluarsa / Basi',
            self::REASON_SPILLAGE       => 'Tumpah / Terbuang',
            self::REASON_QUALITY_REJECT => 'Ditolak (Kualitas)',
            self::REASON_DAMAGED        => 'Rusak',
            self::REASON_OTHER          => 'Lainnya',
        ];
    }

    // ── Relationships ──

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variant_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function pic()
    {
        return $this->belongsTo(Employee::class, 'pic_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockMovement()
    {
        return $this->morphOne(StockMovement::class, 'reference');
    }
}
