<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    public function resellers()
{
    return $this->belongsToMany(Reseller::class, 'reseller_store');
}

    protected $table = 'store';

    protected $fillable = [
        'store_name',
        'store_address',
        'owner_id',
        'store_id',
        'longitude',
        'latitude'
    ];

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user_store', 'store_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }


    public function nameOnly()
    {
        return $this->store_name;
    }


    public function customerStats()
    {
        return $this->hasMany(CustomerStore::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_store')
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
