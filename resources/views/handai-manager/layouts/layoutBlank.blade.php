<!DOCTYPE html>
<html lang="id"
      x-data="{ theme: 'light', loading: false }"
      :data-theme="theme"
      x-init="
        theme = localStorage.getItem('theme') || 'light';
        $watch('theme', value => localStorage.setItem('theme', value));
        window.addEventListener('loading-start', () => loading = true);
        window.addEventListener('loading-end', () => loading = false);
        window.addEventListener('beforeunload', () => loading = true);
      "
      x-cloak
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

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

<body class="bg-gray-100 text-gray-900">

    <!-- Global Loading Overlay -->
    <div x-show="loading"
         x-transition.opacity
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-white bg-opacity-80"
         style="display: none">
        <dotlottie-player
            src="{{ asset('animations/loading.json') }}"
            background="transparent"
            speed="1"
            style="width: 220px; height: 220px"
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
           
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            window.dispatchEvent(new Event('loading-end'));
        }, 10000); // 300ms setelah halaman siap, baru kirim loading-end
    });

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            setTimeout(() => {
                window.dispatchEvent(new Event('loading-end'));
            }, 10000); // sama, pas back/forward dari cache juga kasih delay 300ms
        }
    });


        document.addEventListener("DOMContentLoaded", () => {
            // Link click triggers loading
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function () {
                    const href = this.getAttribute('href');
                    if (href && !href.startsWith('#') && this.target !== '_blank') {
                        window.dispatchEvent(new Event('loading-start'));
                    }
                });
            });

            // Form submit triggers loading
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', () => {
                    window.dispatchEvent(new Event('loading-start'));
                });
            });
        });
    </script>
</body>
</html>
