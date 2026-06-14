<!DOCTYPE html>
<html lang="id" x-data="{ theme: 'light', loading: false }" :data-theme="theme" x-init="
        theme = localStorage.getItem('theme') || 'light';
        $watch('theme', value => localStorage.setItem('theme', value));
        window.addEventListener('loading-start', () => loading = true);
        window.addEventListener('loading-end', () => loading = false);
        window.addEventListener('beforeunload', () => loading = true);
      " x-cloak>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Plugin & Styling -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @vite('resources/js/app.js')
    @vite('resources/css/app.css')

    @yield('vendor-style')
    @yield('page-style')

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body class="bg-gray-100 text-gray-900">

    <!-- Global Loading Overlay -->
    <div x-show="loading"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/70 backdrop-blur-[2px]" style="display: none">
        <dotlottie-player
            src="{{ asset('animations/loading.json') }}"
            background="transparent"
            speed="1"
            style="width: 180px; height: 180px"
            class="transition-transform duration-200 ease-out"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            loop
            autoplay>
        </dotlottie-player>
    </div>



    <div class="flex min-h-screen w-full bg-gray-100">
        @include('layouts.components.sidebar')

        <div class="flex-1 flex flex-col min-h-screen w-full">
            <div class="sticky top-0 z-10">
                @include('layouts.components.navbar')
            </div>

            <main>
                <div>
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @yield('vendor-script')
    @yield('page-script')
    @stack('scripts')

    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.js"></script>
    <script>
        // Responsive dashboard container
        window.addEventListener('resize', () => {
            const dc = document.querySelector('.max-w-screen-xl');
            if (!dc) return;
            const w = window.innerWidth;
            dc.classList.remove('max-w-screen-xl', 'max-w-screen-sm', 'max-w-screen-md');
            dc.classList.add(w < 640 ? 'max-w-screen-sm' : (w < 1024 ? 'max-w-screen-md' : 'max-w-screen-xl'));
        });
        window.dispatchEvent(new Event('resize'));

        // Back/forward nav recovery
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    window.dispatchEvent(new Event('loading-end'));
                }, 100);
            }
        });

        // DOMContentLoaded â€” loading triggers + fallback
        document.addEventListener('DOMContentLoaded', () => {
            // Fallback: hide spinner after 2s if page didn't fire loading-end
            setTimeout(() => window.dispatchEvent(new Event('loading-end')), 2000);

            // Link clicks trigger loading overlay
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('javascript') && this.target !== '_blank') {
                        window.dispatchEvent(new Event('loading-start'));
                    }
                });
            });

            // Form submits trigger loading (skip Alpine @submit.prevent)
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', (e) => {
                    if (e.defaultPrevented) return;
                    window.dispatchEvent(new Event('loading-start'));
                });
            });
        });
    </script>





</body>

</html>
