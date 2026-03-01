{{-- Slim professional POS sidebar --}}
@php use App\Helpers\RoleHelper; @endphp

<aside
    :class="sidebarOpen
        ? 'w-[220px]'
        : 'w-[68px]'"
    class="sidebar-transition h-screen hidden md:flex flex-col bg-white border-r border-slate-200/80 shrink-0 select-none z-30">

    {{-- Brand / Logo --}}
    <div class="flex items-center h-[60px] px-3 border-b border-slate-100">
        <img src="{{ asset('assets/logo.png') }}" alt="Handai Coffee" class="w-10 h-10 rounded-xl object-contain shrink-0">
        <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-3 overflow-hidden whitespace-nowrap">
            <p class="text-sm font-bold text-slate-800 leading-tight">Handai Coffee</p>
            <p class="text-[10px] text-slate-400 font-medium">Point of Sale</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto pos-scroll py-3 px-2 space-y-1">

        {{-- POS --}}
        <a href="{{ route('pos.dashboard') }}"
           class="group flex items-center h-10 rounded-lg px-2 transition-all duration-150
                  {{ request()->routeIs('pos.dashboard') || request()->routeIs('products.index') ? 'bg-green-50 text-[#0C9044]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="ti ti-device-desktop text-lg"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap">
                POS
            </span>
        </a>

        {{-- Keranjang --}}
        <a href="{{ route('pos.checkout') }}"
           class="group flex items-center h-10 rounded-lg px-2 transition-all duration-150
                  {{ request()->routeIs('pos.checkout') ? 'bg-green-50 text-[#0C9044]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="ti ti-shopping-cart text-lg"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap">
                Keranjang
            </span>
        </a>

        {{-- Riwayat Transaksi --}}
        <a href="{{ route('pos.history') }}"
           class="group flex items-center h-10 rounded-lg px-2 transition-all duration-150
                  {{ request()->routeIs('pos.history') ? 'bg-green-50 text-[#0C9044]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="ti ti-history text-lg"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap">
                Riwayat
            </span>
        </a>

        @if (RoleHelper::hasRole('Superadmin'))
        <div class="pt-3 pb-1">
            <div x-show="sidebarOpen" x-transition.opacity class="border-t border-slate-100"></div>
            <div x-show="!sidebarOpen" x-transition.opacity class="border-t border-slate-100 mx-1"></div>
        </div>

        <a href="{{ route('superadmin.dashboard') }}"
           class="group flex items-center h-10 rounded-lg px-2 transition-all duration-150 text-slate-500 hover:bg-slate-50 hover:text-slate-700">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="ti ti-shield-lock text-lg"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap">
                Superadmin
            </span>
        </a>
        @endif
    </nav>

    {{-- Bottom Actions --}}
    <div class="border-t border-slate-100 p-2 space-y-1">
        {{-- Language Toggle --}}
        <button @click="$dispatch('toggle-lang')"
                class="flex items-center h-10 w-full rounded-lg px-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all duration-150 cursor-pointer"
                title="Switch Language (ID/EN)">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="ti ti-language text-lg"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap"
                  x-text="(typeof posLang !== 'undefined' && posLang === 'en') ? 'English' : 'Indonesia'">
            </span>
        </button>

        {{-- Dark Mode Toggle --}}
        <button @click="toggleDarkMode()"
                class="flex items-center h-10 w-full rounded-lg px-2 transition-all duration-150 cursor-pointer"
                :class="darkMode ? 'text-amber-400 hover:bg-amber-500/10' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="text-lg transition-transform duration-300" :class="darkMode ? 'ti ti-sun' : 'ti ti-moon'"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap" x-text="darkMode ? 'Light Mode' : 'Dark Mode'">
            </span>
        </button>

        {{-- Collapse Toggle --}}
        <button @click="sidebarOpen = !sidebarOpen; localStorage.setItem('pos_sidebar', sidebarOpen)"
                class="flex items-center h-10 w-full rounded-lg px-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-all duration-150 cursor-pointer">
            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                <i class="ti ti-chevrons-left text-lg transition-transform duration-300"
                   :class="{ 'rotate-180': !sidebarOpen }"></i>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap">
                Collapse
            </span>
        </button>

        {{-- Logout --}}
        <form action="{{ route('universal.logout') }}" method="POST">
            @csrf
            <button type="submit"
                    class="flex items-center h-10 w-full rounded-lg px-2 text-red-400 hover:bg-red-50 hover:text-red-600 transition-all duration-150 cursor-pointer">
                <div class="w-8 h-8 flex items-center justify-center shrink-0">
                    <i class="ti ti-logout text-lg"></i>
                </div>
                <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="ml-2 text-sm font-medium whitespace-nowrap">
                    Logout
                </span>
            </button>
        </form>
    </div>
</aside>
