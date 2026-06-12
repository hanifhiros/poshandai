<?php

namespace App\Http\Controllers\Manager\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Store;

class CustomerController extends Controller
{
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
public function update(\App\Http\Requests\UpdateCustomerRequest $request, Customer $customer)
{
    $this->authorize('update', $customer);

    $customer->update(array_merge(
        $request->validated(),
        [
            'store_id' => $customer->store_id ?? session('store_id'),
            'created_by' => $customer->created_by ?? session('global_id'),
        ]
    ));

    return redirect()->route('manager.marketing.customers.index')->with('success', 'Customer updated successfully.');
}

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

    public function store(\App\Http\Requests\StoreCustomerRequest $request)
{
    $this->authorize('create', Customer::class);

    $user = auth()->user();
    Customer::create(array_merge(
        $request->validated(),
        [
            'store_id' => session('selected_store'),
            'created_by' => session('global_id') ?? $user->created_by,
        ]
    ));

    return redirect()->route('manager.marketing.customers.index')->with('success', 'Customer added successfully.');
}

}

