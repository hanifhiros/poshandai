<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reseller extends Model
{
    use HasFactory;

    protected $table = 'resellers';



    public function user()
    {
        return $this->belongsTo(User::class);
    }

   

    protected $fillable = ['user_id', 'name', 'code', 'phone', 'status'];

    

    public function resellerStores()
    {
        return $this->hasMany(ResellerStore::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'reseller_store', 'reseller_id', 'store_id')
                    ->withPivot(['payment_rate', 'qty_sold']);
    }
    public function store()
    {
        return $this->belongsToMany(Store::class, 'reseller_store', 'reseller_id', 'store_id')
                    ->withPivot(['payment_rate', 'qty_sold']);
    }


}
