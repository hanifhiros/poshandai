@extends('layouts.layoutBlank')

@section('title', 'Login Customer')

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
            <p class="text-gray-600 mb-6 text-sm">Silakan login sebagai customer atau lanjut sebagai tamu</p>

            <form action="{{ route('customerOrder.login') }}" method="POST">
                @csrf
                <input type="hidden" name="store_id"
                    value="{{ request()->query('store_id') ? request()->query('store_id') : session('selected_store') }}">
                @if(isset($reseller_code))
                    <input type="hidden" name="reseller_code" value="{{ $reseller_code }}">
                @endif
                <div class="mb-4">
                    <label for="contact_number" class="block text-left text-sm font-medium text-gray-700 mb-1">Nomor
                        HP</label>
                    <input type="text" id="contact_number" name="contact_number" placeholder="08xxxxxxxxxx"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-left text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                </div>


                <button type="submit"
                    class="w-full bg-green-700 text-white font-semibold py-2 rounded-lg shadow hover:bg-green-800 transition">
                    Masuk sebagai Customer
                </button>


            </form>

            <p class="text-sm text-gray-600 mt-6">
                Belum punya akun?
                <a href="{{ route('customerOrder.registerForm', ['store_id' => request()->query('store_id')]) }}"
                    class="text-green-700 font-semibold hover:underline">
                    Daftar sekarang
                </a>
            </p>
            <div class="my-4 text-gray-500 text-sm">atau</div>

            <a href="{{ route('customerOrder.guest', ['store_id' => request()->query('store_id')]) }}"
                class="w-full block bg-white text-green-700 font-semibold py-2 border border-green-500 rounded-lg hover:bg-green-50 transition">
                Lanjut sebagai Tamu
            </a>

            <p class="text-sm text-gray-600 mt-6">
                Butuh bantuan? <a href="https://wa.me/6281234567890"
                    class="text-green-700 font-semibold hover:underline">Hubungi Admin</a>
            </p>
        </div>
    </div>
@endsection