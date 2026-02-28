@extends('layouts.layoutBlank')

@section('title', 'Registrasi Superadmin')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md bg-white shadow-xl rounded-2xl px-8 py-10">
        
        {{-- Logo --}}
        <div class="flex justify-center mb-6">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo Handai Coffee" class="h-14">
        </div>

        {{-- Heading --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-green-700">Registrasi Akun</h1>
            {{-- <p class="text-sm text-gray-500"></p> --}}
        </div>

        {{-- Validation Error --}}
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 px-4 py-3 mb-4 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('register') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                    class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="Masukkan nama lengkap" required>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="contoh@email.com" required>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password"
                    class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="••••••••" required>
            </div>

            {{-- Password Confirm --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                    placeholder="Ulangi password" required>
            </div>

            {{-- Submit --}}
            <div>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold shadow-sm transition">
                    Daftar Sekarang
                </button>
            </div>

            {{-- Redirect --}}
            <div class="text-center text-sm text-gray-500 mt-4">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-green-600 font-medium hover:underline">Login di sini</a>
            </div>
        </form>
    </div>
</div>
@endsection
