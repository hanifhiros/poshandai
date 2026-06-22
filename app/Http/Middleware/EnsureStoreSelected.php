<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Middleware to ensure a store is selected before accessing store-scoped routes.
 * Prevents data leakage across tenants when session('selected_store') is null.
 */
class EnsureStoreSelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Superadmin who is simulating always has selected_store set by SimulateController
        // If Superadmin is NOT simulating, they shouldn't be accessing store-scoped routes
        // But if they are, allow it (they may be browsing)
        if ($user && ($user->role === 'Superadmin' || $user->hasRole('Superadmin'))) {
            // Superadmin can pass through — their queries will still be store-scoped
            // if selected_store is set (via simulate), or return empty if not
            return $next($request);
        }

        $selectedStore = Session::get('selected_store');

        if (empty($selectedStore)) {
            // Determine which store selection page to redirect to based on user role
            $userRole = Session::get('user_role', '');

            if (str_starts_with($userRole, 'Manager')) {
                return redirect()->route('manager.store')
                    ->with('warning', 'Silakan pilih toko terlebih dahulu.');
            }

            if (str_starts_with($userRole, 'POS') || str_starts_with($userRole, 'Kasir')) {
                return redirect()->route('pos.store')
                    ->with('warning', 'Silakan pilih toko terlebih dahulu.');
            }

            // Fallback: redirect to login
            return redirect()->route('login')
                ->with('warning', 'Sesi tidak valid. Silakan login kembali.');
        }

        return $next($request);
    }
}
