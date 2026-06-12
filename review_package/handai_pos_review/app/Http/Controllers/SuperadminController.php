<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperadminController extends Controller
{
    
public function destroy($id)
{
    DB::beginTransaction();

    try {
        $user = User::findOrFail($id);

        // Cegah hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.accounts.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Hapus relasi dari pivot role_user_store
        $user->roles()->detach();

        // Hapus user
        $user->delete();

        DB::commit();

        return redirect()->route('superadmin.accounts.index')
            ->with('success', 'Akun berhasil dihapus.');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->route('superadmin.accounts.index')
            ->with('error', 'Terjadi kesalahan saat menghapus akun: ' . $e->getMessage());
    }
}

    public function index()
    {
        return view('superadmin.dashboard');
    }

    public function accountIndex()
    {
        $users = User::with(['roles', 'stores'])
            ->where('created_by', Auth::id())
            ->get();

        return view('superadmin.accounts.index', compact('users'));
    }

    public function accountCreate()
    {
        $roles = Role::all();
        $stores = Store::where('owner_id', Auth::id())->get();

        return view('superadmin.accounts.create', compact('roles', 'stores'));
    }

    public function accountStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'store_id' => 'nullable|exists:store,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'created_by' => Auth::id(),
        ]);

        $user->roles()->attach($request->role_id, [
            'store_id' => $request->store_id
        ]);

        return redirect()->route('superadmin.accounts.index')->with('success', 'Akun berhasil dibuat.');
    }
}
