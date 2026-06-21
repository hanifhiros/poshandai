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
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string',
        ]);

        try {
            $store = new Store();
            $store->store_name = $request->store_name;
            $store->store_address = $request->store_address;
            $store->owner_id = Auth::id(); 
            
            $store->save();

        return redirect()->route('superadmin.store.index')
            ->with('success', 'Cabang Toko baru berhasil didaftarkan!');
            
    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
    }
}

public function edit($id)
{
    $store = Store::findOrFail($id);
    return view('superadmin.stores.edit', compact('store'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'store_name' => 'required|string|max:255',
        'store_address' => 'required|string',
    ]);

    try {
        $store = Store::findOrFail($id);
        $store->store_name = $request->store_name;
        $store->store_address = $request->store_address;
        $store->save();

        return redirect()->route('superadmin.store.index')
            ->with('success', 'Data toko berhasil diperbarui!');
    } catch (\Exception $e) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Gagal memperbarui database: ' . $e->getMessage()]);
    }
}

public function destroy($id)
{
    try {
        $store = Store::findOrFail($id);
        $store->delete();

        return redirect()->route('superadmin.store.index')
            ->with('success', 'Cabang toko berhasil dihapus!');
    } catch (\Exception $e) {
        return redirect()->route('superadmin.store.index')
            ->withErrors(['error' => 'Gagal menghapus toko: ' . $e->getMessage()]);
    }
}

}
