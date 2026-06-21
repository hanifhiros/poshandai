<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 1. BYPASS KHUSUS SUPERADMIN: Bebas masuk ke mana saja!
        if ($user->role === 'Superadmin') {
            return $next($request);
        }

        // 2. Logika lama untuk mengecek role pegawai lainnya (Manager/Kasir)
        if (!in_array($user->role, $roles)) {
            abort(403, 'UNAUTHORIZED - YOU DO NOT HAVE THE REQUIRED ROLE.');
        }

        return $next($request);
    }
}
