<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcNonConformance extends Model
{
    use \App\Models\Traits\ForStoreScope;

    protected $fillable = [
        'store_id', 'qc_inspection_id', 'nc_number', 'issue_description',
        'severity', 'action_taken', 'corrective_action', 'preventive_action',
        'assigned_to', 'status', 'due_date', 'closed_date',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'closed_date' => 'date',
    ];

    const SEVERITIES = [
        'minor'    => 'Minor',
        'major'    => 'Major',
        'critical' => 'Critical',
    ];

    const ACTIONS = [
        'rework'          => 'Rework',
        'reject'          => 'Reject',
        'use_as_is'       => 'Gunakan Apa Adanya',
        'return_supplier' => 'Return ke Supplier',
        'pending'         => 'Pending',
    ];

    public function inspection()
    {
        return $this->belongsTo(QcInspection::class, 'qc_inspection_id');
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public static function generateNumber($storeId): string
    {
        $today = now()->format('Ymd');
        $last = static::where('store_id', $storeId)
            ->where('nc_number', 'like', "NC-{$today}-%")
            ->orderByDesc('nc_number')
            ->value('nc_number');

        $seq = 1;
        if ($last) {
            $seq = (int) substr($last, -3) + 1;
        }
        return 'NC-' . $today . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
