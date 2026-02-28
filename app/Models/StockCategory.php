<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    protected $table = 'stock_category'; // Nama tabel di database
    protected $fillable = ['stock_category_name']; // Kolom yang bisa diisi (fillable)

    public $timestamps = false;

    // Accessor supaya bisa akses stock_category_name lewat 'name'
    public function getNameAttribute()
    {
        return $this->stock_category_name;
    }
    public function batches()
{
    return $this->hasMany(StockBatch::class);
}

}
