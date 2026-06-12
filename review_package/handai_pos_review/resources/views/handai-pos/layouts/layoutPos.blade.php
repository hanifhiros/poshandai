<!DOCTYPE html>
<html lang="id">
<script>
    // Flash prevention: set theme before render
    (function() {
        var d = localStorage.getItem('pos_dark_mode') === 'true';
        document.documentElement.setAttribute('data-theme', d ? 'posdark' : 'light');
    })();
</script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Handai POS')</title>

    {{-- Alpine.js --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- LocalForage for offline persistence (IndexedDB) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>

    {{-- Vite Assets --}}
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @vite('resources/css/handai-pos-layout.css')

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    @yield('page-style')

    {{-- Dark Mode Styles --}}
    @include('handai-pos.layouts.components.dark-mode-styles')
</head>

<body class="bg-slate-50 text-slate-800 antialiased">
    <!-- Skip links removed (keyboard navigation handled via focus traps and explicit tabindex) -->

    <div class="flex h-screen w-screen overflow-hidden" x-data="posApp()" x-cloak @keydown.window="handleGlobalShortcut($event)">

        {{-- Sidebar --}}
        @include('handai-pos.layouts.components.sidebar-pos')

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Header Info Bar --}}
            @yield('header')

            {{-- Page Content --}}
            <div class="flex-1 flex overflow-hidden pb-14 md:pb-0">
                @yield('content')
            </div>
        </div>

        {{-- Mobile Bottom Navigation - visible only on small screens --}}
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-slate-200 flex items-center justify-around h-14 px-2 safe-area-bottom">
            <a href="{{ route('pos.dashboard') }}"
               class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 {{ request()->routeIs('pos.dashboard') ? 'text-[#0C9044]' : 'text-slate-400' }}">
                <i class="ti ti-device-desktop text-xl"></i>
                <span class="text-[10px] font-medium">POS</span>
            </a>
            <a href="{{ route('pos.checkout') }}"
               class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 {{ request()->routeIs('pos.checkout') ? 'text-[#0C9044]' : 'text-slate-400' }}">
                <i class="ti ti-shopping-cart text-xl"></i>
                <span class="text-[10px] font-medium">Keranjang</span>
            </a>
            <a href="{{ route('pos.history') }}"
               class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 {{ request()->routeIs('pos.history') ? 'text-[#0C9044]' : 'text-slate-400' }}">
                <i class="ti ti-history text-xl"></i>
                <span class="text-[10px] font-medium">Riwayat</span>
            </a>
            <button onclick="document.documentElement.getAttribute('data-theme') === 'posdark' ? (localStorage.setItem('pos_dark_mode','false'), document.documentElement.setAttribute('data-theme','light')) : (localStorage.setItem('pos_dark_mode','true'), document.documentElement.setAttribute('data-theme','posdark'))"
                    class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 text-slate-400 cursor-pointer">
                <i class="ti ti-sun-moon text-xl"></i>
                <span class="text-[10px] font-medium">Tema</span>
            </button>
        </nav>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none"></div>
    {{-- ARIA live region for screen readers (toasts announced here) --}}
    <div id="a11y-live" aria-live="polite" aria-atomic="true" class="sr-only"></div>

    @yield('page-script')

    <script>
        // ===== SOUND FEEDBACK SYSTEM =====
        const PosSound = {
            ctx: null,
            enabled: localStorage.getItem('pos_sound') !== 'false',
            getCtx() {
                if (!this.ctx) this.ctx = new (window.AudioContext || window.webkitAudioContext)();
                return this.ctx;
            },
            play(freq, duration, type = 'sine', vol = 0.15) {
                if (!this.enabled) return;
                try {
                    const ctx = this.getCtx();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = type;
                    osc.frequency.value = freq;
                    gain.gain.value = vol;
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + duration);
                } catch(e) {}
            },
            addToCart() { this.play(880, 0.12); setTimeout(() => this.play(1100, 0.1), 80); },
            removeItem() { this.play(440, 0.15, 'triangle'); },
            error() { this.play(200, 0.3, 'square', 0.08); },
            checkout() { this.play(660, 0.1); setTimeout(() => this.play(880, 0.1), 100); setTimeout(() => this.play(1100, 0.15), 200); },
            scan() { this.play(1200, 0.08); },
            toggle() { this.enabled = !this.enabled; localStorage.setItem('pos_sound', this.enabled); return this.enabled; }
        };

        // ===== MULTI-LANGUAGE SYSTEM =====
        var posLang = localStorage.getItem('pos_lang') || 'id';
        const PosI18n = {
            id: {
                cart: 'Keranjang', empty_cart: 'Keranjang kosong', pick_product: 'Pilih produk untuk memulai transaksi',
                delete_all: 'Hapus Semua', subtotal: 'Subtotal', discount: 'Diskon', total: 'Total',
                checkout: 'Checkout', search: 'Cari produk...', all: 'Semua', promo: 'Promo', favorite: 'Favorit',
                variant: 'Varian', quantity: 'Jumlah', stock: 'Stok', add_to_cart: 'Tambah ke Cart',
                cancel: 'Batal', note: 'Catatan Item', note_placeholder: 'Contoh: tanpa gula, extra shot, dll...',
                confirm_clear: 'Hapus Semua Item?', confirm_clear_desc: 'Semua item dalam keranjang akan dihapus.',
                products_found: 'produk ditemukan', search_result: 'Hasil pencarian',
                available: 'Tersedia', sold_out: 'Stok Habis', shift_open: 'Shift Aktif', shift_closed: 'Shift Tutup',
                sound: 'Sound', shortcut: 'Shortcut', outlet: 'Outlet', cashier: 'Kasir',
                no_products: 'Tidak ada produk ditemukan', show_all: 'Tampilkan semua produk',
                item_removed: 'Item dihapus dari keranjang', item_restored: 'dikembalikan', undo: 'Urungkan', cart_cleared: 'Keranjang berhasil dikosongkan',
                added_to_cart: 'ditambahkan ke keranjang', add_failed: 'Gagal menambahkan ke keranjang',
                cart_empty_warning: 'Keranjang masih kosong',
            },
            en: {
                cart: 'Cart', empty_cart: 'Cart is empty', pick_product: 'Select a product to start',
                delete_all: 'Clear All', subtotal: 'Subtotal', discount: 'Discount', total: 'Total',
                checkout: 'Checkout', search: 'Search products...', all: 'All', promo: 'Promo', favorite: 'Favorites',
                variant: 'Variant', quantity: 'Quantity', stock: 'Stock', add_to_cart: 'Add to Cart',
                cancel: 'Cancel', note: 'Item Note', note_placeholder: 'e.g. no sugar, extra shot, etc...',
                confirm_clear: 'Clear All Items?', confirm_clear_desc: 'All cart items will be removed.',
                products_found: 'products found', search_result: 'Search results',
                available: 'Available', sold_out: 'Sold Out', shift_open: 'Shift Active', shift_closed: 'Shift Closed',
                sound: 'Sound', shortcut: 'Shortcut', outlet: 'Outlet', cashier: 'Cashier',
                no_products: 'No products found', show_all: 'Show all products',
                item_removed: 'Item removed from cart', item_restored: 'restored', undo: 'Undo', cart_cleared: 'Cart cleared successfully',
                added_to_cart: 'added to cart', add_failed: 'Failed to add to cart',
                cart_empty_warning: 'Cart is empty',
            }
        };
        function t(key) { return (PosI18n[posLang] && PosI18n[posLang][key]) || key; }
        function toggleLang() {
            posLang = posLang === 'id' ? 'en' : 'id';
            localStorage.setItem('pos_lang', posLang);
            showToast(posLang === 'id' ? 'Bahasa: Indonesia' : 'Language: English', 'info', 1500);
            // Reload to apply translations
            window.location.reload();
        }

        // Global toast function
        function showToast(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toast-container');
            // announce to screen readers
            try { document.getElementById('a11y-live').textContent = message; } catch(e){}
            const toast = document.createElement('div');

            const icons = {
                success: 'ti-circle-check',
                error: 'ti-alert-circle',
                warning: 'ti-alert-triangle',
                info: 'ti-info-circle'
            };
            const isDark = document.documentElement.getAttribute('data-theme') === 'posdark';
            const colors = {
                success: 'bg-[#0C9044]',
                error: 'bg-red-500',
                warning: 'bg-amber-500',
                info: isDark ? 'bg-[#30363d]' : 'bg-[#3A3A3A]'
            };

            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-white text-sm font-medium ${colors[type] || colors.success} toast-enter`;
            toast.innerHTML = `<i class="ti ${icons[type] || icons.success} text-lg"></i><span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-leave');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Toast with Undo action
        function showUndoToast(message, undoCallback, duration = 7000) {
            const container = document.getElementById('toast-container');
            try { document.getElementById('a11y-live').textContent = message; } catch(e){}
            const toast = document.createElement('div');

            const icons = {
                success: 'ti-circle-check',
                error: 'ti-alert-circle',
                warning: 'ti-alert-triangle',
                info: 'ti-info-circle'
            };
            const isDark = document.documentElement.getAttribute('data-theme') === 'posdark';
            const colors = {
                success: 'bg-[#0C9044]',
                error: 'bg-red-500',
                warning: 'bg-amber-500',
                info: isDark ? 'bg-[#30363d]' : 'bg-[#3A3A3A]'
            };

            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-white text-sm font-medium ${colors.info} toast-enter`;
            toast.innerHTML = `<i class="ti ${icons.info} text-lg"></i><span class="flex-1">${message}</span><button class="ml-2 text-xs font-semibold underline undo-btn">${t('undo')}</button>`;
            container.appendChild(toast);

            const btn = toast.querySelector('.undo-btn');
            const remove = () => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-leave');
                setTimeout(() => toast.remove(), 300);
            };

            btn.addEventListener('click', (e) => {
                try { undoCallback && undoCallback(); } catch (err) { console.error(err); }
                remove();
            });

            setTimeout(() => remove(), duration);
        }

        // Main POS app state (sidebar + dark mode)
        function posApp() {
            return {
                sidebarOpen: localStorage.getItem('pos_sidebar') !== 'false',
                darkMode: localStorage.getItem('pos_dark_mode') === 'true',
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('pos_dark_mode', this.darkMode);
                    document.documentElement.setAttribute('data-theme', this.darkMode ? 'posdark' : 'light');
                },
                handleGlobalShortcut(e) {
                    // Alt+D: Toggle dark mode
                    if (e.altKey && (e.key === 'd' || e.key === 'D')) {
                        e.preventDefault();
                        this.toggleDarkMode();
                    }
                    // Alt+L: Toggle language
                    if (e.altKey && (e.key === 'l' || e.key === 'L')) {
                        e.preventDefault();
                        toggleLang();
                    }
                },
                init() {
                    // Listen for language toggle from sidebar
                    this.$el.addEventListener('toggle-lang', () => toggleLang());
                }
            };
        }

        // Register Service Worker for offline support
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw-pos.js').catch(() => {});
        }
    </script>
</body>

</html>
