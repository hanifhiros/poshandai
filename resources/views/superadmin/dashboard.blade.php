@extends('layouts.layoutBlank')

@section('title', 'Dashboard Superadmin')

@section('content')
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="bg-white w-full max-w-xl shadow rounded-2xl p-8">
            <div class="flex items-center justify-center mb-6">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-10">
            </div>
            <h1 class="text-3xl font-bold text-center text-green-700 mb-2">Dashboard Superadmin</h1>
            <p class="text-gray-600 text-center mb-6">Selamat datang di panel Superadmin Handai Coffee.</p>

            <div class="grid gap-4">
                <a href="{{ route('superadmin.account.index') }}"
                   class="block w-full text-center py-3 px-4 bg-white text-green-700 font-semibold border border-green-600 rounded-lg hover:bg-green-600 hover:text-white transition duration-200">
                    Manajemen Akun
                </a>

                <a href="{{ route('superadmin.store.index') }}"
                   class="block w-full text-center py-3 px-4 bg-white text-green-700 font-semibold border border-green-600 rounded-lg hover:bg-green-600 hover:text-white transition duration-200">
                    Kelola Toko
                </a>

                <a href="{{ route('superadmin.simulate.index') }}"
                   class="block w-full text-center py-3 px-4 bg-white text-green-700 font-semibold border border-green-600 rounded-lg hover:bg-green-600 hover:text-white transition duration-200">
                    Managemen Toko
                </a>
            </div>

            <form action="{{ route('logout') }}" method="GET" class="mt-6 text-center">
                <button type="submit"
                        class="inline-block text-sm px-4 py-2 text-red-600 border border-red-500 rounded-lg bg-white hover:bg-red-500 hover:text-white transition duration-200">
                    Logout
                </button>
            </form>
        </div>
    </div>
@endsection
