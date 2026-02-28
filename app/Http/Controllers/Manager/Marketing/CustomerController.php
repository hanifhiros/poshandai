<?php

namespace App\Http\Controllers\Manager\Marketing;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Store;
class CustomerController extends Controller
{
    // public function index()
    // {
    //     $selected_store_id = session('selected_store');
    //     $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
    //     $customers = Customer::when(request('search'), function($query) {
    //         $query->where('name', 'like', '%' . request('search') . '%')
    //               ->orWhere('email', 'like', '%' . request('search') . '%');
    //     })->paginate(5);
        
    //     return view('handai-manager.marketing.customers.index', compact('customers','selected_store'));
//     // }
//     public function index()
// {
//     $store_id = session('store_id');
//     $global_id = session('global_id'); // id user superadmin atau owner

//     $customers = Customer::query()
//         ->where(function ($query) use ($store_id, $global_id) {
//             $query->where('store_id', $store_id)
//                   ->orWhere(function ($q) use ($global_id) {
//                       $q->whereNull('store_id')->where('created_by', $global_id); // customer global
//                   });
//         })
//         ->when(request('search'), function ($q) {
//             $q->where('name', 'like', '%' . request('search') . '%')
//               ->orWhere('email', 'like', '%' . request('search') . '%');
//         })
//         ->paginate(5);

//     $selected_store = $store_id ? Store::find($store_id) : null;

//     return view('handai-manager.marketing.customers.index', compact('customers', 'selected_store'));
// }
public function index(Request $request)
{
    $user = auth()->user();
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
    $search = $request->input('search');

    $customers = Customer::where(function ($query) use ($user) {
            $query->whereHas('store', function ($q) use ($user) {
                // toko yang dibuat langsung oleh user
                $q->where('owner_id', $user->id)
                  ->orWhere('owner_id', $user->created_by);
            })
            ->orWhere(function ($q) use ($user) {
                // customer tanpa store_id tapi dibuat oleh user
                $q->where('created_by', $user->created_by);
            })->orWhere(function ($q) use ($user) {
                // customer tanpa store_id tapi dibuat oleh user
                $q->where('created_by', $user->id);
            });
        })
        ->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        })
        ->orderBy('name')
        ->paginate(10);

    return view('handai-manager.marketing.customers.index', compact('customers', 'selected_store'));
}



public function edit(Customer $customer)
{
    $selected_store_id = session('selected_store');
    $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
    return view('handai-manager.marketing.customers.edit', compact('customer','selected_store'));
}
public function update(Request $request, Customer $customer)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'contact_number' => 'required|string|max:20',
        'email' => 'nullable|email',
        'address' => 'nullable|string|max:255',
    ]);

    $customer->update(array_merge(
        $request->only(['name', 'contact_number', 'email', 'address']),
        [
            'store_id' => $customer->store_id ?? session('store_id'),
            'created_by' => $customer->created_by ?? session('global_id'),
        ]
    ));
    

    return redirect()->route('manager.marketing.customers.index')->with('success', 'Customer updated successfully.');
}


// public function update(Request $request, Customer $customer)
// {
//     $request->validate([
//         'name' => 'required|string|max:255',
//         'contact_number' => 'required|string|max:20',
//         'email' => 'nullable|email',
//         'address' => 'nullable|string|max:255',
//     ]);

//     $customer->update($request->all());
//     return redirect()->route('manager.marketing.customers.index')->with('success', 'Customer updated successfully.');
// }

public function destroy(Customer $customer)
{
    $customer->delete();
    return redirect()->route('manager.marketing.customers.index')->with('success', 'Customer deleted successfully.');
}

    public function create()
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        return view('handai-manager.marketing.customers.create',compact('selected_store'));
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'contact_number' => 'required|string|max:20',
        'email' => 'nullable|email',
        'address' => 'nullable|string|max:255',
    ]);
    $user = auth()->user();
    // dd(session('selected_store'));
    Customer::create([
        'name' => $request->name,
        'contact_number' => $request->contact_number,
        'email' => $request->email,
        'address' => $request->address,
        'store_id' => session('selected_store'),       // ambil dari toko aktif saat ini
        'created_by' => session('global_id')? session('global_id') : $user->created_by,    // ambil dari user yang login (superadmin / manager)
    ]);

    return redirect()->route('manager.marketing.customers.index')->with('success', 'Customer added successfully.');
}

}

