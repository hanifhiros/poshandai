@extends('handai-manager.layouts.master')

@section('title', 'Add Employee')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-10">
    <div class="bg-white p-6 rounded-lg shadow-md max-w-2xl mx-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Add New Employee</h2>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded shadow-sm text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.finance.employees.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="input input-bordered w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" name="position" value="{{ old('position') }}" class="input input-bordered w-full" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                    <input type="number" name="salary" value="{{ old('salary') }}" class="input input-bordered w-full" required>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button type="submit" class="btn btn-primary">Save Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection