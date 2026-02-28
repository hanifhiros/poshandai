<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
class StoreController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stores = Store::where('owner_id', $user->id)->get();

        return view('handai-pos.outlet', compact('stores'));
    }

    public function setStore(Request $request)
    {
        $store_id = $request->input('store_id');

        if ($store_id) {
            Session::put('selected_store', $store_id);
            Log::info('Store selected and saved in session:', ['selected_store' => session('selected_store')]);
        } else {
            Log::warning('No store selected.');
        }

        return redirect()->route('pos.startorder');
    }

}
