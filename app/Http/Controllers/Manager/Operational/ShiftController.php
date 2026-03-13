<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Store;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $shifts = Shift::where('store_id', $storeId)->orderBy('start_time')->get();

        return view('handai-manager.operational.shifts.index', compact('selected_store', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'start_time'             => 'required',
            'end_time'               => 'required',
            'break_duration_minutes' => 'required|integer|min:0',
        ]);

        Shift::create([
            'name'                   => $request->name,
            'start_time'             => $request->start_time,
            'end_time'               => $request->end_time,
            'break_duration_minutes' => $request->break_duration_minutes,
            'is_active'              => true,
            'store_id'               => session('selected_store'),
        ]);

        return redirect()->route('manager.operational.shifts.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);
        abort_if($shift->store_id != session('selected_store'), 403);

        $request->validate([
            'name'                   => 'required|string|max:255',
            'start_time'             => 'required',
            'end_time'               => 'required',
            'break_duration_minutes' => 'required|integer|min:0',
        ]);

        $shift->update($request->only('name', 'start_time', 'end_time', 'break_duration_minutes', 'is_active'));

        return redirect()->route('manager.operational.shifts.index')
            ->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        abort_if($shift->store_id != session('selected_store'), 403);

        $shift->delete();

        return redirect()->route('manager.operational.shifts.index')
            ->with('success', 'Shift berhasil dihapus.');
    }
}
