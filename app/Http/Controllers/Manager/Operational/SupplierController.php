<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        $query = Supplier::where('store_id', $storeId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers = $query->withCount('stockBatches')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Summary stats
        $totalSuppliers = Supplier::where('store_id', $storeId)->count();
        $activeSuppliers = Supplier::where('store_id', $storeId)->where('is_active', true)->count();
        $totalPurchases = StockBatch::where('store_id', $storeId)->sum('cost');
        $outstandingDebt = StockBatch::where('store_id', $storeId)
            ->where('payment_method', 'hutang')
            ->whereNull('paid_at')
            ->sum('cost');

        return view('handai-manager.operational.suppliers.index', compact(
            'selected_store', 'suppliers', 'totalSuppliers', 'activeSuppliers',
            'totalPurchases', 'outstandingDebt'
        ));
    }

    public function create()
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        return view('handai-manager.operational.suppliers.create', compact('selected_store'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string|max:50',
        ]);

        Supplier::create([
            'store_id'       => session('selected_store'),
            'name'           => $request->name,
            'contact_person' => $request->contact_person,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'address'        => $request->address,
            'city'           => $request->city,
            'payment_terms'  => $request->payment_terms ?? 'COD',
            'bank_name'      => $request->bank_name,
            'bank_account'   => $request->bank_account,
            'notes'          => $request->notes,
            'is_active'      => true,
        ]);

        return redirect()->route('manager.operational.suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        $storeId = session('selected_store');
        abort_if($supplier->store_id != $storeId, 403, 'Unauthorized access.');
        $selected_store = $storeId ? Store::find($storeId) : null;

        return view('handai-manager.operational.suppliers.edit', compact('selected_store', 'supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        abort_if($supplier->store_id != session('selected_store'), 403, 'Unauthorized access.');

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $supplier->update($request->only([
            'name', 'contact_person', 'phone', 'email', 'address',
            'city', 'payment_terms', 'bank_name', 'bank_account', 'notes', 'is_active'
        ]));

        return redirect()->route('manager.operational.suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        abort_if($supplier->store_id != session('selected_store'), 403, 'Unauthorized access.');

        $supplier->delete();

        return redirect()->route('manager.operational.suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
