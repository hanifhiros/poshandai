<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    protected $table = "employee";
    protected $fillable = [
        'name',
        'password',
        'email',
        'contact_number',
        'position',
        'salary',
        'store_id',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'salary' => 'decimal:0',
    ];

    // Jika employee digunakan untuk login, aktifkan ini:
    // protected $guard = 'employee';

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function productionHistories()
    {
        return $this->hasMany(ProductionHistory::class, 'pic_id');
    }
}

