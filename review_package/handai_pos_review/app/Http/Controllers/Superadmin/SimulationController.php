<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    public function index()
    {
        $stores = Store::with(['users.roles' => function ($query) {
            $query->whereIn('name', ['Manager', 'Kasir', 'POS']);
        }])->where('owner_id', Auth::id())->paginate(3);

        return view('superadmin.simulation.index', compact('stores'));
    }
    public function simulate(Request $request, $storeId, $roleName)
    {
        // Validate role name against allowed roles
        $allowedRoles = Role::pluck('name')->toArray();
        if (!in_array($roleName, $allowedRoles)) {
            abort(400, 'Role tidak valid.');
        }

        // Validate store belongs to this user
        $store = Store::where('id', $storeId)->where('owner_id', Auth::id())->first();
        if (!$store) {
            abort(403, 'Anda tidak memiliki akses ke store ini.');
        }

        // Simpan ke session
        session()->put('store_id', $storeId);
        session()->put('selected_store', $storeId);
        session()->put('user_role', $roleName);

        return redirect()->route('dashboard');
    }

}