<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';
    protected $fillable = ['symbol', 'name', 'unit_type'];

    public function conversionsFrom()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }
}

