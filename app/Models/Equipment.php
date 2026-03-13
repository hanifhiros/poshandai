<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;
use Carbon\Carbon;

class Equipment extends Model
{
    use ForStoreScope;

    protected $table = 'equipment';

    protected $fillable = [
        'name', 'code', 'category', 'brand', 'model_number', 'serial_number',
        'purchase_date', 'purchase_cost', 'warranty_expiry', 'location',
        'status', 'notes', 'store_id',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'purchase_cost'  => 'decimal:2',
        'warranty_expiry' => 'date',
    ];

    const CATEGORIES = [
        'cooking'       => 'Memasak',
        'refrigeration' => 'Pendingin',
        'mixing'        => 'Mixer/Pencampur',
        'packaging'     => 'Pengemasan',
        'cleaning'      => 'Kebersihan',
        'other'         => 'Lainnya',
    ];

    public function getIsWarrantyActiveAttribute(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }

    public function getTotalMaintenanceCostAttribute(): float
    {
        return $this->maintenanceLogs()->sum('cost');
    }

    // ── Relationships ──

    public function maintenanceSchedules()
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
