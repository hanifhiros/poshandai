@extends('handai-manager.layouts.master')

@section('title', 'Employee List')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6 mt-10y">
        <h2 class="text-2xl font-bold text-gray-800">Employee List</h2>
        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 mb-6 gap-4">
                  <!-- Search Form -->
                  <form method="GET" action="{{ route('manager.finance.employees.index') }}" class="mb-6 flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Nama</label>
                        <input 
                            type="text" 
                            name="search" 
                            id="search" 
                            value="{{ request('search') }}" 
                            placeholder="Masukkan nama, email, atau posisi..." 
                            class="w-full border bg-white  border-gray-300 rounded px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                        />
                    </div>
                    <div class="pt-2 sm:pt-0">
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                            Cari
                        </button>
                    </div>
                </form>
                

            <a href="{{ route('manager.finance.employees.create') }}" class="btn btn-primary whitespace-nowrap">+ Add Employee</a>
            
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif

  

        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full text-sm text-left table-auto">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">ID</th>
                        <th class="px-4 py-3 whitespace-nowrap">Name</th>
                        <th class="px-4 py-3 whitespace-nowrap">Email</th>
                        <th class="px-4 py-3 whitespace-nowrap">Phone</th>
                        <th class="px-4 py-3 whitespace-nowrap">Position</th>
                        <th class="px-4 py-3 whitespace-nowrap">Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">#{{ $employee->id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $employee->name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $employee->email }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $employee->contact_number }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $employee->position }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">Rp{{ number_format($employee->salary, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-500">Tidak ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end">
            {{ $employees->appends(request()->query())->links('vendor.pagination.custom-tailwind') }}
        </div>
    </div>
</div>
@endsection
