<!-- resources/views/layouts/components/navbar.blade.php -->
<div x-data="{ isMobile: window.innerWidth < 768 }" x-init="window.addEventListener('resize', () => isMobile = window.innerWidth < 768)">
    <header
        class="w-full p-4 transition-all duration-300 bg-[linear-gradient(to_top,rgba(255,255,255,0),rgba(255,255,255,0.9),rgba(255,255,255,1),rgba(255,255,255,1))] ">

        <nav class="flex items-center justify-between space-x-2">

            <!-- Kiri: Tombol Dummy -->
            <button class="btn btn-soft btn-primary flex flex-col items-center justify-center gap-0 m-2">
                <i class="ti ti-building-store text-lg"></i>
                <span class="text-xs m-0">
                    {{ $selected_store ? $selected_store->store_name : 'No Store Selected' }}
                </span>
            </button>


            <!-- Tengah: Search Bar (hanya muncul di md ke atas) -->
            <form action="{{ route('pos.dashboard') }}" method="GET" class=" w-full">
                <input type="hidden" name="category" value="{{ $categoryName }}">
                <label class="input input-primary w-full">
                    <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </g>
                    </svg>
                    <input type="search" name="search" class="grow" placeholder="Search" value="{{ $searchTerm ?? '' }}" />
                    <kbd class="kbd kbd-sm">⌘</kbd>
                    <kbd class="kbd kbd-sm">K</kbd>
                </label>
            </form>


            <!-- Kanan: Logo Handai -->
            <div class="flex items-center">
                <img :src="isMobile ? '{{ asset('assets/svg/handai-text-logo-wrapped.svg') }}' : '{{ asset('assets/svg/handai-text-logo.svg') }}'" 
                     alt="Handai Logo" class=" w-auto ml-2" :class="isMobile ? 'h-12 min-h-12 min-w-16' : 'h-8 min-h-auto'" />
            </div>
        </nav>
    </header>
</div>
