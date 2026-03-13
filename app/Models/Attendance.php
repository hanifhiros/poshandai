<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;
use Carbon\Carbon;

class Attendance extends Model
{
    use ForStoreScope;

    protected $table = 'attendances';

    protected $fillable = [
        'employee_id',
        'shift_schedule_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'clock_in_method',
        'status',
        'late_minutes',
        'overtime_minutes',
        'notes',
        'recorded_by',
        'store_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in'        => 'datetime',
        'clock_out'       => 'datetime',
        'late_minutes'    => 'integer',
        'overtime_minutes' => 'integer',
    ];

    // ── Accessors ──

    public function getWorkingHoursAttribute(): ?float
    {
        if (!$this->clock_in || !$this->clock_out) return null;
        $minutes = $this->clock_in->diffInMinutes($this->clock_out);
        $breakMinutes = $this->shiftSchedule?->shift?->break_duration_minutes ?? 60;
        return round(max(0, $minutes - $breakMinutes) / 60, 2);
    }

    public function getIsLateAttribute(): bool
    {
        return $this->late_minutes > 0;
    }

    public function getIsOvertimeAttribute(): bool
    {
        return $this->overtime_minutes > 0;
    }

    // ── Relationships ──

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftSchedule()
    {
        return $this->belongsTo(ShiftSchedule::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
