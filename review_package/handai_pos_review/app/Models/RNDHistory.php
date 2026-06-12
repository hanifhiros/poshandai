<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RNDHistory extends Model
{
    protected $fillable = ['store_id','rnd_name','pic_id','rnd_date','deskripsi'];
    protected $table = "rnd_history";

    protected $casts = [
        'rnd_date' => 'date',
    ];
    public function pic()
{
    return $this->belongsTo(\App\Models\Employee::class, 'pic_id');
}

public function stockUsages()
{
    return $this->hasMany(\App\Models\RNDStockUsage::class, 'rnd_id');
}

}
