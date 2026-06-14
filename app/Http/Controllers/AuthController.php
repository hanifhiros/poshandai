<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function showLoginPage()
    {
        // Ubah menjadi auth.login sesuai perbaikan kita sebelumnya
        return view("auth.login");
    }

    public function login(Request $request)
    {
        // 1. Validasi inputan dari form
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required'
        ]);

        // 2. Cek ke database: Apakah Email, Password, DAN Role-nya cocok?
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role' => $request->role])) {

            // Regenerasi session untuk keamanan
            $request->session()->regenerate();

            // 3. Arahkan ke dashboard masing-masing
            if ($request->role === 'Superadmin') {
                return redirect()->route('superadmin.dashboard'); 
            } elseif ($request->role === 'Manager') {
                return redirect()->route('manager.dashboard'); // Pastikan route ini ada di web_manager.php
            } elseif ($request->role === 'POS') {
                return redirect()->route('pos.dashboard'); // Pastikan route ini ada di web_pos.php
            }
        }

        // 4. Jika salah satu (Email / Password / Role) tidak cocok, tendang kembali!
        return back()->withErrors([
            'email' => 'Email, Password, atau Role tidak sesuai.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Logout bawaan Laravel yang lebih aman daripada sekadar Session::forget
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully');
    }
}