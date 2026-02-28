<!-- resources/views/layouts/components/navbar.blade.php -->
<div x-data="{ isMobile: window.innerWidth < 768 }" x-init="window.addEventListener('resize', () => isMobile = window.innerWidth < 768)">
    <header
        class="w-full p-4 transition-all duration-300 bg-[linear-gradient(to_top,rgba(255,255,255,0),rgba(255,255,255,0.9),rgba(255,255,255,1),rgba(255,255,255,1))] ">

        <nav class="flex items-center justify-between space-x-2">

            <!-- Kiri: Tombol Dummy -->
            <button class="btn btn-soft btn-primary flex flex-col items-center justify-center gap-0 m-2">
                <i class="ti ti-building-store text-lg"></i>
                <span class="text-xs m-0">
                    {{ $selected_store?->store_name ?? 'Semua Toko' }}
                </span>
            </button>
            


            <!-- Tengah: Search Bar (hanya muncul di md ke atas) -->
           

            <!-- Kanan: Logo Handai -->
            <div class="flex items-center">
                <img :src="isMobile ? '{{ asset('assets/svg/handai-text-logo-wrapped.svg') }}' : '{{ asset('assets/svg/handai-text-logo.svg') }}'" 
                     alt="Handai Logo" class=" w-auto ml-2" :class="isMobile ? 'h-12 min-h-12 min-w-16' : 'h-8 min-h-auto'" />
            </div>
        </nav>
    </header>
</div>
