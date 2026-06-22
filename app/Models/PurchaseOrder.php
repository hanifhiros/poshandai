<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'store_id',
        'status',
        'total_amount',
        'notes',
        'created_by'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function generatePoNumber(int $storeId): string
    {
        $prefix = 'PO-' . str_pad($storeId, 3, '0', STR_PAD_LEFT) . '-' . date('Ymd') . '-';
        $latest = self::where('store_id', $storeId)
            ->where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($latest) {
            $seq = intval(substr($latest->po_number, -4)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
