<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SimulateController extends Controller
{
    // Menampilkan halaman daftar toko untuk disimulasi
    public function index()
    {
        return view('superadmin.simulate.index');
    }

    // Memproses saat Superadmin menekan tombol masuk ke cabang
    public function login(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'role' => 'required|in:POS,Manager'
        ]);

        // --- TIKET SUPERADMIN (SIMULASI) ---
        // 1. Tiket yang dibutuhkan oleh sistem Manager
        Session::put('selected_store', $request->store_id);
        Session::put('store_mode', 'multi');
        
        // 2. Tiket standar & POS (kita simpan juga untuk jaga-jaga)
        Session::put('active_store_id', $request->store_id);
        Session::put('active_role', $request->role);
        Session::put('is_simulating', true); 

        // 3. Arahkan Superadmin ke halaman yang sesuai
        if ($request->role === 'Manager') {
            return redirect('/manager/dashboard')->with('success', 'Mode Simulasi: Anda masuk sebagai Manager.');
        } else {
            return redirect('/pos/dashboard')->with('success', 'Mode Simulasi: Anda masuk sebagai POS/Kasir.');
        }
    }
}