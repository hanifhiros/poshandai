<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    use \App\Models\Traits\ForStoreScope;

    protected $fillable = [
        'store_id', 'plan_number', 'name', 'plan_date', 'start_date', 'end_date',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'plan_date'  => 'date',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    const STATUSES = [
        'draft'       => 'Draft',
        'confirmed'   => 'Dikonfirmasi',
        'in_progress' => 'Berjalan',
        'completed'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];

    public function items()
    {
        return $this->hasMany(ProductionPlanItem::class);
    }

    public function materialRequirements()
    {
        return $this->hasMany(MaterialRequirement::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getProgressAttribute(): float
    {
        $items = $this->items;
        if ($items->isEmpty()) return 0;

        $totalPlanned  = $items->sum('planned_quantity');
        if ($totalPlanned == 0) return 0;

        $totalProduced = $items->sum('produced_quantity');
        return round(($totalProduced / $totalPlanned) * 100, 1);
    }

    public function getHasShortageAttribute(): bool
    {
        return $this->materialRequirements()->where('status', 'short')->exists();
    }

    public static function generateNumber($storeId): string
    {
        $today = now()->format('Ymd');
        $last  = static::where('store_id', $storeId)
            ->where('plan_number', 'like', "PP-{$today}-%")
            ->orderByDesc('plan_number')
            ->value('plan_number');

        $seq = 1;
        if ($last) {
            $seq = (int) substr($last, -3) + 1;
        }
        return 'PP-' . $today . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
