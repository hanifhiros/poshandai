<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        // Jika user punya store tetap (single store)
        if (!is_null($user->store_id)) {
            $store = Store::find($user->store_id);

            if ($store) {
                Session::put('selected_store', $store->id);
                Session::put('store_mode', 'single');

                return redirect()->route('manager.dashboard');
            }

            return back()->withErrors(['Toko tidak ditemukan.']);
        }

        // Ambil toko dari pivot role_user_store (multi-store)
        $stores = Store::whereIn('id', function ($query) use ($user) {
            $query->select('store_id')
                  ->from('role_user_store')
                  ->where('user_id', $user->id)
                  ->whereNotNull('store_id');
        })->get();

        // Auto-redirect jika hanya 1 store
        if ($stores->count() === 1) {
            $store = $stores->first();
            Session::put('selected_store', $store->id);
            Session::put('store_mode', 'multi');

            return redirect()->route('manager.dashboard');
        }

        // Cek apakah user punya akses multistore
        $roleUserStores = DB::table('role_user_store')
            ->where('user_id', $user->id)
            ->get();

        if ($roleUserStores->contains('is_multistore', 1)) {
            $stores = Store::where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                      ->orWhere('owner_id', $user->created_by);
            })->get();
        }

        return view('handai-manager.outlet', compact('stores'));
    }

    public function setStore(Request $request): RedirectResponse
    {
        $store_id = $request->input('store_id');

        if (!$store_id) {
            return redirect()->route('manager.store')->withErrors(['Silakan pilih toko.']);
        }

        // Verify user has access to this store
        $user = Auth::user();
        $hasAccess = DB::table('role_user_store')
            ->where('user_id', $user->id)
            ->where('store_id', $store_id)
            ->exists();

        // Also allow if user owns the store
        if (!$hasAccess) {
            $hasAccess = Store::where('id', $store_id)
                ->where(function ($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhere('owner_id', $user->created_by);
                })->exists();
        }

        if (!$hasAccess) {
            return redirect()->route('manager.store')->withErrors(['Anda tidak memiliki akses ke toko ini.']);
        }

        Session::put('selected_store', $store_id);
        Session::put('store_mode', 'multi');

        return redirect()->route('manager.dashboard');
    }
}

