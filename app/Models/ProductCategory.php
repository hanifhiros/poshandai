<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_category';

    protected $fillable = [
        'category_name',
        'category_icon',
    ];

    public $timestamps = false;
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

}
