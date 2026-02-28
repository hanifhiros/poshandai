@extends('layouts.layoutBlank')

@section('title', 'Daftar Reseller')

@section('content')
@if ($errors->any())
    <div class="mb-4 text-red-600 text-sm bg-red-100 p-2 rounded">
        {{ $errors->first() }}
    </div>
@endif

<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full text-center">
        <img src="{{ asset('assets/logo.png') }}" class="mx-auto w-14 h-14 mb-4" alt="Logo">
        
        <h2 class="text-2xl font-bold text-green-700 mb-2">Handai Coffee</h2>
        <p class="text-gray-600 mb-6 text-sm">Pendaftaran akun untuk menjadi reseller resmi kami</p>

        <form action="{{ route('reseller.register.submit') }}" method="POST">
            @csrf

            <div class="mb-4 text-left">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Nama Reseller">
            </div>

            <div class="mb-4 text-left">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="email@contoh.com">
            </div>

            <div class="mb-4 text-left">
                <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                <input type="text" id="contact_number" name="contact_number" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="08xxxxxxxxxx">
            </div>

            <div class="mb-4 text-left">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required minlength="6"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Minimal 6 karakter">
            </div>
            <div class="mb-4 text-left">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Ulangi password">
            </div>
            

            <button type="submit"
                class="w-full bg-green-700 text-white font-semibold py-2 rounded-lg shadow hover:bg-green-800 transition">
                Daftar Reseller
            </button>
        </form>

        <p class="text-sm text-gray-600 mt-6">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-green-700 font-semibold hover:underline">
                Login sekarang
            </a>
        </p>
    </div>
</div>
@endsection
