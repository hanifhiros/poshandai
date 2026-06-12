<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\QcInspection;
use App\Models\QcNonConformance;
use App\Models\QcStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QualityControlController extends Controller
{
    // ── Standards ──────────────────────────────────────────
    public function standards(Request $request)
    {
        $storeId = session('selected_store');
        $standards = QcStandard::where('store_id', $storeId)
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        return view('handai-manager.operational.quality-control.standards.index', compact('standards'));
    }

    public function createStandard()
    {
        return view('handai-manager.operational.quality-control.standards.create');
    }

    public function storeStandard(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'category'          => 'required|in:production,incoming,outgoing',
            'description'       => 'nullable|string',
            'checklist_items'   => 'required|array|min:1',
            'checklist_items.*' => 'required|string|max:255',
        ]);

        QcStandard::create([
            'store_id'        => session('selected_store'),
            'name'            => $request->name,
            'category'        => $request->category,
            'description'     => $request->description,
            'checklist_items' => array_values($request->checklist_items),
        ]);

        return redirect()->route('manager.operational.qc.standards.index')
            ->with('success', 'Standar QC berhasil dibuat.');
    }

    public function editStandard(QcStandard $standard)
    {
        abort_if($standard->store_id != session('selected_store'), 403);
        return view('handai-manager.operational.quality-control.standards.edit', compact('standard'));
    }

    public function updateStandard(Request $request, QcStandard $standard)
    {
        abort_if($standard->store_id != session('selected_store'), 403);

        $request->validate([
            'name'              => 'required|string|max:255',
            'category'          => 'required|in:production,incoming,outgoing',
            'description'       => 'nullable|string',
            'checklist_items'   => 'required|array|min:1',
            'checklist_items.*' => 'required|string|max:255',
            'is_active'         => 'boolean',
        ]);

        $standard->update([
            'name'            => $request->name,
            'category'        => $request->category,
            'description'     => $request->description,
            'checklist_items' => array_values($request->checklist_items),
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return redirect()->route('manager.operational.qc.standards.index')
            ->with('success', 'Standar QC diperbarui.');
    }

    // ── Inspections ───────────────────────────────────────
    public function inspections(Request $request)
    {
        $storeId = session('selected_store');
        $query = QcInspection::where('store_id', $storeId)
            ->with(['standard', 'inspector'])
            ->latest('inspection_date');

        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }
        if ($request->filled('type')) {
            $query->where('inspection_type', $request->type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('item_name', 'like', "%{$s}%")->orWhere('inspection_number', 'like', "%{$s}%"));
        }

        $inspections = $query->paginate(20);

        return view('handai-manager.operational.quality-control.inspections.index', compact('inspections'));
    }

    public function createInspection()
    {
        $storeId = session('selected_store');
        $standards = QcStandard::where('store_id', $storeId)->where('is_active', true)->get();
        $employees = Employee::where('store_id', $storeId)->where('is_active', true)->get();

        return view('handai-manager.operational.quality-control.inspections.create', compact('standards', 'employees'));
    }

    public function storeInspection(Request $request)
    {
        $storeId = session('selected_store');

        $request->validate([
            'qc_standard_id'     => 'nullable|exists:qc_standards,id',
            'inspection_type'    => 'required|in:production,incoming,outgoing',
            'item_name'          => 'required|string|max:255',
            'quantity_inspected' => 'required|numeric|min:0.001',
            'quantity_passed'    => 'required|numeric|min:0',
            'quantity_failed'    => 'required|numeric|min:0',
            'inspection_date'    => 'required|date',
            'inspector_id'       => 'nullable|exists:employees,id',
            'notes'              => 'nullable|string',
            'checklist_results'  => 'nullable|array',
        ]);

        $result = 'pending';
        if ($request->quantity_failed == 0 && $request->quantity_passed > 0) {
            $result = 'pass';
        } elseif ($request->quantity_failed > 0 && $request->quantity_passed > 0) {
            $result = 'conditional';
        } elseif ($request->quantity_passed == 0 && $request->quantity_failed > 0) {
            $result = 'fail';
        }

        $inspection = QcInspection::create([
            'store_id'           => $storeId,
            'inspection_number'  => QcInspection::generateNumber($storeId),
            'qc_standard_id'     => $request->qc_standard_id,
            'inspection_type'    => $request->inspection_type,
            'inspectable_type'   => null,
            'inspectable_id'     => 0,
            'item_name'          => $request->item_name,
            'quantity_inspected' => $request->quantity_inspected,
            'quantity_passed'    => $request->quantity_passed,
            'quantity_failed'    => $request->quantity_failed,
            'checklist_results'  => $request->checklist_results,
            'result'             => $result,
            'inspector_id'       => $request->inspector_id,
            'inspection_date'    => $request->inspection_date,
            'notes'              => $request->notes,
        ]);

        return redirect()->route('manager.operational.qc.inspections.show', $inspection)
            ->with('success', 'Inspeksi QC berhasil dicatat.');
    }

    public function destroyStandard($id)
    {
        $standard = QcStandard::findOrFail($id);
        abort_if($standard->store_id != session('selected_store'), 403);
        $standard->delete();
        return redirect()->route('manager.operational.qc.standards.index')
            ->with('success', 'Standar QC telah dihapus.');
    }

    public function showInspection(QcInspection $inspection)
    {
        abort_if($inspection->store_id != session('selected_store'), 403);
        $inspection->load(['standard', 'inspector', 'nonConformances.assignee']);

        return view('handai-manager.operational.quality-control.inspections.show', compact('inspection'));
    }

    // ── Non-Conformances ──────────────────────────────────
    public function nonConformances(Request $request)
    {
        $storeId = session('selected_store');
        $query = QcNonConformance::where('store_id', $storeId)
            ->with(['inspection', 'assignee'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $nonConformances = $query->paginate(20);

        return view('handai-manager.operational.quality-control.non-conformances.index', compact('nonConformances'));
    }

    public function createNonConformance(QcInspection $inspection)
    {
        abort_if($inspection->store_id != session('selected_store'), 403);
        $employees = Employee::where('store_id', session('selected_store'))->where('is_active', true)->get();

        return view('handai-manager.operational.quality-control.non-conformances.create', compact('inspection', 'employees'));
    }

    public function storeNonConformance(Request $request, QcInspection $inspection)
    {
        abort_if($inspection->store_id != session('selected_store'), 403);

        $request->validate([
            'issue_description'  => 'required|string|max:500',
            'severity'           => 'required|in:minor,major,critical',
            'action_taken'       => 'required|in:rework,reject,use_as_is,return_supplier,pending',
            'corrective_action'  => 'nullable|string',
            'preventive_action'  => 'nullable|string',
            'assigned_to'        => 'nullable|exists:employees,id',
            'due_date'           => 'nullable|date',
        ]);

        QcNonConformance::create([
            'store_id'           => session('selected_store'),
            'qc_inspection_id'   => $inspection->id,
            'nc_number'          => QcNonConformance::generateNumber(session('selected_store')),
            'issue_description'  => $request->issue_description,
            'severity'           => $request->severity,
            'action_taken'       => $request->action_taken,
            'corrective_action'  => $request->corrective_action,
            'preventive_action'  => $request->preventive_action,
            'assigned_to'        => $request->assigned_to,
            'due_date'           => $request->due_date,
        ]);

        return redirect()->route('manager.operational.qc.inspections.show', $inspection)
            ->with('success', 'Non-Conformance berhasil dicatat.');
    }

    public function closeNonConformance(QcNonConformance $nonConformance)
    {
        abort_if($nonConformance->store_id != session('selected_store'), 403);

        $nonConformance->update([
            'status'      => 'closed',
            'closed_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Non-Conformance ditutup.');
    }

    // ── Dashboard ─────────────────────────────────────────
    public function dashboard()
    {
        $storeId = session('selected_store');
        $thisMonth = now()->startOfMonth();

        $totalInspections = QcInspection::where('store_id', $storeId)->where('inspection_date', '>=', $thisMonth)->count();
        $passCount = QcInspection::where('store_id', $storeId)->where('inspection_date', '>=', $thisMonth)->where('result', 'pass')->count();
        $failCount = QcInspection::where('store_id', $storeId)->where('inspection_date', '>=', $thisMonth)->where('result', 'fail')->count();
        $openNc = QcNonConformance::where('store_id', $storeId)->where('status', '!=', 'closed')->count();

        $recentInspections = QcInspection::where('store_id', $storeId)
            ->with(['standard', 'inspector'])
            ->latest('inspection_date')
            ->take(10)
            ->get();

        return view('handai-manager.operational.quality-control.dashboard', compact(
            'totalInspections', 'passCount', 'failCount', 'openNc', 'recentInspections'
        ));
    }
}
