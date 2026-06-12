@extends('layouts.layoutBlank')

@section('title', 'Landing Page')

@section('vendor-style')
@endsection

@section('page-style')
@endsection

@section('content')
    <div class="flex min-h-screen bg-gray-100">
        <!-- Left Image Section -->
        <div class="hidden lg:flex flex-2 p-2  justify-center items-center bg-gray-100">
            <div class="  rounded-xl w-full max-w-5xl object-contain">
                <img src="{{ asset('assets\svg\banner2.svg') }}" alt="Login Illustration">
            </div>

        </div>

        <!-- Login Form -->
        <div class="flex-1 flex justify-center items-center p-6 lg:p-12 bg-white">
            <div class="w-full max-w-md space-y-6">
                <!-- Logo -->
                <div class="flex justify-center space-x-4 mb-8">
                    <a class="flex items-center">
                        <img src="{{ asset('assets/svg/Partner/BTP.svg') }}" class="h-16 mr-2" alt="Logo">
                    </a>
                    <a class="flex items-center">
                        <img src="{{ asset('assets/svg/Partner/kemenkop.svg') }}" class="h-12" alt="Logo">
                    </a>
                    <a class="flex items-center">
                        <img src="{{ asset('assets/svg/Partner/TelU.svg') }}" class="h-12" alt="Logo">
                    </a>
                </div>
                @error('role')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
                <!-- Login Heading -->
                <div class="text-center">
                    <h3 class="text-3xl font-bold text-primary">HANDAI COFFEE</h3>
                    <h5 class="text-xl font-semibold mb-2">Telkom University</h5>
                    <p class="text-gray-500">Silahkan Login menggunakan akun Handai Coffee anda</p>
                </div>

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger bg-red-100 text-red-700 p-4 rounded-md shadow-md mb-4">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div x-data="{ loginType: 'seller', selectedRole: '' }">
                    <form action="{{ url('login/exe') }}" method="POST" class="space-y-6">
                        @csrf
                        <!-- Seluruh isi form dari role, email, password, tombol login -->
                                            <!-- Pilihan tipe login -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Login sebagai:</label>
                        <div class="flex space-x-2">
                            <label :class="loginType === 'seller' ? 'bg-green-100 text-green-700 font-semibold' : 'bg-white text-gray-700'"
                                class="px-4 py-2 rounded-lg border border-green-500 cursor-pointer transition">
                                <input type="radio" name="login_type" value="seller" x-model="loginType" class="hidden">
                                Penjual
                            </label>
                            <label :class="loginType === 'reseller' ? 'bg-green-100 text-green-700 font-semibold' : 'bg-white text-gray-700'"
                                class="px-4 py-2 rounded-lg border border-green-500 cursor-pointer transition">
                                <input type="radio" name="login_type" value="reseller" x-model="loginType" class="hidden">
                                Reseller
                            </label>
                        </div>
                    </div>

                                        <!-- Role hanya jika seller -->
                                        <div x-show="loginType === 'seller'" class="mb-4">
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Role</label>
                                            <div class="flex space-x-2">
                                                <label :class="selectedRole === 'POS' ? 'bg-green-100 text-green-700 font-semibold' : 'bg-white text-gray-700'"
                                                       class="px-4 py-2 rounded-lg border border-green-500 cursor-pointer transition">
                                                    <input type="radio" name="role" value="POS" x-model="selectedRole" class="hidden">
                                                    POS
                                                </label>
                                                <label :class="selectedRole === 'Kasir' ? 'bg-green-100 text-green-700 font-semibold' : 'bg-white text-gray-700'"
                                                       class="px-4 py-2 rounded-lg border border-green-500 cursor-pointer transition">
                                                    <input type="radio" name="role" value="Kasir" x-model="selectedRole" class="hidden">
                                                    Kasir
                                                </label>
                                                <label :class="selectedRole === 'Manager' ? 'bg-green-100 text-green-700 font-semibold' : 'bg-white text-gray-700'"
                                                       class="px-4 py-2 rounded-lg border border-green-500 cursor-pointer transition">
                                                    <input type="radio" name="role" value="Manager" x-model="selectedRole" class="hidden">
                                                    Manager
                                                </label>
                                            </div>
                                        </div>

                                        
                
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                        <input type="text" id="email" name="email" placeholder="email"
                            class="input input-primary mt-2 p-3 w-full border rounded-md" required>
                    </div>
                
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="input input-primary mt-2 p-3 w-full border rounded-md" required>
                    </div>
                
                    <!-- Hidden untuk kirim login_type & role -->
                    <input type="hidden" name="login_type" :value="loginType">
                    <input type="hidden" name="role" :value="selectedRole">
                
                    <!-- Tombol Login -->
                    <button type="submit" class="w-full py-3 btn btn-primary mt-6">
                        <i class="ti ti-login pe-1"></i> Login
                    </button>
                        <!-- Seluruh isi hingga sebelum <p class="text-sm text-center mt-4"> -->
                    </form>
                </div>
                

                




                
                
                <!-- Login Form -->
                

                <!-- Help Link -->
           
                

                <div class="text-center mt-4">
                    <span class="text-sm">Butuh Bantuan? <a href="http://wa.me/+6281219296850"
                            class="link link-primary font-semibold link-hover ">Chat
                            Admin</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const eyeIcon = document.querySelector('#password + span i');
        const passwordInput = document.getElementById('password');

        if (eyeIcon && passwordInput) {
            eyeIcon.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                this.classList.toggle('ti-eye');
                this.classList.toggle('ti-eye-off');
            });
        }
    });
</script>
@endsection

@section('vendor-script')
@endsection

