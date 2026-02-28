<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reseller;
use App\Models\ResellerStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\{User,  Role,Store};

use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;
class ResellerController extends Controller
{

    
    public function index(Request $request)
    {

        $storeId = session('selected_store')? session('selected_store'):session('selected_store_'); // toko aktif
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
    
        $search = $request->input('search');
    
        $resellersQuery = Reseller::whereHas('stores', function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })
        ->with(['resellerStores' => function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        }])
        ->withCount(['resellerStores as total_sold' => function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        }]);
    
        if ($search) {
            $resellersQuery->where(function ($query) use ($search) {
                if (preg_match('/RSL-(\d+)/i', $search, $matches)) {
                    $id = (int) $matches[1];
                    $query->orWhere('id', $id);
                }
    
                $query->orWhere('name', 'like', "%$search%")
                      ->orWhere('code', 'like', "%$search%");
            });
        }
    
        $resellers = $resellersQuery->paginate(10)->appends($request->query());
    
        // Untuk dropdown form "Tambah Reseller ke Toko"
        $allResellers = Reseller::whereHas('stores', function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })->get();
        // dd($allResellers,$resellers,Reseller::all(),$storeId);
        return view('handai-manager.marketing.resellers.index', compact(
            'resellers',
            'allResellers',
            'selected_store'
        ));
    }
    
    

    public function create()
{
    $stores = auth()->user()->accessibleStores();; // hanya toko yang dimiliki user manager
    $allResellers = Reseller::all(); // untuk dropdown "pilih reseller lama"
    $selected_store_id = session('selected_store');
     // 💡 pakai method tadi
    $selected_store = $selected_store_id ? \App\Models\Store::find($selected_store_id) : null;
    return view('handai-manager.marketing.resellers.create', compact('stores', 'allResellers','selected_store'));
}
    


    public function attach(Request $request)
{
    $storeId = session('selected_store');
    ResellerStore::firstOrCreate([
        'store_id' => $storeId,
        'reseller_id' => $request->reseller_id
    ]);
    return view('handai-manager.marketing.resellers.create');
//     return back()->with('success', 'Reseller berhasil ditambahkan ke toko.');
// }
}

public function store(Request $request)
{
    try {
        
        $mode = $request->input('mode');

                $rules = [
                    'store_id' => 'required|exists:store,id',
                    'mode' => 'required|in:lama,baru',
                ];

                if ($mode === 'lama') {
                    $rules['reseller_id'] = 'required|exists:resellers,id';
                } elseif ($mode === 'baru') {
                    $rules['new_name'] = 'required|string|max:255';
                    $rules['new_email'] = 'required|email|unique:users,email';
                    $rules['new_contact_number'] = 'required|string|max:20|unique:resellers,phone';
                    $rules['new_password'] = 'required|string|min:6';
                }

                $request->validate($rules);






        // CASE 1: Attach reseller lama
        if ($mode === 'lama') {
            $resellerId = $request->reseller_id;
        
            $exists = DB::table('reseller_store')
                ->where('reseller_id', $resellerId)
                ->where('store_id', $request->store_id)
                ->exists();
        
            if ($exists) {
                return back()->withErrors(['reseller_id' => 'Reseller ini sudah terhubung ke toko tersebut.'])->withInput();
            }
        }
        

        // CASE 2: Buat reseller baru
        elseif ($mode === 'baru') {
            $user = User::create([
                'name' => $request->new_name,
                'email' => $request->new_email,      
                'password' => Hash::make($request->new_password),
            ]);
        
            $reseller = Reseller::create([
                'user_id' => $user->id,
                'name' => $request->new_name,
                'contact_number' => $request->new_contact_number,
                'code' => 'RS-' . strtoupper(Str::random(6)),
            ]);
        
            $resellerId = $reseller->id;
        
            $resellerRole = Role::where('name', 'reseller')->first();
            if ($resellerRole) {
                $user->roles()->attach($resellerRole->id);
            }
        }
        
        
        // Tidak pilih atau isi apapun
        else {
            return back()->withErrors(['error'=> 'Harap pilih reseller lama atau isi data untuk reseller baru.'])->withInput();
        }
       
        DB::table('reseller_store')->insert([
            'reseller_id' => $resellerId,
            'store_id' => $request->store_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
      
        return redirect()->route('manager.marketing.resellers.index')->with('success', 'Reseller berhasil ditambahkan ke toko!');
    } catch (ValidationException $e) {
        return back()->withErrors($e->validator->errors())->withInput();
    } catch (\Throwable $e) {
        Log::error('Gagal menambahkan reseller: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
    }
}
public function attachForm()
{
    $stores = auth()->user()->stores; // atau filter sesuai peran
    $allResellers = Reseller::with('users')->get(); // sesuaikan query jika perlu

    return view('handai-manager.marketing.resellers.attach', compact('stores', 'allResellers'));
}






}
