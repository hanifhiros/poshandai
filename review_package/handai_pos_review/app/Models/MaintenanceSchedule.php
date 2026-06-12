<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;
use Carbon\Carbon;

class MaintenanceSchedule extends Model
{
    use ForStoreScope;

    protected $table = 'maintenance_schedules';

    protected $fillable = [
        'equipment_id', 'task_name', 'description', 'frequency',
        'last_performed_date', 'next_due_date', 'is_active', 'store_id',
    ];

    protected $casts = [
        'last_performed_date' => 'date',
        'next_due_date'       => 'date',
        'is_active'           => 'boolean',
    ];

    const FREQUENCIES = [
        'daily'       => 'Harian',
        'weekly'      => 'Mingguan',
        'biweekly'    => '2 Mingguan',
        'monthly'     => 'Bulanan',
        'quarterly'   => '3 Bulanan',
        'semi_annual' => '6 Bulanan',
        'annual'      => 'Tahunan',
    ];

    public function calculateNextDueDate(): Carbon
    {
        $base = $this->last_performed_date ?? Carbon::today();

        return match ($this->frequency) {
            'daily'       => $base->copy()->addDay(),
            'weekly'      => $base->copy()->addWeek(),
            'biweekly'    => $base->copy()->addWeeks(2),
            'monthly'     => $base->copy()->addMonth(),
            'quarterly'   => $base->copy()->addMonths(3),
            'semi_annual' => $base->copy()->addMonths(6),
            'annual'      => $base->copy()->addYear(),
            default       => $base->copy()->addMonth(),
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->is_active && $this->next_due_date->isPast();
    }

    // ── Relationships ──

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
