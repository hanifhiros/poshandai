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

    <!-- Alpine & Lottie -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.0/lottie.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Plugin & Styling -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>

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
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-select/bootstrap-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
</head>

<body class="bg-gray-100 text-gray-900">

    <!-- Global Loading Overlay -->
    <!-- Global Loading Overlay -->
    {{-- <div x-show="loading" x-transition.opacity
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-white bg-opacity-80" style="display: none">
        <div id="lottie-loading" class="w-60 h-60"></div> <!-- Ukuran diperbesar -->
    </div> --}}
    <!-- Global Loading Overlay -->
    <div x-show="loading" x-transition.opacity
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-100 bg-opacity-10" style="display: none">
        <div id="lottie-loading" class="w-60 h-60"></div> <!-- Ukuran diperbesar -->
    </div>



    <div class="flex min-h-screen w-full bg-gray-100">
        @include('handai-manager.layouts.components.sidebar')

        <div class="flex-1 flex flex-col min-h-screen w-full">
            <div class="sticky top-0 z-10">
                @include('handai-manager.layouts.components.navbar')
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

    <script>
        // Trigger resize ulang saat kembali via ALT + ← atau → (back/forward navigation)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    window.dispatchEvent(new Event('loading-end'));
                }, 100); // kasih delay agar layout sempat stabil dulu
            }
        });

        // Opsional: bisa pakai event resize untuk menyesuaikan elemen responsif
        window.addEventListener('resize', () => {
            const width = window.innerWidth;

            // Contoh logika: update max-width dashboard secara dinamis
            const dashboardContainer = document.querySelector('.max-w-screen-xl');
            if (dashboardContainer) {
                if (width < 640) {
                    dashboardContainer.classList.remove('max-w-screen-xl');
                    dashboardContainer.classList.add('max-w-screen-sm');
                } else if (width < 1024) {
                    dashboardContainer.classList.remove('max-w-screen-xl');
                    dashboardContainer.classList.add('max-w-screen-md');
                } else {
                    dashboardContainer.classList.remove('max-w-screen-sm', 'max-w-screen-md');
                    dashboardContainer.classList.add('max-w-screen-xl');
                }
            }
        });

        // Trigger resize saat halaman load pertama kali
        window.dispatchEvent(new Event('resize'));


        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                window.dispatchEvent(new Event('loading-end'));
            }, 2000); // 2s fallback to hide loading spinner
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                setTimeout(() => {
                    window.dispatchEvent(new Event('loading-end'));
                }, 500); // quick recovery on back/forward nav
            }
        });


        document.addEventListener("DOMContentLoaded", () => {
            // Load Lottie animation
            lottie.loadAnimation({
                container: document.getElementById('lottie-loading'),
                renderer: 'svg',
                loop: true,
                autoplay: true,
                path: "{{ asset('animations/loading.json') }}"
            });

            // Link click triggers loading
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function (e) {
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

            // Select2 Init
            $('.handai-select').select2();
        });
    </script>





</body>

</html>