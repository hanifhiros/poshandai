<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        // Hanya tampilkan toko yang dimiliki oleh user yang sedang login
        $stores = Store::where('owner_id', Auth::id())->paginate(7);
        return view('superadmin.stores.index', compact('stores'));
    }
    public function create()
{
    $stores = Store::where('owner_id', Auth::id())->get();
    return view('superadmin.stores.create');
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:500',
    ]);

    Store::create([
        'store_name' => $request->name,
        'store_address' => $request->address,
        'owner_id' => Auth::id(), // Pemilik toko = user yang sedang login (Superadmin)
    ]);

    return redirect()->route('superadmin.store.index')->with('success', 'Toko berhasil ditambahkan.');
}
public function destroy($id)
{
    try {
        DB::beginTransaction();

        $store = Store::findOrFail($id);
        $store->delete();

        DB::commit();
        return redirect()->route('superadmin.store.index')->with('success', 'Toko berhasil dihapus.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('superadmin.store.index')->with('error', 'Gagal menghapus toko.');
    }
}

}
