<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $stores = Store::where('owner_id', $user->id)->get();

        return view('handai-pos.outlet', compact('stores'));
    }

    public function setStore(Request $request): RedirectResponse
    {
        $store_id = $request->input('store_id');

        if ($store_id) {
            Session::put('selected_store', $store_id);
        }

        return redirect()->route('pos.startorder');
    }
}

