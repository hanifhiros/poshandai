<?php


namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reseller;
use Illuminate\Support\Facades\Log;
use App\Models\Store;

use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ResellerRegisterController extends Controller
{
    public function showForm()
    {
        return view('public.reseller-register');
    }





    
public function submitForm(Request $request)
{

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
        'contact_number' => 'nullable|string|max:20',
    ]);

    DB::beginTransaction();
    try {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Reseller::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->contact_number,
            'code' => 'RSL-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
        ]);

        $resellerRole = Role::where('name', 'reseller')->firstOrFail();

        DB::table('role_user_store')->insert([
            'user_id' => $user->id,
            'role_id' => $resellerRole->id,
            'store_id' => null,
            'is_multistore' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::commit();
        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan login setelah diaktivasi.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Pendaftaran gagal: ' . $e->getMessage()]);
    }
}
}


