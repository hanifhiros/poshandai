<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class KasirController extends Controller
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

        return view('handai-kasir.outlet', compact('stores'));
    }

    public function setStore(Request $request): RedirectResponse
    {
        $store_id = $request->input('store_id');

        if ($store_id) {
            Session::put('selected_store', $store_id);
        }

        return redirect()->route('kasir.dashboard');
    }

    public function showInvoice(int $id): View
    {
        $order = Order::with('customer')->findOrFail($id);
        $items = DB::table('invoice')
            ->join('products', 'products.id', '=', 'invoice.product_id')
            ->where('invoice.order_id', $id)
            ->select('products.name', 'invoice.quantity_bought')
            ->get();

        return view('handai-kasir.invoice.show', compact('order', 'items'));
    }
}

