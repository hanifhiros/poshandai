<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ForStoreScope;

class Shift extends Model
{
    use ForStoreScope;

    protected $table = 'shifts';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'is_active',
        'store_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──

    public function schedules()
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
