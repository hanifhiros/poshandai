<?php

namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Employee;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
{
    $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $search = $request->input('search');

        $employees = Employee::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
        })->where('store_id', $selected_store_id)->paginate(10);


    return view('handai-manager.finance.employee.index', compact('employees','selected_store'));
}
public function create()
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        return view('handai-manager.finance.employee.create',compact('selected_store'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employee,email',
            'contact_number' => 'nullable|string|max:20',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
        ]);

        $tempPassword = Str::random(10);

        Employee::create(array_merge(
            $request->only('name', 'email', 'contact_number', 'position', 'salary'),
            [
                'store_id' => session('selected_store'),
                'password' => bcrypt($tempPassword),
            ]
        ));

        return redirect()->route('manager.finance.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.')
            ->with('temp_password', $tempPassword);
    }

}
