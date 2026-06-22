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
        //    Support both: column `role` on users table AND pivot `role_user_store`
        if ($user->role === 'Superadmin' || $user->hasRole('Superadmin')) {
            return $next($request);
        }

        // 2. Check role dari session (set saat login)
        $sessionRole = Session::get('user_role');
        if ($sessionRole && in_array($sessionRole, $roles)) {
            return $next($request);
        }

        // 3. Check role dari kolom `role` di tabel users
        if ($user->role && in_array($user->role, $roles)) {
            return $next($request);
        }

        // 4. Fallback: check role dari pivot table `role_user_store`
        $userRoleNames = $user->roles->pluck('name')->toArray();
        foreach ($roles as $requiredRole) {
            if (in_array($requiredRole, $userRoleNames)) {
                return $next($request);
            }
        }

        abort(403, 'UNAUTHORIZED - Anda tidak memiliki akses ke halaman ini.');
    }
}
