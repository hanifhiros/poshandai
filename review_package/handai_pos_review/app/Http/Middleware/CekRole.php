<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
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
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Silakan pilih role terlebih dahulu.', 'redirect' => url('/')], 403);
            }
            return redirect('/')->withErrors(['role' => 'Silakan pilih role terlebih dahulu.']);
        }

        $userRole = Session::get('user_role');

        // Superadmin boleh akses semua halaman
        if ($userRole === 'Superadmin') {
            return $next($request);
        }

        // ── Store-scoping: Validate user has access to selected store ──
        $selectedStore = Session::get('selected_store') ?? Session::get('store_id');
        if ($selectedStore && Auth::check()) {
            $user = Auth::user();
            $hasAccess = \App\Models\RoleUserStore::where('user_id', $user->id)
                ->where('store_id', $selectedStore)
                ->exists();

            // Also allow if user owns the store
            if (!$hasAccess) {
                $hasAccess = \App\Models\Store::where('id', $selectedStore)
                    ->where('owner_id', $user->id)
                    ->exists();
            }

            if (!$hasAccess) {
                Log::warning('CekRole: User tidak punya akses ke store', [
                    'user_id' => $user->id,
                    'store_id' => $selectedStore,
                ]);
                abort(403, 'Anda tidak memiliki akses ke store ini.');
            }
        }

        // Jika tidak ada role spesifik yang diminta, izinkan saja
        if (empty($roles)) return $next($request);

        // Jika role user tidak sesuai (strict equality check)
        $allowed = false;
        foreach ($roles as $role) {
            if ($userRole === $role) {
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
