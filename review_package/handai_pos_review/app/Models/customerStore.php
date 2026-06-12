<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerStore extends Model
{
    protected $table = 'customer_store';
    protected $fillable = [
        'customer_id',
        'store_id',
        'total_qty',
        'total_order',
        'average_qty',
        'first_ordered_at',
        'last_ordered_at'
    ];

    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

