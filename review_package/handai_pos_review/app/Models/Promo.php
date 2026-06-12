<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    public const STATUS_ACTIVE = 'Ya';

    protected $table = 'promo';

    protected $casts = [
        'discount_rate'     => 'decimal:2',
        'max_discount_price' => 'decimal:0',
        // NOTE: is_active stores 'Ya'/'Tidak' strings — do NOT cast to boolean
    ];

    protected $fillable = [
        'Promo_Code',
        'discount_rate',
        'max_discount_price',
        'is_active',
        'order_id',
    ];
}
