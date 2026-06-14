<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class Login extends Controller
{
    /**
     * Halaman login.
     */
    public function index(): View
    {
        return view('auth.login');
    }

    /**
     * Halaman register.
     */
    public function register(): View
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi — DISABLED untuk keamanan.
     */
    public function store(Request $request): never
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Public registration is DISABLED — new accounts must be created by a Superadmin.
        abort(403, 'Registrasi publik tidak tersedia. Hubungi administrator.');
    }

    /**
     * Proses login web.
     */
    public function exe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
                'password' => 'Email atau password salah.',
            ]);
        }

        Auth::login($user);

        $loginType = $request->input('login_type', 'seller');

        // Reseller login
        if ($loginType === 'reseller') {
            return $this->handleResellerLogin($user);
        }

        // Superadmin bypass — no role selection needed
        if ($user->hasRole('Superadmin')) {
            Session::put('user_role', 'Superadmin');
            Session::put('store_id', null);
            Session::put('isMultistore', 1);
            Session::put('globar_id', $user->id);

            return redirect()->route('superadmin.dashboard');
        }

        // Regular user — must select role
        $request->validate([
            'role' => 'required',
        ], [
            'role.required' => 'Silakan pilih role login.',
        ]);

        $requestedRole = trim((string) $request->input('role', ''));
        $availableRoles = $user->roles->pluck('name')->values()->all();

        // If the hidden field is empty, fall back to the only role the user has.
        if ($requestedRole === '') {
            $role = $user->roles->count() === 1 ? $user->roles->first() : null;
        } else {
            $normalizedRequestedRole = Str::lower($requestedRole);

            $role = $user->roles->first(function ($r) use ($normalizedRequestedRole) {
                $normalizedRoleName = Str::lower(trim($r->name));

                return $normalizedRoleName === $normalizedRequestedRole
                    || Str::startsWith($normalizedRoleName, $normalizedRequestedRole)
                    || Str::startsWith($normalizedRequestedRole, $normalizedRoleName);
            });
        }

        if (!$role) {
            Auth::logout();

            return back()->withErrors([
                'role' => 'Role tidak sesuai dengan akun ini.',
            ]);
        }

        Session::put('user_role', $role->name);
        Session::put('store_id', $role->pivot->store_id);
        Session::put('isMultistore', $role->pivot->is_multistore);

        return match (true) {
            str_starts_with($role->name, 'Manager')  => redirect()->route('manager.store'),
            str_starts_with($role->name, 'POS')      => redirect()->route('pos.store'),
            str_starts_with($role->name, 'Kasir')    => redirect()->route('kasir.store'),
            str_starts_with($role->name, 'Reseller') => redirect()->route('reseller.dashboard'),
            default                                   => redirect()->route('home'),
        };
    }

    /**
     * Proses login mobile (API).
     */
    public function exemobile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = null;
        $stores = collect();
        $user = null;
        $isSuperadmin = false;

        // Check customer first
        $customer = Customer::where('email', $request->email)->first();

        if ($customer) {
            $user = $customer;
            $role = 'customer';
        } else {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                $isSuperadmin = $user->created_by === null;
                $role = 'internal';
                $stores = $this->getUserStores($user, $isSuperadmin);
            }
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password.',
            ], 401);
        }

        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'is_superadmin' => $isSuperadmin,
                'stores' => $stores,
                'contact_number' => $user->contact_number ?? null,
            ],
        ]);
    }

    /**
     * Logout dan flush session.
     */
    public function logout(): RedirectResponse
    {
        Session::flush();
        Auth::logout();

        return redirect()->route('login');
    }

    // ── Private Helpers ─────────────────────────────────────

    /**
     * Handle reseller-specific login flow.
     */
    private function handleResellerLogin(User $user): RedirectResponse
    {
        $role = $user->roles->first(
            fn($r) => Str::startsWith(Str::lower($r->name), 'reseller')
        );

        if (!$role) {
            Auth::logout();

            return back()->withErrors(['role' => 'Akun ini bukan reseller.']);
        }

        Session::put('user_role', $role->name);
        Session::put('store_id', $role->pivot->store_id ?? null);
        Session::put('isMultistore', $role->pivot->is_multistore ?? false);

        return redirect()->route('reseller.dashboard');
    }

    /**
     * Get stores accessible by user based on role.
     */
    private function getUserStores(User $user, bool $isSuperadmin)
    {
        if ($isSuperadmin) {
            return DB::table('store')
                ->where('owner_id', $user->id)
                ->select('id', 'store_name')
                ->get();
        }

        return DB::table('store')
            ->join('role_user_store', 'store.id', '=', 'role_user_store.store_id')
            ->where('role_user_store.user_id', $user->id)
            ->select('store.id', 'store.store_name')
            ->get();
    }
}

