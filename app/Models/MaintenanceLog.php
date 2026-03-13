<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;

class MaintenanceLog extends Model
{
    use ForStoreScope;

    protected $table = 'maintenance_logs';

    protected $fillable = [
        'equipment_id', 'maintenance_schedule_id', 'maintenance_type',
        'performed_date', 'performed_by', 'description', 'cost',
        'parts_replaced', 'downtime_minutes', 'status',
        'next_scheduled_date', 'notes', 'store_id',
    ];

    protected $casts = [
        'performed_date'      => 'date',
        'cost'                => 'decimal:2',
        'downtime_minutes'    => 'integer',
        'next_scheduled_date' => 'date',
    ];

    // ── Relationships ──

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id');
    }

    public function performer()
    {
        return $this->belongsTo(Employee::class, 'performed_by');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
