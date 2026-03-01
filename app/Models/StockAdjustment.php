<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustments';

    protected $fillable = [
        'store_id',
        'adjustment_date',
        'adjustment_number',
        'stock_id',
        'product_variant_id',
        'item_type',          // 'stock' or 'product'
        'item_name',
        'system_qty',
        'actual_qty',
        'difference',
        'unit_id',
        'cost_per_unit',
        'total_cost_impact',
        'reason',
        'notes',
        'pic_id',
        'created_by',
        'status',             // draft, approved, completed
    ];

    protected $casts = [
        'adjustment_date'  => 'date',
        'system_qty'       => 'decimal:3',
        'actual_qty'       => 'decimal:3',
        'difference'       => 'decimal:3',
        'cost_per_unit'    => 'decimal:2',
        'total_cost_impact' => 'decimal:2',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_APPROVED  = 'approved';
    const STATUS_COMPLETED = 'completed';

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
}
