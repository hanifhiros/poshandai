<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantAttribute extends Model
{
    protected $fillable = ['name', 'code'];

    public function options()
    {
        return $this->hasMany(VariantOption::class, 'attribute_id')->orderBy('sort_order');
    }
}
