@extends('layouts.layoutBlank')

@section('title', 'Edit Akun')

@section('content')
<div class="flex justify-center items-center min-h-screen bg-gray-50 px-4">
    <div class="w-full max-w-xl bg-white p-8 rounded-2xl shadow-lg border border-gray-200">
        <div class="flex justify-center mb-6">
            <img src="{{ asset('assets/logo.png') }}" alt="Handai Coffee Logo" class="h-16">
        </div>

        <h2 class="text-3xl font-bold text-center text-green-700 mb-1">Edit Akun</h2>
        <p class="text-center text-sm text-gray-500 mb-6">Perbarui data akun pengguna sistem</p>

        @if ($errors->any())
            <div class="bg-red-100 text-red-600 p-4 rounded mb-4 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li class="mt-1">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('superadmin.accounts.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring focus:ring-green-300" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring focus:ring-green-300" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 font-semibold">Password (opsional)</label>
                    <input type="password" name="password" class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring focus:ring-green-300">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full border rounded-lg px-3 py-2 mt-1 focus:ring focus:ring-green-300">
                </div>
            </div>
            

            {{-- Role Selection --}}
            <div x-data="{ selectedRoles: {{ Js::from($assignedRoles) }}, roles: {{ Js::from($roles) }} }" class="mb-6">
                <h3 class="font-bold text-lg text-gray-700 mb-3">Fitur</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <template x-for="role in roles" :key="role.id">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" :value="role.id.toString()" x-model="selectedRoles" name="roles[]" class="form-checkbox h-5 w-5 text-green-600">
                            <span class="text-base font-semibold text-gray-800" x-text="role.name"></span>
                        </label>
                    </template>
                </div>

                <div class="space-y-6">
                    <template x-for="role in roles" :key="role.id">
                        <div x-show="selectedRoles.includes(role.id.toString())" x-transition class="border rounded-lg p-4 bg-gray-50 shadow-sm">
                            <h4 class="text-green-700 font-semibold text-md mb-2">Divisi dari <span x-text="role.name"></span></h4>

                            <div class="space-y-3 pl-3">
                                <template x-for="child in (role.children || [])" :key="child.id">
                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" :value="child.id.toString()" x-model="selectedRoles" name="roles[]" class="form-checkbox h-5 w-5 text-green-600">
                                            <span class="text-base text-gray-800" x-text="child.name.split('-').pop()"></span>
                                        </label>

                                        <div x-show="selectedRoles.includes(child.id.toString())" x-transition class="mt-2 pl-6 space-y-2">
                                            <h5 class="text-sm text-gray-600 font-semibold">Subdivisi:</h5>
                                            <template x-for="sub in (child.children || [])" :key="sub.id">
                                                <label class="flex items-center space-x-2">
                                                    <input type="checkbox" :value="sub.id.toString()" x-model="selectedRoles" name="roles[]" class="form-checkbox h-4 w-4 text-green-600">
                                                    <span class="text-sm text-gray-800" x-text="sub.name.split('-').pop().replace(/([A-Z])/g, ' $1')"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Store Access --}}
            <div x-data="{ isMultiStore: '{{ $storeMode }}' }" class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Akses Toko</label>
                <div class="space-y-2 mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="store_mode" value="multi" x-model="isMultiStore" class="form-radio h-4 w-4 text-green-600">
                        <span>Akses Semua Toko (MultiStore)</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="store_mode" value="manual" x-model="isMultiStore" class="form-radio h-4 w-4 text-green-600">
                        <span>Pilih Toko Secara Manual</span>
                    </label>
                </div>

                <div x-show="isMultiStore === 'manual'" x-transition>
                    <label class="block text-gray-700 font-semibold mb-2">Pilih Store</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($stores as $store)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="stores[]" value="{{ $store->id }}" class="form-checkbox h-5 w-5 text-green-600" {{ in_array($store->id, $assignedStores) ? 'checked' : '' }}>
                                <span>{{ $store->store_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('superadmin.account.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 hover:bg-gray-100 transition">← Batal</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">Perbarui Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection
