<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Reseller;
use App\Models\CustomerStore;

class CustomerStoreSelectorController extends Controller
{
    public function index(Request $request)
    {
        $resellerCode = $request->query('reseller') ?? session('reseller_code');

        $reseller = Reseller::where('code', $resellerCode)->firstOrFail();

        // Simpan reseller ke session
        session([
            'reseller_id' => $reseller->id,
            'reseller_code' => $reseller->code,
        ]);

        $stores = $reseller->stores;

        return view('customer-order.select-store', compact('stores', 'resellerCode'));
    }

    public function setStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:store,id',
        ]);

        $storeId = $request->store_id;

        // Simpan ke session
        session([
            'store_id' => $storeId,
            'selected_store' => $storeId,
        ]);

        // Cek reseller ulang (optional tapi aman)
        if ($request->has('reseller_code')) {
            $reseller = Reseller::where('code', $request->reseller_code)->first();
            if ($reseller) {
                session([
                    'reseller_id' => $reseller->id,
                    'reseller_code' => $reseller->code,
                ]);
            }
        }

        // ✅ Cek jika customer sedang login
        if (session()->has('customer_id')) {
            $customerId = session('customer_id');

            // Cek apakah relasi customer_store sudah ada
            $relation = CustomerStore::where('customer_id', $customerId)
                ->where('store_id', $storeId)
                ->first();

            // Jika belum, buat relasi
            if (!$relation) {
                $relation = CustomerStore::create([
                    'customer_id' => $customerId,
                    'store_id' => $storeId,
                    'total_ordered_qty' => 0,
                    'average_ordered_qty' => 0,
                    'total_orders' => 0,
                ]);
            }

            // Simpan relasi ke session
            session(['customer_store_id' => $relation->id]);
        }

        return redirect()->route('customerOrder.form');
    }
}
