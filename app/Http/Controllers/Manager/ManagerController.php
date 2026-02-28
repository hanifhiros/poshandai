<?php

namespace App\Http\Controllers\Manager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index()
{
    $user = Auth::user();

    // ✅ Jika user punya store tetap (single store)
    if (!is_null($user->store_id)) {
        $store = Store::find($user->store_id);

        if ($store) {
            Session::put('selected_store', $store->id);
            Session::put('store_mode', 'single');
            Log::info('Single store auto-selected', ['store_id' => $store->id]);

            return redirect()->route('manager.dashboard');
        }

        return back()->withErrors(['Toko tidak ditemukan.']);
    }
    
    // ✅ Ambil toko dari pivot role_user_store (multi-store)
    $stores = Store::whereIn('id', function ($query) use ($user) {
        $query->select('store_id')
              ->from('role_user_store')
              ->where('user_id', $user->id)
              ->whereNotNull('store_id');
    })->get();

    // ✅ Auto-redirect jika hanya 1 store
    if ($stores->count() === 1) {
        $store = $stores->first();
        Session::put('selected_store', $store->id);
        Session::put('store_mode', 'multi');
        Log::info('Multi-store: only one store, auto-selected', ['store_id' => $store->id]);

        return redirect()->route('manager.dashboard');
    }

    // ✅ Tampilkan halaman pilih toko jika lebih dari satu

    // Kalau lebih dari satu toko, tampilkan halaman pilihan toko
    $roleUserStores = \DB::table('role_user_store')
    ->where('user_id', $user->id)
    ->get();
    $hasMultiStore = $roleUserStores->contains('is_multistore', 1);

    if ($hasMultiStore) {
        // Ambil semua store yang dibuat atau dimiliki oleh user
        $stores = \App\Models\Store::where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                  ->orWhere('owner_id', $user->created_by);
        })->get();
    }
    return view('handai-manager.outlet', compact('stores'));
}




    public function setStore(Request $request)
    {
        $store_id = $request->input('store_id');

        if ($store_id) {
            // Verify user has access to this store
            $user = Auth::user();
            $hasAccess = DB::table('role_user_store')
                ->where('user_id', $user->id)
                ->where('store_id', $store_id)
                ->exists();

            // Also allow if user owns the store or it was created by the user
            if (!$hasAccess) {
                $hasAccess = Store::where('id', $store_id)
                    ->where(function ($q) use ($user) {
                        $q->where('owner_id', $user->id)
                          ->orWhere('owner_id', $user->created_by);
                    })->exists();
            }

            if (!$hasAccess) {
                Log::warning('Unauthorized store access attempt', ['user_id' => $user->id, 'store_id' => $store_id]);
                return redirect()->route('manager.store')->withErrors(['Anda tidak memiliki akses ke toko ini.']);
            }

            Session::put('selected_store', $store_id);
            Session::put('store_mode', 'multi'); 
            Log::info('Store selected and saved in session:', ['selected_store' => session('selected_store')]);
        } else {
            Log::warning('No store selected.');
        }

        return redirect()->route('manager.dashboard');
    }
}
