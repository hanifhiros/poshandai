<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;

class ShiftSchedule extends Model
{
    use ForStoreScope;

    protected $table = 'shift_schedules';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'schedule_date',
        'status',
        'notes',
        'store_id',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    // ── Relationships ──

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
