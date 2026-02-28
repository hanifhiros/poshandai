@extends('layouts.layoutBlank')

@section('title', 'Buat Akun Baru')

@section('content')
<div class="flex min-h-screen bg-gray-100">
    <!-- Left Image Section -->
    <div class="hidden lg:flex flex-2 p-2 justify-center items-center bg-gray-100">
        <div class="rounded-xl w-full max-w-5xl object-contain">
            <img src="{{ asset('assets/svg/banner2.svg') }}" alt="Register Illustration">
        </div>
    </div>

    <!-- Register Form -->
    <div class="flex-1 flex justify-center items-center p-6 lg:p-12 bg-white">
        <div class="w-full max-w-md space-y-6" x-data="{ selectedRole: '{{ old('role') }}' }">
            <!-- Logo -->
            <div class="flex justify-center space-x-4 mb-8">
                <img src="{{ asset('assets/svg/Partner/BTP.svg') }}" class="h-14" alt="BTP">
                <img src="{{ asset('assets/svg/Partner/kemenkop.svg') }}" class="h-10" alt="Kemenkop">
                <img src="{{ asset('assets/svg/Partner/TelU.svg') }}" class="h-10" alt="Telkom University">
            </div>

            <!-- Heading -->
            <div class="text-center">
                <h3 class="text-3xl font-bold text-primary">Buat Akun Baru</h3>
                <p class="text-gray-500">Silakan isi data berikut untuk membuat akun Handai Coffee</p>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-md shadow-sm">
                    <ul class="text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Register Form -->
            <form action="{{ url('register') }}" method="POST" class="space-y-5" @submit.prevent="loading = true; $el.submit();">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap"
                        class="mt-2 w-full border p-3 rounded-md @error('name') border-red-500 @enderror">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com"
                        class="mt-2 w-full border p-3 rounded-md @error('email') border-red-500 @enderror">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" placeholder="••••••••"
                        class="mt-2 w-full border p-3 rounded-md @error('password') border-red-500 @enderror">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi password"
                        class="mt-2 w-full border p-3 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Role</label>
                    <div class="flex gap-3">
                        @foreach (['POS', 'Kasir', 'Manager'] as $role)
                            <label 
                                :class="selectedRole === '{{ $role }}' ? 'bg-primary text-white' : 'bg-white text-gray-700'"
                                class="px-4 py-2 border rounded-md cursor-pointer transition-all duration-200"
                            >
                                <input type="radio" name="role" value="{{ $role }}" class="hidden"
                                    @click="selectedRole = '{{ $role }}'" {{ old('role') == $role ? 'checked' : '' }}>
                                {{ $role }}
                            </label>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 bg-primary text-white rounded-md hover:bg-indigo-600 transition">
                    <i class="ti ti-user-plus pe-1"></i> Buat Akun
                </button>
            </form>

            <div class="text-center mt-4">
                <span class="text-sm text-gray-600">Sudah punya akun? 
                    <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">Login di sini</a>
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
