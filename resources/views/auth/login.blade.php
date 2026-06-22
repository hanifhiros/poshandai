@extends('layouts.layoutBlank')

@section('title', 'Login - Handai Coffee')

@section('content')
    <div class="flex min-h-screen bg-gray-50">
        <!-- Left Image Section (Illustration) -->
        <div class="hidden lg:flex flex-2 p-2 justify-center items-center bg-gray-100">
            <div class="rounded-xl w-full max-w-5xl object-contain">
                <!-- Path gambar diubah ke absolut (/) agar mengabaikan APP_URL yang mati -->
                <img src="/assets/login.png" alt="Login Illustration" class="drop-shadow-xl w-full">
            </div>
        </div>

        <!-- Login Form -->
        <div class="flex-1 flex justify-center items-center p-6 lg:p-12 bg-white shadow-[-10px_0_30px_rgba(0,0,0,0.05)]">
            <div class="w-full max-w-md space-y-6">
                <!-- Logos -->
                <div class="flex justify-center space-x-4 mb-8">
                    <img src="/assets/BTP.png" class="h-16 mr-2" alt="Logo BTP">
                    <img src="/assets/kemenkop.png" class="h-12" alt="Logo Kemenkop">
                    <img src="/assets/TelU.png" class="h-12" alt="Logo TelU">
                </div>
                
                <!-- Login Heading -->
                <div class="text-center mb-6">
                    <h3 class="text-3xl font-black text-green-700 tracking-tight">HANDAI COFFEE</h3>
                    <h5 class="text-lg font-semibold text-gray-700 mb-1">Telkom University</h5>
                    <p class="text-gray-500 text-sm">Masuk ke dalam sistem operasional</p>
                </div>

                <!-- ALARM ERROR (Peringatan Salah Password/Email) -->
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg mb-6 shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="ti ti-alert-triangle text-red-500 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-red-800">Akses Ditolak!</h3>
                                <div class="mt-1 text-sm text-red-700">
                                    <ul class="list-disc pl-4 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div x-data="{ loginType: 'admin', selectedRole: 'Manager' }">
                    <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                        @csrf
                        
                        <!-- Pilihan Tipe Login -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Login Sebagai:</label>
                            <div class="flex space-x-2 bg-gray-100 p-1 rounded-lg">
                                <label :class="loginType === 'admin' ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="w-1/2 text-center py-2 rounded-md cursor-pointer transition-all duration-200 font-semibold text-sm">
                                    <input type="radio" name="login_type" value="admin" x-model="loginType" class="hidden">
                                    Superadmin
                                </label>
                                <label :class="loginType === 'pegawai' ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="w-1/2 text-center py-2 rounded-md cursor-pointer transition-all duration-200 font-semibold text-sm">
                                    <input type="radio" name="login_type" value="pegawai" x-model="loginType" class="hidden">
                                    Pegawai
                                </label>
                            </div>
                        </div>

                        <!-- Role HANYA MUNCUL jika pilih Pegawai Cabang (Kasir & POS Disatukan) -->
                        <div x-show="loginType === 'pegawai'" x-transition.opacity class="pt-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Posisi Anda:</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label :class="selectedRole === 'Manager' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                                       class="text-center py-2 text-sm rounded-lg border cursor-pointer transition-all font-medium">
                                    <input type="radio" x-model="selectedRole" value="Manager" class="hidden"> Manager
                                </label>
                                <label :class="selectedRole === 'POS' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                                       class="text-center py-2 text-sm rounded-lg border cursor-pointer transition-all font-medium">
                                    <!-- Value diubah ke "POS" agar sinkron dengan database seeder dan route middleware -->
                                    <input type="radio" x-model="selectedRole" value="POS" class="hidden"> Kasir / POS
                                </label>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="pt-2">
                            <label for="email" class="block text-sm font-bold text-gray-700">Alamat Email</label>
                            <input type="email" id="email" name="email" placeholder="contoh: admin@handai.com"
                                class="w-full mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:bg-white transition-all outline-none" required value="{{ old('email') }}">
                        </div>
                    
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-700">Kata Sandi</label>
                            <div class="relative mt-2">
                                <input type="password" id="password" name="password" placeholder="••••••••"
                                    class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:bg-white transition-all outline-none" required>
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-green-600">
                                    <i class="ti ti-eye text-lg"></i>
                                </button>
                            </div>
                        </div>
                    
                        <!-- Sistem Otomatis menentukan Role berdasarkan Tipe Login -->
                        <input type="hidden" name="role" :value="loginType === 'admin' ? 'Superadmin' : selectedRole">
                    
                        <!-- Tombol Login -->
                        <button type="submit" class="w-full py-3.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 mt-4">
                            <i class="ti ti-login mr-1"></i> Login
                        </button>
                    </form>
                </div>

                <div class="text-center mt-6">
                    <span class="text-sm text-gray-500">Lupa password atau butuh bantuan? 
                        <a href="http://wa.me/+6281219296850" class="text-green-600 font-bold hover:underline">Chat Admin</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                this.innerHTML = isHidden ? '<i class="ti ti-eye-off text-lg"></i>' : '<i class="ti ti-eye text-lg"></i>';
            });
        }
    });
</script>
@endsection