<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ResellerStore extends Pivot
{
    protected $table = 'reseller_store';

    protected $fillable = [
        'reseller_id',
        'store_id',
        'payment_rate',
        'qty_sold',
    ];



    
}
