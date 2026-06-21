<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        // Tampilkan semua user kecuali Superadmin yang sedang login
        $users = User::where('id', '!=', Auth::id())->paginate(10);
        return view('superadmin.accounts.index', compact('users'));
    }

    public function create()
    {
        // Data toko sudah dipanggil langsung dari Blade (App\Models\Store::all())
        return view('superadmin.accounts.create');
    }
    
    public function store(Request $request)
    {
        // 1. Validasi data dari form yang baru
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|string',
            'is_multistore' => 'required|in:0,1',
            'store_id' => 'required_if:is_multistore,0'
        ]);
    
        DB::beginTransaction();
    
        try {
            // 2. Simpan user baru (langsung assign kolom 'role' agar bisa login)
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = $request->role; 
            $user->created_by = Auth::id();
            $user->save();
    
            // 3. Pastikan role ada di database (Otomatis dibuat jika seeder Anda masih kosong)
            $roleModel = Role::firstOrCreate(['name' => $request->role]);
    
            // 4. Daftarkan hak akses toko ke tabel perantara (Untuk CheckRole Middleware)
            DB::table('role_user_store')->insert([
                'user_id' => $user->id,
                'role_id' => $roleModel->id,
                'store_id' => $request->is_multistore == '1' ? null : $request->store_id,
                'is_multistore' => $request->is_multistore == '1' ? true : false,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            DB::commit();
    
            return redirect()->route('superadmin.account.index')
                ->with('success', 'Akun Pegawai berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('AccountController store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan akun: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $assignedRole = DB::table('role_user_store')->where('user_id', $user->id)->first();
        
        return view('superadmin.accounts.edit', compact('user', 'assignedRole'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|string',
            'is_multistore' => 'required|in:0,1',
            'store_id' => 'required_if:is_multistore,0'
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role; 
            
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            // Hapus hak akses lama
            DB::table('role_user_store')->where('user_id', $id)->delete();

            // Masukkan hak akses baru
            $roleModel = Role::firstOrCreate(['name' => $request->role]);

            DB::table('role_user_store')->insert([
                'user_id' => $user->id,
                'role_id' => $roleModel->id,
                'store_id' => $request->is_multistore == '1' ? null : $request->store_id,
                'is_multistore' => $request->is_multistore == '1' ? true : false,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('superadmin.account.index')->with('success', 'Akun berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui akun: ' . $e->getMessage());
        }
    }

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
            DB::table('role_user_store')->where('user_id', $id)->delete();

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
}