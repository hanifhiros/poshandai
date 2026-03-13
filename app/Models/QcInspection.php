<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcInspection extends Model
{
    use \App\Models\Traits\ForStoreScope;

    protected $fillable = [
        'store_id', 'inspection_number', 'qc_standard_id', 'inspection_type',
        'inspectable_type', 'inspectable_id', 'item_name',
        'quantity_inspected', 'quantity_passed', 'quantity_failed',
        'checklist_results', 'result', 'inspector_id', 'inspection_date', 'notes',
    ];

    protected $casts = [
        'checklist_results'  => 'array',
        'quantity_inspected' => 'decimal:3',
        'quantity_passed'    => 'decimal:3',
        'quantity_failed'    => 'decimal:3',
        'inspection_date'    => 'date',
    ];

    const RESULTS = [
        'pass'        => 'Lulus',
        'fail'        => 'Gagal',
        'conditional' => 'Bersyarat',
        'pending'     => 'Menunggu',
    ];

    public function standard()
    {
        return $this->belongsTo(QcStandard::class, 'qc_standard_id');
    }

    public function inspector()
    {
        return $this->belongsTo(Employee::class, 'inspector_id');
    }

    public function inspectable()
    {
        return $this->morphTo();
    }

    public function nonConformances()
    {
        return $this->hasMany(QcNonConformance::class, 'qc_inspection_id');
    }

    public function getPassRateAttribute(): float
    {
        if ($this->quantity_inspected == 0) return 0;
        return round(($this->quantity_passed / $this->quantity_inspected) * 100, 1);
    }

    public static function generateNumber($storeId): string
    {
        $today = now()->format('Ymd');
        $last = static::where('store_id', $storeId)
            ->where('inspection_number', 'like', "QC-{$today}-%")
            ->orderByDesc('inspection_number')
            ->value('inspection_number');

        $seq = 1;
        if ($last) {
            $seq = (int) substr($last, -3) + 1;
        }
        return 'QC-' . $today . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
