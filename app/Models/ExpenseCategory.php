<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'store_id', 'name', 'slug', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Seed default categories for a store if none exist.
     */
    public static function ensureDefaults(int $storeId): void
    {
        if (self::where('store_id', $storeId)->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'Pembelian Bahan', 'slug' => 'inventory_purchase'],
            ['name' => 'Biaya Operasional', 'slug' => 'operational'],
            ['name' => 'Biaya Marketing', 'slug' => 'marketing'],
            ['name' => 'Gaji & Upah', 'slug' => 'salary'],
            ['name' => 'Utilitas', 'slug' => 'utilities'],
            ['name' => 'Sewa', 'slug' => 'rent'],
            ['name' => 'Transportasi', 'slug' => 'transport'],
            ['name' => 'Maintenance', 'slug' => 'maintenance'],
            ['name' => 'Lain-lain', 'slug' => 'other'],
        ];

        foreach ($defaults as $cat) {
            self::create(array_merge($cat, ['store_id' => $storeId]));
        }
    }
}
