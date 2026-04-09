<!DOCTYPE html>
<html lang="id"
      x-data="globalLoading"
      x-init="init()"
      x-cloak
      :data-theme="theme"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    <!-- Alpine.js & DotLottie -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @vite('resources/js/app.js')
    @vite('resources/css/app.css')


    @yield('vendor-style')
    @yield('page-style')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<style>[x-cloak] { display: none !important; }</style>

<body class="bg-gray-100 text-gray-900">

<!-- 🔄 Global Loading -->
<div id="global-loading-overlay"
     x-show="loading"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="absolute inset-0 z-[9999] flex items-center justify-center bg-white bg-opacity-80"
     style="display: none">
    <dotlottie-player
        src="{{ asset('animations/loading.json') }}"
        background="transparent"
        speed="1"
        style="width: 220px; height: 220px"
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


<main class="w-full min-h-screen">
    @yield('content')
</main>

@yield('vendor-script')
@yield('page-script')
@stack('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('globalLoading', () => ({
            theme: localStorage.getItem('theme') || 'light',
            loading: true,
            init() {
                const MINIMUM_DURATION = 1500; // minimum durasi tampil loading
                this.$watch('theme', value => {
                    localStorage.setItem('theme', value);
                });

                const start = Date.now();
                const finishLoading = () => {
                    const elapsed = Date.now() - start;
                    const remaining = Math.max(0, MINIMUM_DURATION - elapsed);
                    setTimeout(() => {
                        this.loading = false;
                    }, remaining);
                };

                window.addEventListener('loading-start', () => this.loading = true);
                window.addEventListener('loading-end', finishLoading);
                window.addEventListener('beforeunload', () => this.loading = true);
                window.addEventListener('pageshow', (event) => {
                    if (event.persisted) {
                        finishLoading();
                    }
                });

                window.addEventListener('load', () => {
                    window.dispatchEvent(new Event('loading-end'));
                });
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        const shouldTrigger = (href, target) => {
            return href && !href.startsWith('#') && !href.startsWith('javascript:') && target !== '_blank';
        };

        const startLoading = () => window.dispatchEvent(new Event('loading-start'));

        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (shouldTrigger(link.getAttribute('href'), link.target)) {
                    startLoading();
                }
            });
        });

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                startLoading();
            });
        });
    });
    </script>
    
    

</body>
</html>  




{{-- <!DOCTYPE html>
<html lang="id" x-data x-cloak :data-theme="$store.main.theme">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- Alpine.js & Lottie -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.0/lottie.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @vite('resources/js/app.js')
    @vite('resources/css/app.css')


    @yield('vendor-style')
    @yield('page-style')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">



    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('main', {
                theme: localStorage.getItem('theme') || 'light',
                loading: false,
                init() {
                    this.theme = localStorage.getItem('theme') || 'light';
                }
            });
        });
    </script>

    <!-- Tambahkan script lottie setelah Alpine -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.0/lottie.min.js"></script>
</head>

<body x-data x-init="$store.main.init()" :data-theme="$store.main.theme">


    
    
<!-- 🔄 Global Loading -->
<!-- 🔄 Global Loading -->
<div id="global-loading-overlay"
     x-show="$store.main.loading"
     x-transition.opacity
     class="absolute inset-0 z-[9999] flex items-center justify-center bg-white bg-opacity-80">
    <div id="lottie-loading" class="w-48 h-48"></div>
</div>



<main class="w-full min-h-screen">
    {{-- <button onclick="Alpine.store('main').loading = true"
    class="fixed bottom-5 right-5 bg-blue-600 text-white px-4 py-2 rounded-md z-[99999]">
Tes Loading
</button> --}}

    
    
    {{-- @yield('content')
    
</main>

@yield('vendor-script')
@yield('page-script')
@stack('scripts')
<script>
    window.addEventListener('loading-start', () => {});
    window.addEventListener('loading-end', () => {});

    document.addEventListener('DOMContentLoaded', () => {
        const loader = document.getElementById('lottie-loading');
        if (loader) {
            lottie.loadAnimation({
                container: loader,
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: "{{ asset('animations/loading.json') }}"
            });
        }
    
        const MIN_DURATION = 1500; // Minimal 1.5 detik tampil
        const MAX_DURATION = 10000; // Maksimal 10 detik loading
        let loadingStartTime = null;
        let loadingTimeout = null;
    
        const startLoading = () => {
    if (!loadingStartTime) {
        loadingStartTime = Date.now();
        Alpine.store('main').loading = true; // ✅ PENTING
        window.dispatchEvent(new Event('loading-start'));
        loadingTimeout = setTimeout(() => {
            endLoading();
        }, MAX_DURATION);
    }
};

const endLoading = () => {
    if (loadingStartTime) {
        const elapsed = Date.now() - loadingStartTime;
        const remaining = Math.max(0, MIN_DURATION - elapsed);
        clearTimeout(loadingTimeout);
        setTimeout(() => {
            Alpine.store('main').loading = false; // ✅ PENTING
            window.dispatchEvent(new Event('loading-end'));
            loadingStartTime = null;
        }, remaining);
    }
};

        // ✅ Panggil loading-start begitu DOM siap
        startLoading();
    
        // Link click triggers loading
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function () {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && this.target !== '_blank') {
                    startLoading();
                }
            });
        });
    
        // Form submit triggers loading
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                startLoading();
            });
        });
    
        // ✅ End loading saat semua load selesai
        window.addEventListener('load', () => {
            endLoading();
        });
    
        // Handle back/forward cache
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                endLoading();
            }
        });
    
        // Manual force end loading (optional)
        window.addEventListener('loading-force-end', () => {
            endLoading();
        });
    });
    </script>
    
    

</body>
</html>   --}}