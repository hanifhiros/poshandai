<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CekRole
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Session::has('user_role')) {
            Log::warning('CekRole: Role tidak ditemukan di session.');
            return redirect('/')->withErrors(['role' => 'Silakan pilih role terlebih dahulu.']);
        }

        $userRole = Session::get('user_role');

        Log::info('CekRole: Role ditemukan', ['role' => $userRole]);

        // Superadmin boleh akses semua halaman
        if ($userRole === 'Superadmin') {
            return $next($request);
        }

        // Jika tidak ada role spesifik yang diminta, izinkan saja
        if (empty($roles)) return $next($request);

        // Jika role user tidak sesuai (gunakan str_starts_with agar cocok dengan login flow)
        $allowed = false;
        foreach ($roles as $role) {
            if ($userRole === $role || str_starts_with($userRole, $role)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            Log::error("CekRole: Role '$userRole' tidak diizinkan. Harus salah satu dari: " . implode(', ', $roles));
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
