@extends('layouts.layoutBlank')

@section('title', 'Buat Akun Baru')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
    <div class="bg-white w-full max-w-2xl shadow-lg rounded-2xl p-8 border border-gray-100">
        
        <!-- Logo & Header -->
        <div class="flex items-center justify-center mb-4">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-12 drop-shadow-sm">
        </div>
        <h1 class="text-3xl font-extrabold text-center text-green-700 mb-1">Buat Akun Baru</h1>
        <p class="text-gray-500 text-center mb-8 text-sm">Kelola akun baru untuk sistem Handai Coffee</p>

        <!-- Form Pembuatan Akun -->
        <form action="{{ route('superadmin.accounts.store') }}" method="POST">
            @csrf

            <!-- Grid Input -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" placeholder="Masukkan nama" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" placeholder="email@contoh.com" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" placeholder="••••••••" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••" 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all" required>
                </div>
            </div>

            <!-- Role Pegawai -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Role / Posisi</label>
                <select name="role" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-white transition-all cursor-pointer" required>
                    <option value="" disabled selected>-- Pilih Posisi Pegawai --</option>
                    <option value="Manager">Manager</option>
                    <option value="Kasir">Kasir/POS</option>
                </select>
            </div>

            <!-- Akses Toko (Diberi animasi Alpine.js) -->
            <div x-data="{ isMultiStore: '1' }" class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
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

                <!-- Dropdown Pilihan Toko (HANYA MUNCUL JIKA PILIH MANUAL) -->
                <div x-show="isMultiStore === '0'" x-transition class="mt-4 pt-4 border-t border-gray-200">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Cabang Toko</label>
                    <select name="store_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-white transition-all cursor-pointer">
                        <option value="" disabled selected>-- Pilih Cabang --</option>
                        
                        <!-- Tech Lead Trick: Tarik data dari database secara langsung -->
                        @foreach(\App\Models\Store::all() as $store)
                            <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                        @endforeach

                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-between border-t border-gray-100 pt-6">
                <!-- Tombol Batal/Kembali -->
                <a href="{{ route('superadmin.account.index') }}" 
                   class="px-6 py-3 text-red-600 font-semibold border border-red-200 rounded-full bg-red-50 hover:bg-red-600 hover:text-white transition duration-300 flex items-center">
                    <i class="ti ti-arrow-left mr-2"></i> Batal / Kembali
                </a>
                
                <button type="submit" 
                        class="px-6 py-3 bg-green-600 text-white font-bold rounded-full hover:bg-green-700 hover:shadow-lg transition duration-300 flex items-center">
                    <i class="ti ti-device-floppy mr-2"></i> Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection