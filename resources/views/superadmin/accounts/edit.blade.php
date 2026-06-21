@extends('layouts.layoutBlank')

@section('title', 'Edit Akun Pegawai')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
    <div class="bg-white w-full max-w-2xl shadow-lg rounded-2xl p-8 border border-gray-100">
        
        <div class="flex items-center justify-center mb-4">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-12 drop-shadow-sm">
        </div>
        <h1 class="text-3xl font-extrabold text-center text-green-700 mb-1">Edit Akun</h1>
        <p class="text-gray-500 text-center mb-8 text-sm">Perbarui data pegawai Handai Coffee</p>

        <form action="{{ route('superadmin.accounts.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT') <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" placeholder="Kosongkan jika tidak diubah" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Role / Posisi</label>
                <select name="role" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-white transition-all cursor-pointer" required>
                    <option value="Manager" {{ old('role', $user->role) == 'Manager' ? 'selected' : '' }}>Manager</option>
                    <option value="Kasir" {{ old('role', $user->role) == 'Kasir' ? 'selected' : '' }}>Kasir / POS</option>
                </select>
            </div>

            @php
                // Mengambil jejak rekam akses toko pegawai ini
                $isMulti = old('is_multistore', ($assignedRole && $assignedRole->is_multistore) ? '1' : '0');
                $selectedStore = old('store_id', $assignedRole ? $assignedRole->store_id : '');
            @endphp

            <div x-data="{ isMultiStore: '{{ $isMulti }}' }" class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-3">Akses Toko</label>
                <div class="space-y-3">
                    <label class="flex items-center space-x-3 cursor-pointer group">
                        <input type="radio" name="is_multistore" value="1" x-model="isMultiStore" class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300">
                        <span class="text-gray-700 font-semibold group-hover:text-green-700 transition-colors">Akses Semua Toko (MultiStore)</span>
                    </label>
                    <label class="flex items-center space-x-3 cursor-pointer group">
                        <input type="radio" name="is_multistore" value="0" x-model="isMultiStore" class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300">
                        <span class="text-gray-700 font-semibold group-hover:text-green-700 transition-colors">Pilih Toko Secara Manual</span>
                    </label>
                </div>

                <div x-show="isMultiStore === '0'" x-transition class="mt-4 pt-4 border-t border-gray-200">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Cabang Toko</label>
                    <select name="store_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-white transition-all cursor-pointer">
                        <option value="" disabled {{ empty($selectedStore) ? 'selected' : '' }}>-- Pilih Cabang --</option>
                        
                        @foreach(\App\Models\Store::all() as $store)
                            <option value="{{ $store->id }}" {{ $selectedStore == $store->id ? 'selected' : '' }}>{{ $store->store_name }}</option>
                        @endforeach

                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                <a href="{{ route('superadmin.account.index') }}" 
                   class="px-6 py-3 text-red-600 font-semibold border border-red-200 rounded-full bg-red-50 hover:bg-red-600 hover:text-white transition duration-300 flex items-center">
                    <i class="ti ti-arrow-left mr-2"></i> Batal / Kembali
                </a>
                
                <button type="submit" 
                        class="px-6 py-3 bg-green-600 text-white font-bold rounded-full hover:bg-green-700 hover:shadow-lg transition duration-300 flex items-center">
                    <i class="ti ti-device-floppy mr-2"></i> Perbarui Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection