<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function logout(Request $request)
{
    if (Auth::check()) {
        Auth::logout();
        return redirect('/login'); // redirect untuk user kasir/superadmin
    }

    // Ambil reseller_code dan store_id dari session sebelum forget
    $reseller = session('reseller_code');
    $storeId = session('store_id');

    if (session('customer_id') || session('customer_guest')) {
        session()->forget([
            'customer_id',
            'customer_name',
            'is_guest',
            'customer_guest',
            'store_id',
            'selected_store',
            'reseller_id',
            'reseller_code',
        ]);

        if ($reseller) {
            return redirect()->route('customerOrder.loginForm', ['reseller' => $reseller]);
        } elseif ($storeId) {
            return redirect()->route('customerOrder.loginForm', ['store_id' => $storeId]);
        } else {
            return redirect()->route('customerOrder.loginForm');
        }
    }

    return redirect('/'); // fallback
}

}
