<!DOCTYPE html>
<html lang="id" x-data="{ theme: 'light' }" :data-theme="theme" x-init="
    if (!localStorage.getItem('theme')) {
        localStorage.setItem('theme', 'light');  
    }
    theme = localStorage.getItem('theme') || 'light';  
    $watch('theme', value => localStorage.setItem('theme', value));">



<head>
    <style>
        html {
            --theme: light;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                open: true
            });
        });
    </script>
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @yield('vendor-style')
    @yield('page-style')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body class="bg-gray-100 text-gray-900">
    <div class="flex">
        @include('handai-pos.layouts.components.sidebar')

        <div class="flex-1 flex flex-col min-h-screen">
            <div class="sticky top-0 z-10">
                @include('handai-pos.layouts.components.navbar')
            </div>

            <main class="">
                <div class="">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @yield('vendor-script')
    @yield('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const savedTheme = localStorage.getItem('theme') || 'light';

            document.documentElement.setAttribute('data-theme', savedTheme);

            const themeObserver = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === 'data-theme') {
                        localStorage.setItem('theme', document.documentElement.getAttribute('data-theme'));
                    }
                });
            });

            themeObserver.observe(document.documentElement, {
                attributes: true
            });
        });

    </script>
</body>

</html>