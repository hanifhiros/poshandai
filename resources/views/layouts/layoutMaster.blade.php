<!DOCTYPE html>
<html lang="id" x-data="globalLoading" x-init="init()" :data-theme="theme" x-cloak>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    {{-- Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Vite --}}
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @yield('vendor-style')
    @yield('page-style')

    <style>
        #global-loading {
            transition: opacity 0.4s ease;
            z-index: 9999;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900">

    {{-- 🔄 Global Loading --}}
    <div id="global-loading"
         x-show="loading"
         x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-white bg-opacity-80">
        <dotlottie-player
            src="{{ asset('animations/loading.json') }}"
            background="transparent"
            speed="1"
            style="width: 220px; height: 220px"
            loop
            autoplay>
        </dotlottie-player>
    </div>

    {{-- Navbar --}}
    <div class="fixed top-0 left-0 right-0 z-50">
        @include('layouts.components.navbar')
    </div>

    {{-- Content --}}
    <div class="pt-[4rem]">
        @yield('content')
    </div>

    {{-- Scripts --}}
    @yield('vendor-script')
    @yield('page-script')

    {{-- DotLottie --}}
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('globalLoading', () => ({
                theme: localStorage.getItem('theme') || 'light',
                loading: true,
                init() {
                    const MINIMUM_DURATION = 2500; // minimum durasi tampil loading
                    this.$watch('theme', val => localStorage.setItem('theme', val));

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

                    // PENTING: kirim loading-end setelah Alpine sudah aktif
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

