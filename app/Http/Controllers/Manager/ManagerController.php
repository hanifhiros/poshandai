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

        $stores = $user->accessibleStores();

        // Auto-redirect jika hanya 1 store
        if ($stores->count() === 1) {
            $store = $stores->first();
            Session::put('selected_store', $store->id);
            Session::put('store_mode', 'single');

            return redirect()->route('manager.dashboard');
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
        $stores = $user->accessibleStores();
        $hasAccess = $stores->contains('id', $store_id);

        if (!$hasAccess) {
            return redirect()->route('manager.store')->withErrors(['Anda tidak memiliki akses ke toko ini.']);
        }

        Session::put('selected_store', $store_id);
        Session::put('store_mode', 'multi');

        return redirect()->route('manager.dashboard');
    }
}

