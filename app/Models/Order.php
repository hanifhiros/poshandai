<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $casts = [
        'gross_amount'    => 'decimal:0',
        'total_item_price' => 'decimal:0',
        'delivery_fee'    => 'decimal:0',
        'pajak'           => 'decimal:0',
        'ongkos_kirim'    => 'decimal:0',
        'kemasan'         => 'decimal:0',
        'delivery_date'   => 'date',
    ];

    protected $fillable = [
        'order_id',
        'snap_token',
        'gross_amount',
        'total_item_price',
        'order_origin',
        'delivery_fee',
        'PROMO_ID',
        'note',
        'order_status',
        'description',
        'customer_id',
        'payment_id',
        'seller_id',
        'store_id',
        'midtrans_status',
        'payment_type',
        'va_number',
        'pdf_url',
        'midtrans_response',
        'delivery_date',
        'delivery_time',
        'total_hpp_orders',
        'delivery_address',
        'reseller_id',
        'pajak',
        'ongkos_kirim',
        'kemasan'
    ];

    // === RELASI ===

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    

    public function promo()
    {
        return $this->belongsTo(Promo::class, 'PROMO_ID');
    }

    // App\Models\Order.php

public function invoices() {
    return $this->hasMany(Invoice::class);
}

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }
}
