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

    // Jika employee digunakan untuk login, aktifkan ini:
    // protected $guard = 'employee';

    public function productionHistories()
    {
        return $this->hasMany(ProductionHistory::class, 'pic_id');
    }
}

