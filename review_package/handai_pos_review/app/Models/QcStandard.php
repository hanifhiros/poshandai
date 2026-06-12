<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcStandard extends Model
{
    use \App\Models\Traits\ForStoreScope;

    protected $fillable = [
        'store_id', 'name', 'category', 'description', 'checklist_items', 'is_active',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'is_active'       => 'boolean',
    ];

    const CATEGORIES = [
        'production' => 'Produksi',
        'incoming'   => 'Bahan Masuk',
        'outgoing'   => 'Produk Keluar',
    ];

    public function inspections()
    {
        return $this->hasMany(QcInspection::class);
    }
}
