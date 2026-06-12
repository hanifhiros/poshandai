<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerStore;
use App\Models\Reseller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        
        $store_id = $request->query('store_id');
        $reseller_code = $request->query('reseller');

        if (!$store_id && !$reseller_code) {
            return abort(400, 'Store ID atau reseller code dibutuhkan.');
        }

        if ($store_id) {
            session([
                'store_id' => $store_id,
                'selected_store' => $store_id,
            ]);
        }

        if ($reseller_code) {
            session(['reseller_code' => $reseller_code]);
           
        }
        if ($reseller_code && !$store_id) {
            session(['store_id_' =>session($store_id)? $store_id:-1,'selected_store_'=>session('selected_store')]);
            session()->forget(['store_id', 'selected_store']);
           
        }
          
        return view('customer-order.login', compact('store_id', 'reseller_code'));
    }



    
    public function login(Request $request)
    {
        $request->validate([
            'contact_number' => 'required',
            'password' => 'required',
        ]);
    
        $store_id = $request->input('store_id');
        $reseller_code = $request->input('reseller_code');
    
        if (!$store_id && !$reseller_code) {
            return back()->withErrors(['store_id' => 'Store ID atau reseller code wajib diisi.']);
        }
    
        // Simpan reseller session
        if ($reseller_code) {
            session(['reseller_code' => $reseller_code]);
            $reseller = Reseller::where('code', $reseller_code)->first();
            if ($reseller) {
                session(['reseller_id' => $reseller->id]);
            }
        }
    
        // Cari customer berdasarkan contact_number
        $customer = Customer::where('contact_number', $request->contact_number)->first();
    
        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['login' => 'Nomor atau password salah.']);
        }
    
        // Simpan data customer ke session
        session([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'is_guest' => false,
        ]);
    
        // Kalau store_id belum dipilih → redirect ke selectStore
        if (!$store_id) {
            return redirect()->route('customerOrder.selectStore', ['reseller' => $reseller_code]);
        }
    
        // Cek apakah relasi customer-store sudah ada
        $relation = CustomerStore::where('customer_id', $customer->id)
                    ->where('store_id', $store_id)
                    ->first();
    
        // Kalau belum ada → buat
        if (!$relation) {
            $relation = CustomerStore::create([
                'customer_id' => $customer->id,
                'store_id' => $store_id,
                'total_ordered_qty' => 0,
                'average_ordered_qty' => 0,
                'total_orders' => 0,
            ]);
        }
    
        // Simpan store dan relasi ke session
        session([
            'store_id' => $store_id,
            'selected_store' => $store_id,
            'customer_store_id' => $relation->id,
        ]);
    
        return redirect()->route('customerOrder.form');
    }
    
    
    


    public function guestLogin(Request $request)
    {
        $store_id = $request->query('store_id');

        // ── Create an anonymous guest customer per session ──
        $guestName = 'Tamu #' . strtoupper(\Illuminate\Support\Str::random(6));
        $customer = Customer::create([
            'name' => $guestName,
            'contact_number' => 'guest_' . time() . '_' . rand(1000, 9999),
            'store_id' => $store_id ?? session('selected_store'),
            'has_ordered' => 0,
        ]);

        session([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'selected_store' => $store_id ?? session('selected_store'),
            'is_guest' => true
        ]);

        $reseller_code = $request->input('reseller_code');
        if ($reseller_code) {
            session(['reseller_code' => $reseller_code]);
            $reseller = Reseller::where('code', $reseller_code)->first();
            if ($reseller) {
                session(['reseller_id' => $reseller->id]);
            }
        }
        if (!$store_id) {
            return redirect()->route('customerOrder.selectStore', ['reseller' => $reseller_code]);
        }

        return redirect()->route('customerOrder.form', ['store_id' => $store_id]);
    }
    public function showRegisterForm(Request $request)
    {
        return view('customer-order.register');
    }

    public function register(Request $request)
    {
        try {
            // Trim inputan
            $request->merge([
                'contact_number' => trim($request->contact_number),
                'email' => trim($request->email),
                'name' => trim($request->name),
            ]);


            // Validasi input
            $request->validate([
                'name' => 'required',
                'address' => 'required',
                'email' => 'nullable|email|unique:customer,email',
                'contact_number' => 'required|unique:customer,contact_number',
                'password' => 'required|min:6',
                'gender' => 'required|in:Laki-laki,Perempuan',
                'store_id' => 'required|exists:store,id',
            ]);

            // Ambil store & buat customer
            $store = Store::findOrFail($request->store_id);

            $customer = Customer::create([
                'name' => $request->name,
                'address' => $request->address,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'store_id' => $request->store_id,
                'has_ordered' => 0,
                'created_by' => $store->owner_id,
            ]);

            // Simpan sesi login
            session([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'selected_store' => $request->store_id,
                'is_guest' => false
            ]);

            return redirect()->route('customerOrder.form', ['store_id' => $request->store_id]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mendaftar: ' . $e->getMessage()]);
        }
    }


    public function logout()
    {
        session()->forget(['customer_id', 'customer_name', 'selected_store', 'is_guest', 'customer_guest']);
        return redirect()->route('customerOrder.loginForm');
    }


}

