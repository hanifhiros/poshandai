<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ReturnOrder;
use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use App\Models\MaintenanceSchedule;
use App\Models\StockAlert;
use App\Models\Stock;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;

class OperationalKpiService
{
    /**
     * Collect all operational KPIs for a store within date range.
     */
    public static function getKpis(int $storeId, string $startDate, string $endDate): array
    {
        return [
            // production/quality modules removed
            'production'   => ['completion_rate' => 0], // stub to keep array shape
            'inventory'    => self::inventoryKpis($storeId),
            'returns'      => self::returnKpis($storeId, $startDate, $endDate),
            'attendance'   => self::attendanceKpis($storeId, $startDate, $endDate),
            'maintenance'  => self::maintenanceKpis($storeId, $startDate, $endDate),
            'sales'        => self::salesKpis($storeId, $startDate, $endDate),
        ];
    }

    // production and quality functions removed per user request

    private static function inventoryKpis(int $storeId): array
    {
        $alerts = StockAlert::where('store_id', $storeId)
            ->where('status', 'active')
            ->get();

        $lowStock = $alerts->where('alert_type', 'low_stock')->count();
        $outOfStock = $alerts->where('alert_type', 'out_of_stock')->count();

        // SQLite doesn't have a 'quantity' column on stock; compute value using batches
        // unit_qty holds the batch quantity
        $totalStockValue = StockBatch::where('store_id', $storeId)
            ->selectRaw('SUM(unit_qty * cost) as total')
            ->value('total') ?? 0;

        return [
            'active_alerts'     => $alerts->count(),
            'low_stock_count'   => $lowStock,
            'out_of_stock_count' => $outOfStock,
            'total_stock_value' => $totalStockValue,
        ];
    }

    private static function returnKpis(int $storeId, string $start, string $end): array
    {
        $returns = ReturnOrder::where('store_id', $storeId)
            ->whereBetween('return_date', [$start, $end])
            ->get();

        $orders = Order::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'total_returns'   => $returns->count(),
            'return_rate'     => $orders > 0 ? round($returns->count() / $orders * 100, 2) : 0,
            'total_refunded'  => $returns->whereIn('status', ['processed', 'completed'])->sum('total_refund_amount'),
            'pending_returns' => $returns->where('status', 'pending')->count(),
        ];
    }

    private static function attendanceKpis(int $storeId, string $start, string $end): array
    {
        $attendances = Attendance::where('store_id', $storeId)
            ->whereBetween('attendance_date', [$start, $end])
            ->get();

        $total = $attendances->count();
        $onTime = $attendances->where('status', 'present')->where('late_minutes', 0)->count();
        $late = $attendances->where('late_minutes', '>', 0)->count();
        $totalOvertime = $attendances->sum('overtime_minutes');

        return [
            'total_records'    => $total,
            'on_time_rate'     => $total > 0 ? round($onTime / $total * 100, 1) : 0,
            'late_count'       => $late,
            'total_overtime_hours' => round($totalOvertime / 60, 1),
        ];
    }

    private static function maintenanceKpis(int $storeId, string $start, string $end): array
    {
        $logs = MaintenanceLog::where('store_id', $storeId)
            ->whereBetween('performed_date', [$start, $end])
            ->get();

        $overdue = MaintenanceSchedule::where('store_id', $storeId)
            ->where('is_active', true)
            ->where('next_due_date', '<', now()->toDateString())
            ->count();

        $equipmentCount = Equipment::where('store_id', $storeId)->count();
        $operationalCount = Equipment::where('store_id', $storeId)->where('status', 'operational')->count();

        return [
            'total_maintenance'   => $logs->count(),
            'total_cost'          => $logs->sum('cost'),
            'total_downtime_hours' => round($logs->sum('downtime_minutes') / 60, 1),
            'overdue_schedules'   => $overdue,
            'equipment_uptime'    => $equipmentCount > 0 ? round($operationalCount / $equipmentCount * 100, 1) : 0,
        ];
    }

    private static function salesKpis(int $storeId, string $start, string $end): array
    {
        $orders = Order::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return [
            'total_orders'    => $orders->count(),
            'total_revenue'   => $orders->sum('gross_amount'),
            'avg_order_value' => $orders->count() > 0 ? round($orders->avg('gross_amount'), 0) : 0,
        ];
    }
}
