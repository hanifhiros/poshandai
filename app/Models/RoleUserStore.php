<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUserStore extends Pivot
{
    protected $table = 'role_user_store';

    protected $fillable = [
        'user_id',
        'role_id',
        'store_id',
    ];
}
