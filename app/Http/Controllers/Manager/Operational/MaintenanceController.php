<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceLog;
use App\Models\Employee;
use App\Models\Store;
use App\Services\MaintenanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    // ── Dashboard ──
    public function dashboard()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $totalEquipment = Equipment::where('store_id', $storeId)->count();
        $operational = Equipment::where('store_id', $storeId)->where('status', 'operational')->count();
        $upcoming = MaintenanceService::getUpcomingMaintenance($storeId);
        $overdue = MaintenanceService::getOverdueMaintenance($storeId);

        return view('handai-manager.operational.maintenance.dashboard', compact(
            'selected_store', 'totalEquipment', 'operational', 'upcoming', 'overdue'
        ));
    }

    // ── Equipment CRUD ──
    public function equipmentIndex()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $query = Equipment::where('store_id', $storeId);

        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('search')) {
            $s = request('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }

        $equipment = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('handai-manager.operational.maintenance.equipment.index', compact('selected_store', 'equipment'));
    }

    public function equipmentCreate()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        return view('handai-manager.operational.maintenance.equipment.create', compact('selected_store'));
    }

    public function equipmentStore(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:50',
            'category' => 'required|in:cooking,refrigeration,mixing,packaging,cleaning,other',
        ]);

        $storeId = session('selected_store');

        Equipment::create(array_merge($request->only(
            'name', 'code', 'category', 'brand', 'model_number', 'serial_number',
            'purchase_date', 'purchase_cost', 'warranty_expiry', 'location', 'notes'
        ), ['store_id' => $storeId, 'status' => 'operational']));

        return redirect()->route('manager.operational.maintenance.equipment.index')
            ->with('success', 'Peralatan berhasil ditambahkan.');
    }

    public function equipmentShow($id)
    {
        $storeId = session('selected_store');
        $equipment = Equipment::findOrFail($id);
        abort_if($equipment->store_id != $storeId, 403);

        $selected_store = Store::find($storeId);
        $schedules = MaintenanceSchedule::where('equipment_id', $id)->where('is_active', true)->orderBy('next_due_date')->get();
        $logs = MaintenanceLog::with('performer')->where('equipment_id', $id)->orderByDesc('performed_date')->paginate(10);

        return view('handai-manager.operational.maintenance.equipment.show', compact(
            'selected_store', 'equipment', 'schedules', 'logs'
        ));
    }

    public function equipmentEdit($id)
    {
        $equipment = Equipment::findOrFail($id);
        abort_if($equipment->store_id != session('selected_store'), 403);
        $selected_store = Store::find(session('selected_store'));

        return view('handai-manager.operational.maintenance.equipment.edit', compact('selected_store', 'equipment'));
    }

    public function equipmentUpdate(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
        abort_if($equipment->store_id != session('selected_store'), 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:50',
            'category' => 'required|in:cooking,refrigeration,mixing,packaging,cleaning,other',
        ]);

        $equipment->update($request->only(
            'name', 'code', 'category', 'brand', 'model_number', 'serial_number',
            'purchase_date', 'purchase_cost', 'warranty_expiry', 'location', 'status', 'notes'
        ));

        return redirect()->route('manager.operational.maintenance.equipment.show', $id)
            ->with('success', 'Peralatan berhasil diperbarui.');
    }

    public function equipmentDestroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        abort_if($equipment->store_id != session('selected_store'), 403);
        $equipment->delete();

        return redirect()->route('manager.operational.maintenance.equipment.index')
            ->with('success', 'Peralatan berhasil dihapus.');
    }

    // ── Maintenance Schedule ──
    public function scheduleCreate($equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        abort_if($equipment->store_id != session('selected_store'), 403);
        $selected_store = Store::find(session('selected_store'));

        return view('handai-manager.operational.maintenance.schedules.create', compact('selected_store', 'equipment'));
    }

    public function scheduleStore(Request $request, $equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        abort_if($equipment->store_id != session('selected_store'), 403);

        $request->validate([
            'task_name'     => 'required|string|max:255',
            'frequency'     => 'required|in:daily,weekly,biweekly,monthly,quarterly,semi_annual,annual',
            'next_due_date' => 'required|date',
        ]);

        MaintenanceSchedule::create([
            'equipment_id'  => $equipmentId,
            'task_name'     => $request->task_name,
            'description'   => $request->description,
            'frequency'     => $request->frequency,
            'next_due_date' => $request->next_due_date,
            'is_active'     => true,
            'store_id'      => session('selected_store'),
        ]);

        return redirect()->route('manager.operational.maintenance.equipment.show', $equipmentId)
            ->with('success', 'Jadwal maintenance berhasil dibuat.');
    }

    public function scheduleDestroy($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        abort_if($schedule->store_id != session('selected_store'), 403);
        $equipmentId = $schedule->equipment_id;
        $schedule->delete();

        return redirect()->route('manager.operational.maintenance.equipment.show', $equipmentId)
            ->with('success', 'Jadwal maintenance berhasil dihapus.');
    }

    // ── Maintenance Logs ──
    public function logCreate($equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        abort_if($equipment->store_id != session('selected_store'), 403);
        $selected_store = Store::find(session('selected_store'));

        $schedules = MaintenanceSchedule::where('equipment_id', $equipmentId)->where('is_active', true)->get();
        $employees = Employee::where('store_id', session('selected_store'))->orderBy('name')->get();

        return view('handai-manager.operational.maintenance.logs.create', compact(
            'selected_store', 'equipment', 'schedules', 'employees'
        ));
    }

    public function logStore(Request $request, $equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        abort_if($equipment->store_id != session('selected_store'), 403);

        $request->validate([
            'maintenance_type' => 'required|in:preventive,corrective,emergency',
            'performed_date'   => 'required|date',
            'description'      => 'required|string',
            'cost'             => 'nullable|numeric|min:0',
            'downtime_minutes' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $equipmentId) {
            MaintenanceService::logMaintenance([
                'equipment_id'             => $equipmentId,
                'maintenance_schedule_id'  => $request->maintenance_schedule_id,
                'maintenance_type'         => $request->maintenance_type,
                'performed_date'           => $request->performed_date,
                'performed_by'             => $request->performed_by,
                'description'              => $request->description,
                'cost'                     => $request->cost ?? 0,
                'parts_replaced'           => $request->parts_replaced,
                'downtime_minutes'         => $request->downtime_minutes ?? 0,
                'status'                   => $request->status ?? 'completed',
                'notes'                    => $request->notes,
                'store_id'                 => session('selected_store'),
            ]);
        });

        return redirect()->route('manager.operational.maintenance.equipment.show', $equipmentId)
            ->with('success', 'Log maintenance berhasil dicatat.');
    }

    // ── Report ──
    public function report(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = Store::find($storeId);

        $startDate = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $report = MaintenanceService::getEquipmentReport($storeId, $startDate, $endDate);

        $totalCost = collect($report)->sum('total_cost');
        $totalDowntime = collect($report)->sum('total_downtime');

        return view('handai-manager.operational.maintenance.report', compact(
            'selected_store', 'report', 'startDate', 'endDate', 'totalCost', 'totalDowntime'
        ));
    }
}
