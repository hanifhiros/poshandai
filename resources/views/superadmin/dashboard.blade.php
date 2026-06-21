@extends('layouts.layoutBlank')

@section('title', 'Dashboard Superadmin')

@section('content')
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="bg-white w-full max-w-xl shadow-lg rounded-2xl p-8 border border-gray-100">
            <div class="flex items-center justify-center mb-6">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-12 drop-shadow-sm">
            </div>
            <h1 class="text-3xl font-extrabold text-center text-green-700 mb-2">Superadmin Dashboard</h1>
            <p class="text-gray-500 text-center mb-8">Selamat datang di panel utama Superadmin Handai Coffee.</p>

            <div class="grid gap-4">
                <a href="{{ route('superadmin.account.index') }}"
                   class="block w-full text-center py-4 px-4 bg-white text-green-700 font-bold border-2 border-green-600 rounded-xl hover:bg-green-600 hover:text-black transition-all duration-300 hover:shadow-md">
                    <i class="ti ti-users mr-2"></i> Manajemen Akun
                </a>

                <a href="{{ route('superadmin.store.index') }}"
                   class="block w-full text-center py-4 px-4 bg-white text-green-700 font-bold border-2 border-green-600 rounded-xl hover:bg-green-600 hover:text-black transition-all duration-300 hover:shadow-md">
                    <i class="ti ti-building-store mr-2"></i> Kelola Toko
                </a>

                <a href="{{ route('superadmin.simulate.index') }}"
                   class="block w-full text-center py-4 px-4 bg-white text-green-700 font-bold border-2 border-green-600 rounded-xl hover:bg-green-600 hover:text-black transition-all duration-300 hover:shadow-md">
                    <i class="ti ti-device-analytics mr-2"></i> Simulasi & Monitoring
                </a>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="mt-8 text-center pt-6 border-t border-gray-100">
                @csrf
                <button type="submit"
                        class="inline-flex items-center text-sm px-6 py-2 text-red-600 font-semibold border border-red-200 rounded-full bg-red-50 hover:bg-red-600 hover:text-white transition duration-300">
                    <i class="ti ti-logout mr-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
@endsection