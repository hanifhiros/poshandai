<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = "customer";

    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'address',
        'gender',
        'qty_ordered',
        'qty_ordered_avg',
        'has_ordered',
        'created_by',
        'store_id',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function storeStats()
    {
        return $this->hasMany(CustomerStore::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'customer_store')
            ->withPivot([
                'total_ordered_qty',
                'average_ordered_qty',
                'total_orders',
                'first_ordered_at',
                'last_ordered_at'
            ])
            ->withTimestamps();
    }

}

