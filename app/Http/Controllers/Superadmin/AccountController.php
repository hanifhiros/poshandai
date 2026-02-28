<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class AccountController extends Controller
{
    public function destroy($id)
{
    DB::beginTransaction();

    try {
        $user = User::findOrFail($id);

        // Cegah hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.account.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Hapus relasi dari pivot role_user_store
        $user->roles()->detach();

        // Hapus user
        $user->delete();

        DB::commit();

        return redirect()->route('superadmin.account.index')
            ->with('success', 'Akun berhasil dihapus.');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()->route('superadmin.account.index')
            ->with('error', 'Terjadi kesalahan saat menghapus akun: ' . $e->getMessage());
    }
}
    public function index()
    {
        $users = User::where('created_by', Auth::id())->paginate(7);
        return view('superadmin.accounts.index', compact('users'));
    }

    public function create()
    {
        $stores = Store::where('owner_id', auth()->id())->get();
    
        // Ambil superadmin secara terpisah
        $superadmin = Role::where('name', 'Superadmin')->first();
    
        // Hanya ambil role root (Fitur utama: Manager, POS, Kasir)
        $roles = Role::with('children.children')
            ->whereNull('parent_id') // ⬅ tambahkan ini!
            ->where('name', '!=', 'Superadmin')
            ->get();
        return view('superadmin.accounts.create', compact('roles', 'stores', 'superadmin'));
    }
    
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'store_mode' => 'required|in:multi,manual',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:store,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Buat user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_by' => Auth::id(),
            ]);
    
            $roleUserStore = [];
    
            if ($request->store_mode === 'multi') {
                // Satu role berlaku untuk semua toko (is_multistore = true, store_id = null)
                foreach ($request->roles as $roleId) {
                    $roleUserStore[] = [
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'store_id' => null,
                        'is_multistore' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                // Role diberikan ke store tertentu (is_multistore = false)
                foreach ($request->roles as $roleId) {
                    foreach ($request->stores ?? [] as $storeId) {
                        $roleUserStore[] = [
                            'user_id' => $user->id,
                            'role_id' => $roleId,
                            'store_id' => $storeId,
                            'is_multistore' => false,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
    
            // Masukkan semua data ke pivot table
            DB::table('role_user_store')->insert($roleUserStore);
    
            DB::commit();
    
            return redirect()->route('superadmin.account.index')
                ->with('success', 'Akun berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('AccountController store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan akun: ' . $e->getMessage());
        }
    }

    public function edit($id)
{
    $user = User::findOrFail($id);

    // Ambil semua roles dan stores seperti di create()
    $stores = Store::where('owner_id', auth()->id())->get();
    $roles = Role::with('children.children')
        ->whereNull('parent_id')
        ->where('name', '!=', 'Superadmin')
        ->get();

    // Ambil role_id dan store_id dari user ini
    $assignedRoles = DB::table('role_user_store')
        ->where('user_id', $user->id)
        ->get();
        $storeMode = $assignedRoles->contains('is_multistore', true) ? 'multi' : 'manual';

        $assignedStores = $assignedRoles->pluck('store_id')->filter()->unique()->toArray();

    return view('superadmin.accounts.edit', compact('user', 'roles', 'stores', 'assignedRoles','storeMode','assignedStores'));
}

    public function update(Request $request, $id)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'store_mode' => 'required|in:multi,manual',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:store,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        

        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            // Hapus role lama
            DB::table('role_user_store')->where('user_id', $id)->delete();

            // Insert role baru
            $roleUserStore = [];
            if ($request->store_mode === 'multi') {
                foreach ($request->roles as $roleId) {
                    $roleUserStore[] = [
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'store_id' => null,
                        'is_multistore' => true,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                foreach ($request->roles as $roleId) {
                    foreach ($request->stores ?? [] as $storeId) {
                        $roleUserStore[] = [
                            'user_id' => $user->id,
                            'role_id' => $roleId,
                            'store_id' => $storeId,
                            'is_multistore' => false,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            DB::table('role_user_store')->insert($roleUserStore);

            DB::commit();

            return redirect()->route('superadmin.account.index')->with('success', 'Akun berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui akun: ' . $e->getMessage());
        }
    }
    

}
