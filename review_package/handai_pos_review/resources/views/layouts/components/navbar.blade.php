<!-- resources/views/layouts/components/navbar.blade.php -->
<div x-data="{ isMobile: window.innerWidth < 768 }"
    x-init="window.addEventListener('resize', () => isMobile = window.innerWidth < 768)">
    <div
        class="flex pt-4 left-1/2 transform items-center text-dark  bg-gradient-to-b from-transparent to-transparent backdrop-blur-sm">
        <div class="bs-container">
            <div class="navbar bg-base-100 shadow-md rounded-lg">
                <div class="navbar-start">
                    <div class="dropdown">
                        <div tabindex="0" role="button" class="btn btn-ghost " :class="isMobile ? '' : 'hidden'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                        </div>
                        <ul tabindex="0"
                            class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                            <li><a>Homepage</a></li>
                            <li><a>Portfolio</a></li>
                            <li><a>About</a></li>
                        </ul>
                    </div>
                    <a href="/" class="flex items-center space-x-2 " :class="isMobile ? 'hidden' : 'ms-6'">
                        <!-- Ganti logo berdasarkan ukuran layar -->
                        <img src="{{ asset('assets/svg/handai-logo-filled.svg') }}" alt="Handai Logo"
                            class="h-8 w-auto">
                        <span x-show="!isMobile" class="text-lg font-bold  text-gray-800">Handai Coffee</span>
                    </a>
                </div>
                <div class="navbar-center">
                    <a href="/" class="flex items-center space-x-2 " :class="isMobile ? '' : 'hidden'">
                        <!-- Ganti logo berdasarkan ukuran layar -->
                        <img src="{{ asset('assets/svg/handai-logo-filled.svg') }}" alt="Handai Logo"
                            class="h-8 w-auto">
                        <span x-show="!isMobile" class="text-lg font-bold  text-gray-800">Handai Coffee</span>
                    </a>
                </div>
                <div class="navbar-end gap-2">
                    <button class="btn btn-ghost btn-circle">
                        <div class="indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="badge badge-xs badge-primary indicator-item"></span>
                        </div>
                    </button>
                    <a href="/login" class="btn btn-primary">
                        <i class="ti ti-login"></i>
                        <p :class="isMobile ? 'hidden' : ''">Login</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>