<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Weekly schedule view — calendar grid.
     */
    public function schedule(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $dates = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $dates[] = $d->copy();
        }

        $employees = Employee::where('store_id', $storeId)->orderBy('name')->get();
        $shifts = Shift::where('store_id', $storeId)->where('is_active', true)->orderBy('start_time')->get();

        $schedules = ShiftSchedule::where('store_id', $storeId)
            ->whereBetween('schedule_date', [$weekStart, $weekEnd])
            ->get()
            ->groupBy(fn($s) => $s->employee_id . '-' . $s->schedule_date->format('Y-m-d'));

        return view('handai-manager.operational.attendance.schedule', compact(
            'selected_store', 'employees', 'shifts', 'dates', 'schedules', 'weekStart', 'weekEnd'
        ));
    }

    /**
     * Bulk save schedule for a week.
     */
    public function storeSchedule(Request $request)
    {
        $storeId = session('selected_store');
        $request->validate([
            'schedules'              => 'required|array',
            'schedules.*.employee_id' => 'required|integer',
            'schedules.*.date'       => 'required|date',
            'schedules.*.shift_id'   => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $storeId) {
            foreach ($request->schedules as $entry) {
                if (empty($entry['shift_id'])) {
                    // Remove schedule if shift cleared
                    ShiftSchedule::where('employee_id', $entry['employee_id'])
                        ->where('schedule_date', $entry['date'])
                        ->where('store_id', $storeId)
                        ->delete();
                    continue;
                }

                ShiftSchedule::updateOrCreate(
                    [
                        'employee_id'   => $entry['employee_id'],
                        'schedule_date' => $entry['date'],
                        'store_id'      => $storeId,
                    ],
                    [
                        'shift_id' => $entry['shift_id'],
                        'status'   => 'scheduled',
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Jadwal berhasil disimpan.');
    }

    /**
     * Attendance records list.
     */
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $query = Attendance::with(['employee', 'shiftSchedule.shift'])
            ->where('store_id', $storeId);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderByDesc('attendance_date')->paginate(20)->withQueryString();
        $employees = Employee::where('store_id', $storeId)->orderBy('name')->get();

        return view('handai-manager.operational.attendance.index', compact(
            'selected_store', 'attendances', 'employees'
        ));
    }

    /**
     * Clock in an employee.
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
        ]);

        // ensure employee belongs to this store
        $employee = Employee::find($request->employee_id);
        if (!$employee || $employee->store_id != $storeId) {
            abort(404, 'Employee not found');
        }

        $storeId = session('selected_store');
        $today = Carbon::today();

        $schedule = ShiftSchedule::where('employee_id', $request->employee_id)
            ->where('schedule_date', $today)
            ->where('store_id', $storeId)
            ->first();

        $lateMinutes = 0;
        if ($schedule && $schedule->shift) {
            $shiftStart = Carbon::parse($today->format('Y-m-d') . ' ' . $schedule->shift->start_time);
            if (Carbon::now()->gt($shiftStart)) {
                $lateMinutes = Carbon::now()->diffInMinutes($shiftStart);
            }
        }

        $attributes = [
            'employee_id'     => $request->employee_id,
            'attendance_date' => $today,
            'store_id'        => $storeId,
        ];

        $values = [
            'clock_in'        => Carbon::now(),
            'clock_in_method' => 'manual',
            'status'          => $lateMinutes > 0 ? 'late' : 'present',
            'late_minutes'    => $lateMinutes,
            'recorded_by'     => Auth::id(),
        ];

        if ($schedule) {
            // only include FK when we actually have a schedule
            $values['shift_schedule_id'] = $schedule->id;
        }

        Attendance::updateOrCreate($attributes, $values);

        return redirect()->route('manager.operational.attendance.index')
            ->with('success', 'Clock-in berhasil dicatat.');
    }

    /**
     * Clock out an employee.
     */
    public function clockOut(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        abort_if($attendance->store_id != session('selected_store'), 403);

        $overtimeMinutes = 0;
        if ($attendance->shiftSchedule?->shift) {
            $shiftEnd = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->shiftSchedule->shift->end_time);
            if (Carbon::now()->gt($shiftEnd)) {
                $overtimeMinutes = Carbon::now()->diffInMinutes($shiftEnd);
            }
        }

        $attendance->update([
            'clock_out'        => Carbon::now(),
            'overtime_minutes' => $overtimeMinutes,
        ]);

        return redirect()->route('manager.operational.attendance.index')
            ->with('success', 'Clock-out berhasil dicatat.');
    }

    /**
     * Monthly summary report.
     */
    public function summary(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $employees = Employee::where('store_id', $storeId)->orderBy('name')->get();

        $summaries = [];
        foreach ($employees as $emp) {
            $attendances = Attendance::where('employee_id', $emp->id)
                ->where('store_id', $storeId)
                ->whereBetween('attendance_date', [$startDate, $endDate])
                ->get();

            $summaries[] = [
                'employee'       => $emp,
                'total_present'  => $attendances->whereIn('status', ['present', 'late'])->count(),
                'total_late'     => $attendances->where('status', 'late')->count(),
                'total_absent'   => $attendances->where('status', 'absent')->count(),
                'total_leave'    => $attendances->where('status', 'leave')->count(),
                'total_overtime' => round($attendances->sum('overtime_minutes') / 60, 1),
                'total_late_min' => $attendances->sum('late_minutes'),
            ];
        }

        return view('handai-manager.operational.attendance.summary', compact(
            'selected_store', 'summaries', 'month'
        ));
    }

    /**
     * Bulk record attendance (manager input at end of day).
     */
    public function bulkRecord(Request $request)
    {
        $request->validate([
            'records'                => 'required|array',
            'records.*.employee_id'  => 'required|integer',
            'records.*.status'       => 'required|in:present,late,absent,half_day,leave',
            'attendance_date'        => 'required|date',
        ]);

        $storeId = session('selected_store');

        DB::transaction(function () use ($request, $storeId) {
            foreach ($request->records as $record) {
                Attendance::updateOrCreate(
                    [
                        'employee_id'     => $record['employee_id'],
                        'attendance_date' => $request->attendance_date,
                        'store_id'        => $storeId,
                    ],
                    [
                        'status'      => $record['status'],
                        'notes'       => $record['notes'] ?? null,
                        'recorded_by' => Auth::id(),
                    ]
                );
            }
        });

        return redirect()->route('manager.operational.attendance.index')
            ->with('success', 'Absensi batch berhasil disimpan.');
    }
}
