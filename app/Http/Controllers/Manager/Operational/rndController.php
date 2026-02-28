<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RNDHistory;
use App\Models\RNDStockUsage;
use App\Models\Unit;
use App\Models\Employee;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class rndController extends Controller
{
    public function index(Request $request)
{
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

    // Base query untuk filtering tanggal & store
    $query = RNDHistory::with(['pic', 'stockUsages.stock.unit'])
        ->where('store_id', $selected_store_id);

    if ($request->filled('from')) {
        $query->whereDate('rnd_date', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $query->whereDate('rnd_date', '<=', $request->to);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('rnd_name', 'like', "%{$search}%")
              ->orWhere('deskripsi', 'like', "%{$search}%");
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Data utama yang ditampilkan
    $rndHistories = $query->orderByDesc('rnd_date')->paginate(10);

    // Summary stats
    $rndStats = RNDHistory::where('store_id', $selected_store_id)
        ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

    // Project yang sudah ready
    $readyProjects = RNDHistory::with(['stockUsages.stock.unit'])
        ->where('progress', 'Ready')
        ->where('status', 'approved')
        ->where('store_id', $selected_store_id)
        ->get();

    return view('handai-manager.operational.rnd', compact(
        'selected_store',
        'readyProjects',
        'rndHistories',
        'rndStats'
    ));
}



    public function create(Request $request)
    {
        $selected_store_id = session('selected_store');
        $employees = Employee::all()->where('store_id', $selected_store_id);
        $units = Unit::all();
        $stocks = Stock::where('store_id', $selected_store_id)->with('unit')->get()->values();

      
        $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;

        return view('handai-manager.operational.create-rnd', compact('units','stocks','employees','selected_store'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rnd_name' => 'required|string|max:255',
            'rnd_date' => 'required|date',
            'pic_id' => 'required|exists:employee,id',
            'description' => 'nullable|string',
            'ingredients.*.stock_id' => 'nullable|exists:stock,id',
            'ingredients.*.manual_name' => 'nullable|string|required_if:ingredients.*.stock_id,manual',
            'ingredients.*.quantity_used' => 'required|numeric|min:0.01',
            'ingredients.*.unit_id' => 'required|exists:units,id',
            'ingredients.*.cost' => 'required|decimal|min:0.01',
        ]);
        
        DB::beginTransaction();
        try {
            //dd($request->pic_id,$request->rnd_date,$request->description,$request);
            $rnd = RNDHistory::create([
                'rnd_name' => $request->rnd_name,
                'rnd_date' => $request->rnd_date,
                'pic_id' => $request->pic_id,
                'deskripsi' => $request->description,
                'store_id'=>session('selected_store'),
            ]);
           

            foreach ($request->rnd_ingredients as $ingredient) {
                $stockId = $ingredient['stock_id'];
                $manualName = $ingredient['manual_name'] ?? null;
                $unitId = $ingredient['unit_id'];
                $qty = $ingredient['quantity_used'];
                $cost = $ingredient['cost'];
                RNDStockUsage::create([
                    'rnd_id' => $rnd->id,
                    'stock_id' => $stockId === 'manual' ? null : $stockId,
                    'manual_name' => $stockId === 'manual' ? $manualName : null,
                    'unit_id' => $unitId,
                    'quantity_used' => $qty,
                    'cost'=> $cost,
                ]);
            }

            DB::commit();
            return redirect()->route('manager.operational.rnd')->with('success', 'R&D berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function fulfillApprovedRND($rndId)
{
    $usages = RNDStockUsage::where('rnd_id', $rndId)
                ->where('status', 'approved')
                ->whereNull('stock_id')
                ->get();

    foreach ($usages as $usage) {
        $stock = Stock::create([
            'name' => $usage->manual_name,
            'unit_id' => $usage->unit_id,
            'unit_qty' => 0,
            'store_id' => session('selected_store'),
            'expired_duration' => 30,
        ]);

        $usage->stock_id = $stock->id;
        $usage->save();
    }

    return back()->with('success', 'Bahan manual berhasil dimasukkan ke stok!');
}

public function destroy($id)
{
    DB::beginTransaction();

    try {
        $rnd = RNDHistory::with('stockUsages')->findOrFail($id);

        // Hapus terlebih dahulu semua stockUsages terkait
        foreach ($rnd->stockUsages as $usage) {
            $usage->delete();
        }

        // Lalu hapus RND project
        $rnd->delete();

        DB::commit();

        return redirect()->back()->with('success', 'Proyek R&D dan semua data terkait berhasil dihapus.');
    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->back()->withErrors(['error' => 'Gagal menghapus proyek R&D: ' . $e->getMessage()]);
    }
}

}
