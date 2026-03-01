@extends('handai-manager.layouts.master')

@section('title', 'Edit Customer')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Customer</h2>

    <form method="POST" action="{{ route('manager.marketing.customers.update', $customer->id) }}">
        @csrf
        @method('PUT')

        @if($errors->any())
            <div class="alert alert-error mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="input input-bordered w-full" required />
            </div>

            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                <select name="gender" class="input input-bordered w-full">
                    <option value="Laki-laki" {{ $customer->gender == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ $customer->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div>
                <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $customer->contact_number) }}" class="input input-bordered w-full" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="input input-bordered w-full" />
            </div>

            <div class="col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="3" class="input input-bordered w-full">{{ old('address', $customer->address) }}</textarea>
            </div>
        </div>

        <div class="mt-6 text-right">
            <button type="submit" class="btn btn-success px-6">Update Customer</button>
        </div>
    </form>
</div>
@endsection
