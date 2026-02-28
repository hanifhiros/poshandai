<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use  App\Models\Customer;
use App\Models\Stock;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class Login extends Controller
{
    public function index()
    {
        return view('login');
    }
    // Tampilkan halaman form register
    public function register()
    {
        return view('auth.register');
    }

    // Proses registrasi
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // // Ambil role Superadmin
        $superadmin = Role::where('name', 'Superadmin')->first();

        // // Assign ke user (MultiStore = store_id null)
        $user->roles()->attach($superadmin->id, ['store_id' => null]);

        // // Login dan simpan role + store ke session
        Auth::login($user);
        Session::put('user_role', 'Superadmin');
        Session::put('store_id', null);
        //C:\Project\Handai_POS_System\resources\views\superadmin.blade.php\dashboard.blade.php
        return redirect()->route('superadmin.dashboard');
    }


    public function exe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            // Cek login_type: reseller vs seller
                $loginType = $request->input('login_type', 'seller');

                if ($loginType === 'reseller') {
                    // Pastikan user punya role reseller
                    $role = $user->roles->first(fn($r) => Str::startsWith(Str::lower($r->name), 'reseller'));
                    if (!$role) {
                        Auth::logout();
                        return back()->withErrors(['role' => 'Akun ini bukan reseller.']);
                    }

                    Session::put('user_role', $role->name);
                    Session::put('store_id', $role->pivot->store_id ?? null);
                    Session::put('isMultistore', $role->pivot->is_multistore ?? false);

                    return redirect()->route('reseller.dashboard');
                }
            // Cek apakah dia Superadmin dulu
            if ($user->hasRole('Superadmin')) {
                Session::put('user_role', 'Superadmin');
                Session::put('store_id', null);
                Session::put('isMultistore', 1);
                Session::put('globar_id', $user->id);
                return redirect()->route('superadmin.dashboard');
            }

            

            // Kalau bukan, cek role dari input
            $request->validate([
                'role' => 'required',
            ], [
                'role.required' => 'Silakan pilih role login.',
            ]);

            // $role = $user->roles()->where('name', $request->role)->first();
            $role = $user->roles->first(function ($r) use ($request) {
                return Str::startsWith(Str::lower($r->name), Str::lower($request->role));
            });
            // dd($role,$request->role, $user->roles()->pluck('name'),$role->pivot->is_multistore);
            if (!$role) {
                Auth::logout();
                return back()->withErrors([
                    'role' => 'Role tidak sesuai dengan akun ini.',
                ]);
            }

            Session::put('user_role', $role->name);
            Session::put('store_id', $role->pivot->store_id); // NULL = MultiStore
            Session::put('isMultistore', $role->pivot->is_multistore);
            return match (true) {
                str_starts_with($role->name, 'Manager') => redirect()->route('manager.store'),
                str_starts_with($role->name, 'POS') => redirect()->route('pos.store'),
                str_starts_with($role->name, 'Kasir') => redirect()->route('kasir.store'),
                str_starts_with($role->name, 'Reseller') => redirect()->route('reseller.dashboard'),
                default => redirect()->route('home'),
            };
            
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
            'password' => 'Email atau password salah.',
        ]);
    }

    public function exemobile(Request $request)
{
    Log::info('Login attempt:', ['email' => $request->email]);

    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|min:4',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid input.',
            'errors' => $validator->errors()
        ], 422);
    }

    $role = null;
    $stores = [];
    $user = null;
    $isSuperadmin = false;

    // Cek customer dulu
    $customer = Customer::where('email', $request->email)->first();
    if ($customer) {
        $user = $customer;
        $role = 'customer';
    } else {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $isSuperadmin = $user->created_by === null;
            $role = 'internal';

            // Kalau superadmin → ambil store yang dia buat (created_by)
            if ($isSuperadmin) {
                $stores = DB::table('store')
                    ->where('owner_id', $user->id)
                    ->select('id', 'store_name')
                    ->get();
            } else {
                // Kalau internal → ambil store dari role_user_store
                $stores = DB::table('store')
                    ->join('role_user_store', 'store.id', '=', 'role_user_store.store_id')
                    ->where('role_user_store.user_id', $user->id)
                    ->select('store.id', 'store.store_name')
                    ->get();
            }
        }
    }

    if (!$user) {
        Log::error('User not found:', ['email' => $request->email]);
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 404);
    }

    if (!Hash::check($request->password, $user->password)) {
        Log::error('Invalid password:', ['email' => $request->email]);
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid email or password.',
        ], 401);
    }

    Auth::login($user);

    Log::info('Login successful:', ['email' => $request->email]);

    return response()->json([
        'status' => 'success',
        'message' => 'Login successful.',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role, // tetap 'internal'
            'is_superadmin' => $isSuperadmin,
            'stores' => $stores,
            'contact_number' => $user->contact_number ?? null,
        ],
    ]);
}

    

    public function cust()
    {
        $customers = Customer::with('store')
            ->select('id', 'store_id', 'name', 'contact_number', 'email', 'gender')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'number' => $customer->contact_number,
                    'email' => $customer->email,
                    'gender' => $customer->gender,
                    'store' => $customer->store ? $customer->store->store_name : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect()->route('login');
    }



    public function StockByCategory(Request $request)
{
    $stockCategoryId = $request->route('stock_category_id');

    if (!$stockCategoryId) {
        return response()->json([
            'status' => 'error',
            'message' => 'stock_category_id parameter is required'
        ], 400);
    }

    $stocks = Stock::where('stock_category_id', $stockCategoryId)
        ->with('unit')
        ->get()
        ->map(function ($stock) {
            // Tentukan status berdasarkan unit_qty
            $status = 'Out of Stock';
            if ($stock->unit_qty > 15) {
                $status = 'In Stock';
            } elseif ($stock->unit_qty > 5) {
                $status = 'Low Stock';
            }

            return [
                'id' => $stock->id,
                'name' => $stock->name,
                'qty' => $stock->unit_qty,
                'unit' => $stock->unit->name ?? '-',
                'status' => $status,
            ];
        });

    return response()->json([
        'status' => 'success',
        'data' => $stocks,
    ]);
}


public function unit()
    {
        $units = Unit::select('id', 'unit_type')->get();

        return response()->json([
            'data' => $units
        ]);
    }


public function addStock(Request $request)
    {
       
        $validated = $request->validate([
            'name' => 'required|string',
            'price_per_unit' => 'nullable|numeric',
            'unit_qty' => 'required|numeric',
            'unit_id' => 'required|integer',
            'expired_duration' => 'nullable|integer',
            'stock_category_id' => 'required|integer',
            'store_id' => 'required|integer',
        ]);

        $stock = Stock::create([
            'name' => $validated['name'],
            'price_per_unit' => $validated['price_per_unit'] ?? 0,
            'unit_qty' => $validated['unit_qty'],
            'unit_id' => $validated['unit_id'],
            'expired_duration' => $validated['expired_duration'] ?? null,
            'stock_category_id' => $validated['stock_category_id'],
            'store_id' => $validated['store_id'],
        ]);

        return response()->json([
            'message' => 'Stock added successfully!',
            'stock' => $stock
        ], 201);
    }



    public function salesToday()
    {
        $today = Carbon::today();

        // Fetch today's sales grouped by variant (size)
        $todaySales = DB::table('invoice')
            ->join('orders', 'invoice.order_id', '=', 'orders.id')
            ->whereDate('orders.created_at', $today)
            ->groupBy('invoice.variant_id')
            ->select(
                'invoice.variant_id',
                DB::raw('SUM(invoice.quantity_bought) as total_bottles')
            )
            ->get();

        $result = [];

        foreach ($todaySales as $item) {
            $result[] = [
                'variant_id' => $item->variant_id,
                'total_bottles' => $item->total_bottles,
            ];
        }

        return response()->json([
            'data' => $result
        ]);
    }

    public function finance(Request $request)
    {
        $filter = $request->input('filter', 'monthly'); // default is monthly
        $month = $request->input('month'); // optional
        $year = $request->input('year', Carbon::now()->year); // default current year

        $query = DB::table('orders');

        // Apply filters
        if ($filter == 'daily') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter == 'weekly') {
            $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        } elseif ($filter == 'monthly') {
            if ($month) {
                $query->whereMonth('created_at', $month)
                      ->whereYear('created_at', $year);
            } else {
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
            }
        } elseif ($filter == 'year') {
            $query->whereYear('created_at', $year);
        }

        // Revenue = sum gross_amount
        $revenue = $query->sum('gross_amount');

        // Orders = count
        $orders = DB::table('orders')
            ->when($filter == 'daily', fn($q) => $q->whereDate('created_at', Carbon::today()))
            ->when($filter == 'weekly', fn($q) => $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]))
            ->when($filter == 'monthly', function($q) use ($month, $year) {
                $month = $month ?? Carbon::now()->month;
                return $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
            })
            ->when($filter == 'year', fn($q) => $q->whereYear('created_at', $year))
            ->count();

        // Customers = count customers created in that time
        $customers = DB::table('orders')
    ->when($filter == 'daily', fn($q) => $q->whereDate('created_at', Carbon::today()))
    ->when($filter == 'weekly', fn($q) => $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]))
    ->when($filter == 'monthly', function($q) use ($month, $year) {
        $month = $month ?? Carbon::now()->month;
        return $q->whereMonth('created_at', $month)->whereYear('created_at', $year);
    })
    ->when($filter == 'year', fn($q) => $q->whereYear('created_at', $year))
    ->distinct('customer_id')
    ->count('customer_id');


        // For now, assume Profits = Revenue (no cost calculation yet)
        $profits = $revenue;

        return response()->json([
            'revenue' => $revenue,
            'profits' => $profits,
            'orders' => $orders,
            'customers' => $customers,
        ]);
    }


    public function countBySize(Request $request)
    {
        $now = Carbon::now(); // Bulan dan tahun sekarang
        $productId = $request->input('product_id'); // ambil dari query string

        $query = DB::table('product_variant_option')
            ->join('product_variants', 'product_variant_option.product_variant_id', '=', 'product_variants.id')
            ->join('product', 'product_variants.product_id', '=', 'product.id')
            ->join('variant_options', 'product_variant_option.variant_option_id', '=', 'variant_options.id')
            ->join('invoice', 'invoice.variant_id', '=', 'product_variants.id')
            ->join('orders', 'invoice.order_id', '=', 'orders.id')
            ->whereMonth('orders.created_at', $now->month)
            ->whereYear('orders.created_at', $now->year);

        // Jika ada product_id dikirim, filter di sini
        if ($productId) {
            $query->where('product.id', $productId);
        }

        $data = $query->select(
                'variant_options.name as size',
                DB::raw('SUM(invoice.quantity_bought) as total_bottles')
            )
            ->groupBy('variant_options.name')
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function getProducts()
{
    $products = DB::table('product')
                ->select('id', 'name')
                ->get();

    return response()->json([
        'data' => $products
    ]);
}


public function productStandard()
    {
        $products = DB::table('product')
            ->select('id', 'name', 'image_url')
            ->get();

        $result = [];

        foreach ($products as $product) {
            // Get Ingredients (from BOM)
            $ingredients = DB::table('bom')
                ->join('units', 'bom.unit_id', '=', 'units.id')
                ->join('product', 'bom.product_id', '=', 'product.id')
                ->where('bom.product_id', $product->id)
                ->select('product.name as name', 'bom.quantity_required as qty', 'units.symbol as unit')
                ->get();

            // Get Raw Materials
            $rawMaterials = DB::table('bom')
                ->join('stock', 'bom.stock_id', '=', 'stock.id')
                ->where('bom.product_id', $product->id)
                ->where('stock.stock_category_id', 1) // Raw Material
                ->select('product.name as name', 'stock.name as stock_name', 'stock.price_per_unit as cost')
                ->join('product', 'bom.product_id', '=', 'product.id')
                ->get();

            // Get WIP
            $wips = DB::table('bom')
                ->join('stock', 'bom.stock_id', '=', 'stock.id')
                ->where('bom.product_id', $product->id)
                ->where('stock.stock_category_id', 3) // WIP
                ->select('product.name as name', 'stock.name as stock_name', 'stock.price_per_unit as cost')
                ->join('product', 'bom.product_id', '=', 'product.id')
                ->get();

            // Total cost (raw material + WIP)
            $totalCost = 0;
            foreach ($rawMaterials as $raw) {
                $totalCost += $raw->cost;
            }
            foreach ($wips as $wip) {
                $totalCost += $wip->cost;
            }

            $result[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => 'Lorem ipsum kangen rido', // Default sementara
                'image_url' => $product->image_url,
                'ingredients' => $ingredients,
                'raw_materials' => $rawMaterials,
                'wip' => $wips,
                'total_cost' => $totalCost,
            ];
        }

        return response()->json($result);
    }

    public function todayProduction()
{
    $today = Carbon::today(); // ambil tanggal hari ini

    $productions = DB::table('production_history')
        ->join('product_variants', 'production_history.product_variants_id', '=', 'product_variants.id')
        ->join('product', 'product_variants.product_id', '=', 'product.id')
        ->whereDate('production_history.production_date', $today)
        ->select(
            'product_variants.id as variant_id',
            'product_variants.quantity as variant_quantity',
            'product.name as product_name',
            'product_variants.price as variant_price',
            DB::raw('SUM(production_history.quantity_produced) as quantity_produced_today')
        )
        ->groupBy(
            'product_variants.id',
            'product_variants.quantity',
            'product.name',
            'product_variants.price'
        )
        ->get();

    return response()->json([
        'data' => $productions
    ]);
}


}




