<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
class StartOrder extends Controller
{
    public function index()
    {
        session()->forget(['cart', 'promo_code', 'promo_discount']);
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        return view('handai-pos.startorder', compact('selected_store'));
    }
}
