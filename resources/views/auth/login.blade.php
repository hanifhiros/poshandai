<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Handai Coffee</title>
    <!-- Pastikan file CSS/JS utama Laravel Anda terhubung di sini (Vite/Mix) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js untuk logika perpindahan Tab -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased">
    <!-- Background Image dengan efek gelap (Overlay) -->
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat relative" 
         style="background-image: url('https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=1920&auto=format&fit=crop');">
        
        <!-- Dark Overlay agar form lebih terbaca -->
        <div class="absolute inset-0 bg-black/50"></div>

        <!-- Glassmorphism Card Wrapper -->
        <div class="relative z-10 bg-white/10 backdrop-blur-lg border border-white/20 p-8 sm:p-10 rounded-3xl shadow-2xl w-full max-w-md mx-4">
            
            <!-- Logo & Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-white tracking-wider mb-1">HANDAI COFFEE</h1>
                <p class="text-sm text-gray-300 font-medium tracking-wide">Telkom University</p>
                <p class="text-xs text-gray-400 mt-4">Silakan Login menggunakan akun Handai Coffee Anda</p>
            </div>

            <!-- Komponen Alpine.js untuk Tab -->
            <div x-data="{ loginType: 'staff' }">
                
                <!-- Tab Selector -->
                <div class="flex bg-black/30 rounded-xl p-1 mb-6">
                    <button type="button" @click="loginType = 'staff'" 
                            :class="loginType === 'staff' ? 'bg-green-600 text-white shadow-md' : 'text-gray-300 hover:text-white'" 
                            class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all duration-300 focus:outline-none">
                        Login Staff
                    </button>
                    <button type="button" @click="loginType = 'admin'" 
                            :class="loginType === 'admin' ? 'bg-green-600 text-white shadow-md' : 'text-gray-300 hover:text-white'" 
                            class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all duration-300 focus:outline-none">
                        Login Admin
                    </button>
                </div>

                <!-- Form Login -->
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <!-- Alert Peringatan Error -->
                    @if($errors->any())
                        <div class="mb-4 bg-red-500/80 border border-red-500 text-white px-4 py-3 rounded-xl relative" role="alert">
                            <span class="block sm:inline text-sm font-medium">{{ $errors->first() }}</span>
                        </div>
                    @endif
                    <!-- Dropdown Role (HANYA MUNCUL JIKA TAB STAFF DIPILIH) -->
                    <div x-show="loginType === 'staff'" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mb-4">
                        <label class="block text-sm font-medium text-gray-200 mb-1">Role</label>
                        <!-- Pastikan input hidden untuk role admin dikirim otomatis jika di tab admin -->
                        <select name="role" x-bind:disabled="loginType === 'admin'" class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 text-white focus:bg-white/30 focus:ring-2 focus:ring-green-500 outline-none transition-all appearance-none">
                            <option value="POS" class="text-gray-800">POS</option>
                            <option value="Manager" class="text-gray-800">Manager</option>
                        </select>
                    </div>
                    
                    <!-- Input Hidden Role Admin (Aktif saat tab Admin) -->
                    <input type="hidden" name="role" value="Superadmin" x-bind:disabled="loginType === 'staff'">

                    <!-- Field Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-200 mb-1">Email</label>
                        <input type="email" name="email" required placeholder="email@contoh.com" 
                               class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 text-white placeholder-gray-400 focus:bg-white/30 focus:ring-2 focus:ring-green-500 outline-none transition-all">
                    </div>

                    <!-- Field Password -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-200 mb-1">Password</label>
                        <input type="password" name="password" required placeholder="••••••••" 
                               class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 text-white placeholder-gray-400 focus:bg-white/30 focus:ring-2 focus:ring-green-500 outline-none transition-all">
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform transition-all hover:scale-[1.02] active:scale-95 flex justify-center items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login Sekarang
                    </button>
                </form>
                
                <!-- Chat Admin Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-300">
                        Butuh Bantuan? <a href="#" class="text-green-400 font-bold hover:text-green-300 hover:underline transition-colors">Chat Admin</a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>