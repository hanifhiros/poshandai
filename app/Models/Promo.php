<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promo';

    protected $fillable = [
        'Promo_Code',
        'discount_rate',
        'max_discount_price',
        'is_active',
        'order_id',
    ];
}
