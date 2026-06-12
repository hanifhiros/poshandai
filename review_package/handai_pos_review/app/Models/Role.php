<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'roles';

    // Kolom yang bisa diisi
    protected $fillable = ['name'];

    // Gunakan timestamps jika tabel pivot menggunakannya
    public $timestamps = true;

    /**
     * Relasi many-to-many ke User lewat tabel pivot role_user_store
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user_store')
            ->withPivot('store_id')
            ->withTimestamps();
    }
   // Role.php
public function children()
{
    return $this->hasMany(Role::class, 'parent_id');
}
public function parent()
{
    return $this->belongsTo(Role::class, 'parent_id');
}


}
