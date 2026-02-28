@extends('layouts.layoutBlank')

@section('title', 'Daftar Customer')

@section('content')
@if ($errors->any())
    <div class="mb-4 text-red-600 text-sm bg-red-100 p-2 rounded">
        {{ $errors->first() }}
    </div>
@endif
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full">
        <div class="text-center mb-6">
            <img src="{{ asset('assets/logo.png') }}" class="mx-auto w-14 h-14 mb-2" alt="Logo">
            <h2 class="text-2xl font-bold text-green-700">Daftar Customer</h2>
            <p class="text-gray-600 text-sm">Isi data untuk membuat akun customer</p>
        </div>

        <form action="{{ route('customerOrder.register') }}" method="POST">
            @csrf
            <input type="hidden" name="store_id" value="{{ request()->query('store_id') }}">

            <div class="mb-3">
                <label class="text-sm font-medium">Nama</label>
                <input type="text" name="name" class="input input-bordered w-full" required>
            </div>

            <div class="mb-3">
                <label class="text-sm font-medium">Alamat</label>
                <input type="text" name="address" class="input input-bordered w-full" required>
            </div>

            <div class="mb-3">
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" class="input input-bordered w-full">
            </div>

            <div class="mb-3">
                <label class="text-sm font-medium">Nomor HP</label>
                <input type="text" name="contact_number" class="input input-bordered w-full" required>
            </div>

            <div class="mb-3">
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="password" class="input input-bordered w-full" required>
            </div>

            <div class="mb-4">
                <label class="text-sm font-medium">Gender</label>
                <select name="gender" class="select w-full">
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>

            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg shadow-md transition duration-200 hover:scale-105 active:scale-95">
                Daftar
            </button>
        


            


            <p class="mt-4 text-sm text-center">
                Sudah punya akun? 
                <a href="{{ route('customerOrder.loginForm', ['store_id' => request()->query('store_id')]) }}" 
                   class="text-green-700 font-semibold hover:underline">
                    Login sekarang
                </a>
            </p>
        </form>
    </div>
</div>
@endsection
