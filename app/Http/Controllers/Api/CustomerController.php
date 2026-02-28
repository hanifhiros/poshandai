<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Store;

class CustomerController extends Controller
{
    public function custMobile(Request $request)
    {
        $storeId = $request->query('store_id');

        $customers = Customer::with('store')
            ->where('store_id', $storeId)
            ->select('id', 'store_id', 'name', 'contact_number', 'email', 'address', 'gender', 'qty_ordered', 'qty_ordered_avg', 'has_ordered')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'number' => $customer->contact_number,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'gender' => $customer->gender,
                    'qty_ordered' => $customer->qty_ordered,
                    'qty_ordered_avg' => $customer->qty_ordered_avg,
                    'has_ordered' => $customer->has_ordered ? true : false,
                    'store' => $customer->store ? $customer->store->store_name : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:10',
            'store_id' => 'required|exists:store,id',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'address' => $request->address,
            'gender' => $request->gender,
            'store_id' => $request->store_id,
            'created_by' => auth()->id() ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully.',
            'data' => $customer
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:10',
        ]);

        $customer = Customer::findOrFail($id);

        $customer->update([
            'name' => $request->name,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'address' => $request->address,
            'gender' => $request->gender,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully.',
            'data' => $customer
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully.'
        ]);
    }

}
