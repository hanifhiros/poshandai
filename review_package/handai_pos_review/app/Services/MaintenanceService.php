<?php

namespace App\Services;

use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceLog;
use App\Models\Equipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MaintenanceService
{
    /**
     * Get upcoming maintenance tasks within $daysAhead.
     */
    public static function getUpcomingMaintenance(int $storeId, int $daysAhead = 7): \Illuminate\Database\Eloquent\Collection
    {
        return MaintenanceSchedule::with('equipment')
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->whereDate('next_due_date', '<=', Carbon::today()->addDays($daysAhead))
            ->whereDate('next_due_date', '>=', Carbon::today())
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get overdue maintenance.
     */
    public static function getOverdueMaintenance(int $storeId): \Illuminate\Database\Eloquent\Collection
    {
        return MaintenanceSchedule::with('equipment')
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->whereDate('next_due_date', '<', Carbon::today())
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Log a maintenance performed, update schedule, optionally record cost journal.
     */
    public static function logMaintenance(array $data): MaintenanceLog
    {
        $log = MaintenanceLog::create($data);

        // Update the schedule's last_performed_date and next_due_date
        if ($log->maintenance_schedule_id) {
            $schedule = MaintenanceSchedule::find($log->maintenance_schedule_id);
            if ($schedule) {
                $schedule->last_performed_date = $log->performed_date;
                $schedule->next_due_date = $schedule->calculateNextDueDate();
                $schedule->save();
            }
        }

        // If equipment was under_maintenance, set back to operational
        $equipment = Equipment::find($log->equipment_id);
        if ($equipment && $equipment->status === 'under_maintenance' && $log->status === 'completed') {
            $equipment->update(['status' => 'operational']);
        }

        // Record accounting if cost > 0
        if ($log->cost > 0 && $log->store_id) {
            try {
                AccountingService::createJournal(
                    $log->store_id,
                    "Maintenance: {$equipment->name} — {$log->description}",
                    'MAINTENANCE',
                    [
                        [
                            'account_sub_type' => 'operasional',
                            'debit'  => $log->cost,
                            'credit' => 0,
                            'memo'   => "Biaya maintenance {$equipment->name}",
                        ],
                        [
                            'account_sub_type' => 'kas',
                            'debit'  => 0,
                            'credit' => $log->cost,
                            'memo'   => 'Pembayaran maintenance',
                        ],
                    ],
                    'maintenance_logs',
                    $log->id,
                    $log->performed_date->format('Y-m-d')
                );
            } catch (\Exception $e) {
                Log::warning('Maintenance journal failed: ' . $e->getMessage());
            }
        }

        return $log;
    }

    /**
     * Equipment downtime & cost report.
     */
    public static function getEquipmentReport(int $storeId, string $startDate, string $endDate): array
    {
        $logs = MaintenanceLog::with('equipment')
            ->where('store_id', $storeId)
            ->whereBetween('performed_date', [$startDate, $endDate])
            ->get();

        $report = [];
        foreach ($logs->groupBy('equipment_id') as $equipmentId => $eqLogs) {
            $equipment = $eqLogs->first()->equipment;
            $report[] = [
                'equipment'         => $equipment,
                'total_events'      => $eqLogs->count(),
                'total_cost'        => $eqLogs->sum('cost'),
                'total_downtime'    => $eqLogs->sum('downtime_minutes'),
                'preventive_count'  => $eqLogs->where('maintenance_type', 'preventive')->count(),
                'corrective_count'  => $eqLogs->where('maintenance_type', 'corrective')->count(),
                'emergency_count'   => $eqLogs->where('maintenance_type', 'emergency')->count(),
            ];
        }

        return $report;
    }
}
