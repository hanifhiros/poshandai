<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'store_id',
        'stock_id',
        'product_variant_id',
        'movement_type',
        'quantity',
        'unit_id',
        'cost_per_unit',
        'total_cost',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity'      => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
        'total_cost'    => 'decimal:2',
    ];

    // ── Movement Type Constants ──────────────────────

    const PURCHASE_IN    = 'PURCHASE_IN';
    const PRODUCTION_OUT = 'PRODUCTION_OUT';
    const PRODUCTION_IN  = 'PRODUCTION_IN';
    const SALE_OUT       = 'SALE_OUT';
    const SALE_RETURN    = 'SALE_RETURN';
    const ADJUSTMENT     = 'ADJUSTMENT';
    const EXPIRED_OUT    = 'EXPIRED_OUT';
    const WASTE_OUT      = 'WASTE_OUT';
    const RND_OUT        = 'RND_OUT';

    // ── Relationships ────────────────────────────────

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ───────────────────────────────────────

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeIncoming($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeOutgoing($query)
    {
        return $query->where('quantity', '<', 0);
    }

    public function scopeForStock($query, $stockId)
    {
        return $query->where('stock_id', $stockId);
    }

    public function scopeForVariant($query, $variantId)
    {
        return $query->where('product_variant_id', $variantId);
    }

    // ── Helpers ──────────────────────────────────────

    public function isIncoming(): bool
    {
        return $this->quantity > 0;
    }

    public function isOutgoing(): bool
    {
        return $this->quantity < 0;
    }

    public function getAbsoluteQuantity(): float
    {
        return abs($this->quantity);
    }
}
