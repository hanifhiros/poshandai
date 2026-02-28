@extends('handai-pos.layouts.layoutPos')

@section('title', 'POS Dashboard — Handai')

@section('page-style')
<style>
    /* Category pill */
    .cat-pill {
        transition: all 0.15s ease;
        white-space: nowrap;
        user-select: none;
    }
    .cat-pill:hover:not(.cat-active) {
        background-color: #f1f5f9;
        color: #475569;
    }
    .cat-active {
        background-color: #0C9044;
        color: white;
        border-color: #0C9044;
    }

    /* Product grid card */
    .prod-card {
        transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
        cursor: pointer;
    }
    .prod-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0,0,0,0.07);
        border-color: rgba(12,144,68,0.3);
    }
    .prod-card:active {
        transform: translateY(0);
    }
    .prod-card.sold-out {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Cart item */
    .cart-item {
        transition: all 0.2s ease;
    }
    .cart-item:hover {
        background-color: #f8fafc;
    }

    /* Qty button */
    .qty-btn {
        transition: all 0.15s ease;
    }
    .qty-btn:hover:not(:disabled) {
        background-color: #ecfdf5;
        color: #0C9044;
    }
    .qty-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Checkout button glow */
    .checkout-btn {
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .checkout-btn:hover {
        box-shadow: 0 4px 20px rgba(12,144,68,0.35);
        transform: translateY(-1px);
    }
    .checkout-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(12,144,68,0.25);
    }

    /* Smooth fade for Alpine */
    [x-cloak] { display: none !important; }

    /* Cart badge bounce */
    @keyframes badgeBounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.25); }
    }
    .badge-bounce {
        animation: badgeBounce 0.3s ease-in-out;
    }

    /* Cart item slide-in animation */
    @keyframes cartSlideIn {
        from { opacity: 0; transform: translateX(20px); max-height: 0; }
        to { opacity: 1; transform: translateX(0); max-height: 200px; }
    }
    .cart-slide-in {
        animation: cartSlideIn 0.3s ease-out forwards;
    }

    /* Loading spinner */
    .pos-spinner {
        width: 16px; height: 16px;
        border: 2px solid rgba(12,144,68,0.2);
        border-top-color: #0C9044;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Mobile floating cart button */
    .mobile-cart-fab {
        animation: cartFabPulse 2s ease-in-out infinite;
    }
    @keyframes cartFabPulse {
        0%, 100% { box-shadow: 0 4px 15px rgba(12,144,68,0.3); }
        50% { box-shadow: 0 4px 25px rgba(12,144,68,0.5); }
    }

    /* Cart drawer panel */
    .cart-drawer-panel {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
@endsection

@section('header')
{{-- Top Info Bar --}}
<div class="h-[52px] bg-white border-b border-slate-200/80 flex items-center justify-between px-5 shrink-0">
    {{-- Left: Store info --}}
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center">
                <i class="ti ti-building-store text-[#0C9044] text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-700 leading-none">
                    {{ $selected_store ? $selected_store->store_name : 'No Store' }}
                </p>
                <p class="text-[10px] text-slate-400 leading-none mt-0.5">Outlet</p>
            </div>
        </div>
        <div class="w-px h-6 bg-slate-200"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center">
                <i class="ti ti-user text-emerald-600 text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-700 leading-none">
                    {{ Auth::user()->name ?? 'Kasir' }}
                </p>
                <p class="text-[10px] text-slate-400 leading-none mt-0.5">Kasir</p>
            </div>
        </div>
        <div class="w-px h-6 bg-slate-200"></div>
        {{-- Shift Indicator --}}
        <div x-data="shiftManager()" class="flex items-center gap-2">
            <button @click="toggleShift()"
                    class="flex items-center gap-1.5 h-7 px-2.5 rounded-lg text-[11px] font-semibold transition cursor-pointer"
                    :class="isOpen ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100'">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                          :class="isOpen ? 'bg-emerald-400' : 'bg-red-400'"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2"
                          :class="isOpen ? 'bg-emerald-500' : 'bg-red-500'"></span>
                </span>
                <span x-text="isOpen ? 'Shift Aktif' : 'Shift Tutup'"></span>
            </button>
            <span x-show="isOpen" class="text-[10px] text-slate-400" x-text="shiftDuration"></span>
        </div>
    </div>

    {{-- Center: Date & Time --}}
    <div class="hidden md:flex items-center gap-2" x-data="clock()" x-init="start()">
        <i class="ti ti-clock text-slate-400 text-base"></i>
        <span class="text-xs font-medium text-slate-500" x-text="time"></span>
        <span class="text-xs text-slate-300">|</span>
        <span class="text-xs text-slate-500" x-text="date"></span>
    </div>

    {{-- Right: Status + Sound + Shortcut --}}
    <div class="flex items-center gap-3">
        {{-- Sound Toggle --}}
        <button class="hidden sm:flex items-center gap-1 cursor-pointer hover:opacity-80 transition"
                @click="soundEnabled = PosSound.toggle(); showToast(soundEnabled ? 'Suara aktif' : 'Suara nonaktif', 'info', 1500)"
                :title="soundEnabled ? 'Matikan suara' : 'Nyalakan suara'">
            <i class="text-base" :class="soundEnabled ? 'ti ti-volume text-[#0C9044]' : 'ti ti-volume-off text-slate-400'"></i>
            <span x-show="soundEnabled" class="text-[10px] text-slate-400">Sound</span>
        </button>
        {{-- Help Shortcut --}}
        <div class="hidden sm:flex items-center gap-1 cursor-pointer hover:opacity-80 transition" @click="document.dispatchEvent(new CustomEvent('open-help'))" title="Buka panduan shortcut (F1)">
            <kbd class="kbd kbd-xs bg-slate-100 border-slate-200 text-slate-500">F1</kbd>
            <span class="text-[10px] text-slate-400">Shortcut</span>
        </div>
    </div>
</div>
@endsection

@section('content')
{{-- ========== MAIN 2-PANEL LAYOUT ========== --}}
<div class="flex flex-1 overflow-hidden" x-data="posStore()" x-init="init()" @keydown.window="handleShortcut($event)" @open-help.window="showHelp = true">

    {{-- ===== LEFT: Product Selection Area ===== --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Search + Category Bar --}}
        <div class="bg-white border-b border-slate-100 px-5 py-3 shrink-0">
            <div class="flex items-center gap-3 mb-3">
                {{-- Search --}}
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="ti text-lg" :class="barcodeMode ? 'ti-barcode text-[#0C9044]' : 'ti-search text-slate-400'"></i>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           x-ref="searchInput"
                           @input.debounce.300ms="filterProducts()"
                           @keydown="detectBarcode($event)"
                           @keydown.escape="searchQuery = ''; filterProducts()"
                           :placeholder="barcodeMode ? 'Mode Barcode — scan atau ketik SKU...' : 'Cari produk... (Ctrl+K)'"
                           class="w-full pl-10 pr-20 h-11 rounded-xl border text-sm font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50 transition"
                           :class="barcodeMode ? 'border-[#0C9044] bg-green-50/30' : 'border-slate-200 bg-slate-50/50'" />
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                        <button @click="barcodeMode = !barcodeMode; $refs.searchInput.focus()"
                                class="w-8 h-8 rounded-lg flex items-center justify-center transition cursor-pointer"
                                :class="barcodeMode ? 'bg-[#0C9044] text-white' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
                                title="Toggle Barcode Scanner Mode">
                            <i class="ti ti-barcode text-base"></i>
                        </button>
                        <button x-show="searchQuery.length > 0" @click="searchQuery = ''; filterProducts()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                            <i class="ti ti-x text-base"></i>
                        </button>
                    </div>
                </div>
                {{-- View toggle --}}
                <button class="h-11 w-11 rounded-xl border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-[#0C9044] hover:border-green-200 transition cursor-pointer"
                        @click="gridCols = gridCols === 4 ? 3 : gridCols === 3 ? 5 : 4"
                        title="Change grid size">
                    <i class="ti ti-layout-grid text-lg"></i>
                </button>
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-1">
                <button @click="activeCategory = 'All Products'; filterProducts()"
                        :class="activeCategory === 'All Products' ? 'cat-active' : ''"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 cursor-pointer">
                    <i class="ti ti-apps text-sm"></i> <span x-text="t('all')"></span>
                </button>
                <button @click="activeCategory = 'Favorit'; filterProducts()"
                        :class="activeCategory === 'Favorit' ? 'cat-active' : ''"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 cursor-pointer">
                    <i class="ti ti-star-filled text-sm text-amber-400"></i> <span x-text="t('favorite')"></span>
                </button>
                <button @click="activeCategory = 'Promo'; filterProducts()"
                        :class="activeCategory === 'Promo' ? 'cat-active' : ''"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 cursor-pointer">
                    <i class="ti ti-discount-2 text-sm"></i> <span x-text="t('promo')"></span>
                </button>
                @foreach($categories as $cat)
                <button @click="activeCategory = '{{ $cat->category_name }}'; filterProducts()"
                        :class="activeCategory === '{{ $cat->category_name }}' ? 'cat-active' : ''"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-500 cursor-pointer">
                    {{ $cat->category_name }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto pos-scroll p-5">
            {{-- Results info --}}
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs text-slate-400 font-medium">
                    <span x-text="filteredProducts.length"></span> produk ditemukan
                </p>
                <p x-show="searchQuery.length > 0" class="text-xs text-slate-400">
                    Hasil pencarian: "<span class="font-semibold text-slate-600" x-text="searchQuery"></span>"
                </p>
            </div>

            {{-- Grid --}}
            <div class="grid gap-3"
                 :class="{
                    'grid-cols-2 sm:grid-cols-3': gridCols === 3,
                    'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4': gridCols === 4,
                    'grid-cols-3 sm:grid-cols-4 lg:grid-cols-5': gridCols === 5
                 }">
                <template x-for="item in filteredProducts" :key="item.product.id">
                    <div class="prod-card bg-white rounded-xl border border-slate-200/80 overflow-hidden"
                         :class="{ 'sold-out': item.isSoldOut }"
                         @click="!item.isSoldOut && openVariantModal(item)">

                        {{-- Image --}}
                        <div class="relative aspect-square bg-slate-50 flex items-center justify-center p-3 overflow-hidden">
                            <img :src="item.product.image_url ? '{{ asset('') }}' + item.product.image_url : '{{ asset('assets/image.png') }}'"
                                 :alt="item.product.name"
                                 class="max-h-full max-w-full object-contain"
                                 onerror="this.src='{{ asset('assets/image.png') }}'">

                            {{-- Favorite star --}}
                            <button @click.stop="toggleFavorite(item.product.id)"
                                    class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center transition-all cursor-pointer"
                                    :class="isFavorite(item.product.id) ? 'bg-amber-100 text-amber-500 shadow-sm' : 'bg-white/80 text-slate-300 hover:text-amber-400 hover:bg-amber-50'"
                                    :title="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'">
                                <i class="text-sm" :class="isFavorite(item.product.id) ? 'ti ti-star-filled' : 'ti ti-star'"></i>
                            </button>

                            {{-- Promo badge --}}
                            <div x-show="item.isPromo === 'yes'" class="absolute top-2 left-2">
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-red-500 text-white text-[10px] font-bold uppercase tracking-wide">
                                    <i class="ti ti-discount-2 text-xs"></i> Promo
                                </span>
                            </div>

                            {{-- Sold out overlay --}}
                            <div x-show="item.isSoldOut" class="absolute inset-0 bg-white/70 flex items-center justify-center">
                                <span class="px-3 py-1 rounded-lg bg-slate-800 text-white text-xs font-bold" x-text="t('sold_out')"></span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="p-3 border-t border-slate-100">
                            <h4 class="text-sm font-semibold text-slate-800 truncate" x-text="item.product.name"></h4>
                            <div class="mt-1.5 flex items-baseline gap-1.5">
                                <span class="text-sm font-bold"
                                      :class="item.isPromo === 'yes' ? 'text-red-600' : 'text-[#3A3A3A]'"
                                      x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')">
                                </span>
                                <span x-show="item.isPromo === 'yes' && item.normal_price"
                                      class="text-[11px] text-slate-400 line-through"
                                      x-text="'Rp ' + Number(item.normal_price).toLocaleString('id-ID')">
                                </span>
                            </div>
                            {{-- Stock indicator --}}
                            <div class="mt-2 flex items-center gap-1" x-show="!item.isSoldOut">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                                <span class="text-[10px] text-slate-400 font-medium" x-text="t('available')"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty state --}}
            <div x-show="filteredProducts.length === 0" class="flex flex-col items-center justify-center py-20">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <i class="ti ti-coffee-off text-3xl text-slate-300"></i>
                </div>
                <p class="text-slate-400 font-medium text-sm" x-text="t('no_products')"></p>
                <button @click="searchQuery = ''; activeCategory = 'All Products'; filterProducts()"
                        class="mt-3 text-xs text-[#0C9044] hover:text-green-700 font-semibold cursor-pointer">
                    <i class="ti ti-arrow-left text-xs"></i> <span x-text="t('show_all')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT: Cart & Checkout Panel ===== --}}
    <div class="w-[360px] xl:w-[380px] bg-white border-l border-slate-200/80 flex flex-col shrink-0 overflow-hidden">

        {{-- Cart Header --}}
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <div class="relative">
                    <i class="ti ti-shopping-cart text-xl text-slate-600"></i>
                    <span x-show="cartItems.length > 0"
                          class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full bg-[#0C9044] text-white text-[10px] font-bold flex items-center justify-center badge-bounce"
                          x-text="cartTotalQty">
                    </span>
                </div>
                <h3 class="text-sm font-bold text-slate-800" x-text="t('cart')"></h3>
            </div>
            <button x-show="cartItems.length > 0"
                    @click="confirmClearCart()"
                    class="text-xs text-red-400 hover:text-red-600 font-medium transition cursor-pointer flex items-center gap-1">
                <i class="ti ti-trash text-sm"></i> <span x-text="t('delete_all')"></span>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto pos-scroll px-4 py-3" x-ref="cartList">
            {{-- Empty cart --}}
            <div x-show="cartItems.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                    <i class="ti ti-shopping-cart-off text-2xl text-slate-300"></i>
                </div>
                <p class="text-sm text-slate-400 font-medium" x-text="t('empty_cart')"></p>
                <p class="text-xs text-slate-300 mt-1" x-text="t('pick_product')"></p>
            </div>

            {{-- Cart item list --}}
            <div class="space-y-2">
                <template x-for="(item, idx) in cartItems" :key="item.variant_id">
                    <div class="cart-item rounded-xl border border-slate-100 p-3 group">
                        <div class="flex items-start gap-3">
                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-slate-700 truncate" x-text="item.product_name"></h4>
                                <p class="text-[11px] text-slate-400 mt-0.5" x-text="item.variant_summary || 'Default'"></p>
                                <p x-show="item.note" class="text-[10px] text-amber-600 mt-0.5 italic flex items-center gap-1">
                                    <i class="ti ti-note text-xs"></i> <span x-text="item.note"></span>
                                </p>
                                <div class="flex items-baseline gap-1.5 mt-1">
                                    <span class="text-sm font-bold text-[#0C9044]"
                                          x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                                    <span x-show="item.normal_price && item.normal_price != item.price"
                                          class="text-[10px] text-slate-400 line-through"
                                          x-text="'Rp ' + Number(item.normal_price).toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            {{-- Remove --}}
                            <button @click="removeCartItem(item)"
                                    class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition cursor-pointer">
                                <i class="ti ti-x text-sm"></i>
                            </button>
                        </div>

                        {{-- Qty + Subtotal row --}}
                        <div class="flex items-center justify-between mt-2.5 pt-2.5 border-t border-slate-50">
                            <div class="flex items-center gap-1.5">
                                <button @click="decreaseQty(item)" :disabled="item.quantity <= 1"
                                        class="qty-btn w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                                    <i class="ti ti-minus text-xs"></i>
                                </button>
                                <span class="w-8 text-center text-sm font-bold text-slate-700" x-text="item.quantity"></span>
                                <button @click="increaseQty(item)"
                                        class="qty-btn w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                                    <i class="ti ti-plus text-xs"></i>
                                </button>
                            </div>
                            <span class="text-sm font-bold text-slate-800"
                                  x-text="'Rp ' + Number(item.price * item.quantity).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Cart Summary & Checkout --}}
        <div class="border-t border-slate-200 bg-white px-5 py-4 shrink-0 space-y-3">
            {{-- Summary rows --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium" x-text="t('subtotal')"></span>
                    <span class="text-xs font-semibold text-slate-600" x-text="'Rp ' + Number(cartSubtotal).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex items-center justify-between" x-show="cartDiscount > 0">
                    <span class="text-xs text-emerald-500 font-medium" x-text="t('discount')"></span>
                    <span class="text-xs font-semibold text-emerald-600" x-text="'- Rp ' + Number(cartDiscount).toLocaleString('id-ID')"></span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-dashed border-slate-200"></div>

            {{-- Total --}}
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-800" x-text="t('total')"></span>
                <span class="text-lg font-extrabold text-[#3A3A3A]" x-text="'Rp ' + Number(cartTotal).toLocaleString('id-ID')"></span>
            </div>

            {{-- Checkout Button --}}
            <a :href="cartItems.length > 0 ? '{{ route('pos.checkout') }}' : '#'"
               @click.prevent="if (cartItems.length > 0) { PosSound.checkout(); window.location.href = '{{ route('pos.checkout') }}'; } else { PosSound.error(); showToast(t('cart_empty_warning'), 'warning'); }"
               class="checkout-btn w-full h-12 rounded-xl flex items-center justify-center gap-2 text-sm font-bold text-white cursor-pointer"
               :class="cartItems.length > 0 ? 'bg-[#0C9044] hover:bg-green-700' : 'bg-slate-300 cursor-not-allowed'">
                <i class="ti ti-arrow-right text-lg"></i>
                <span x-text="t('checkout')"></span>
                <span x-show="cartItems.length > 0" class="ml-1 px-2 py-0.5 rounded-md bg-white/20 text-xs"
                      x-text="'Rp ' + Number(cartTotal).toLocaleString('id-ID')"></span>
            </a>
        </div>
    </div>

    {{-- ===== VARIANT MODAL ===== --}}
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showModal = false" @keydown.escape.window="showModal = false">

        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800" x-text="modalProduct?.product?.name"></h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="t('variant') + ' & ' + t('quantity')"></p>
                </div>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-5 space-y-4">
                {{-- Variant Select --}}
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5 block" x-text="t('variant')"></label>
                    <select x-model="modalSelectedVariantId" @change="updateModalVariant()"
                            class="w-full h-11 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50 transition cursor-pointer">
                        <template x-for="v in modalProduct?.variants || []" :key="v.id">
                            <option :value="v.id" x-text="v.variant_options.join(', ') + ' — Rp ' + Number(v.final_price).toLocaleString('id-ID')"></option>
                        </template>
                    </select>
                </div>

                {{-- Quantity --}}
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5 block" x-text="t('quantity')"></label>
                        <div class="flex items-center gap-2">
                            <button @click="modalQty > 1 && modalQty--" :disabled="modalQty <= 1"
                                    class="qty-btn w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                                <i class="ti ti-minus text-sm"></i>
                            </button>
                            <input type="number" x-model.number="modalQty" min="1"
                                   class="w-16 h-10 rounded-xl border border-slate-200 text-center text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50" />
                            <button @click="modalQty++"
                                    class="qty-btn w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                                <i class="ti ti-plus text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wide" x-text="t('stock')"></p>
                        <p class="text-xl font-bold text-emerald-600" x-text="modalSelectedVariant?.quantity ?? '—'"></p>
                    </div>
                </div>

                {{-- Item Note --}}
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5 block" x-text="t('note')"></label>
                    <input type="text" x-model="modalNote"
                           :placeholder="t('note_placeholder')"
                           class="w-full h-10 rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50 transition" />
                </div>

                {{-- Price preview --}}
                <div class="bg-green-50 rounded-xl px-4 py-3 flex items-center justify-between">
                    <span class="text-xs font-semibold text-[#0C9044]">Total</span>
                    <span class="text-base font-extrabold text-[#3A3A3A]"
                          x-text="'Rp ' + Number((modalSelectedVariant?.final_price || 0) * modalQty).toLocaleString('id-ID')"></span>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 flex items-center gap-3">
                <button @click="showModal = false"
                        class="flex-1 h-11 rounded-xl border border-slate-200 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition cursor-pointer"
                        x-text="t('cancel')">
                </button>
                <button @click="addToCartFromModal()"
                        :disabled="!modalSelectedVariant || modalSelectedVariant.quantity <= 0"
                        class="flex-1 h-11 rounded-xl bg-[#0C9044] hover:bg-green-700 text-white text-sm font-bold transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="ti ti-shopping-cart-plus text-lg"></i> <span x-text="t('add_to_cart')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== CLEAR CART CONFIRM MODAL ===== --}}
    <div x-show="showClearConfirm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showClearConfirm = false">

        <div x-show="showClearConfirm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="ti ti-alert-triangle text-2xl text-red-500"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Hapus Semua Item?</h3>
            <p class="text-sm text-slate-400 mb-5">Semua item dalam keranjang akan dihapus. Tindakan ini tidak bisa dibatalkan.</p>
            <div class="flex gap-3">
                <button @click="showClearConfirm = false"
                        class="flex-1 h-10 rounded-xl border border-slate-200 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition cursor-pointer">
                    Batal
                </button>
                <button @click="clearCart()"
                        class="flex-1 h-10 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-bold transition cursor-pointer">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- ===== F1 HELP / KEYBOARD SHORTCUTS MODAL ===== --}}
    <div x-show="showHelp" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showHelp = false">

        <div x-show="showHelp" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="ti ti-keyboard text-[#0C9044] text-lg"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Keyboard Shortcuts</h3>
                </div>
                <button @click="showHelp = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    <i class="ti ti-x text-lg"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto pos-scroll">
                {{-- Navigation --}}
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-2.5">Navigasi</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Fokus pencarian produk</span>
                            <div class="flex items-center gap-1">
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">Ctrl</kbd>
                                <span class="text-slate-400">+</span>
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">K</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Langsung ke checkout</span>
                            <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">F2</kbd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Reset filter & pencarian</span>
                            <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">F4</kbd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Tutup popup / modal</span>
                            <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">Esc</kbd>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Tampilan --}}
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-2.5">Tampilan</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Toggle mode gelap/terang</span>
                            <div class="flex items-center gap-1">
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">Alt</kbd>
                                <span class="text-slate-400">+</span>
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">D</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Toggle bahasa ID/EN</span>
                            <div class="flex items-center gap-1">
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">Alt</kbd>
                                <span class="text-slate-400">+</span>
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">L</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Buka panduan shortcut ini</span>
                            <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">F1</kbd>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Tips --}}
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-2.5">Tips</p>
                    <div class="space-y-2 text-sm text-slate-500">
                        <div class="flex items-start gap-2">
                            <i class="ti ti-bolt text-amber-500 mt-0.5"></i>
                            <span>Produk dengan 1 varian langsung masuk keranjang tanpa popup</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-star-filled text-amber-400 mt-0.5"></i>
                            <span>Klik bintang pada produk untuk menyimpan favorit</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-barcode text-emerald-500 mt-0.5"></i>
                            <span>Aktifkan mode barcode untuk scan produk otomatis</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-note text-blue-500 mt-0.5"></i>
                            <span>Tambahkan catatan per-item di modal varian</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-bolt text-amber-500 mt-0.5"></i>
                            <span>Klik ikon grid untuk mengubah ukuran tampilan produk</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="ti ti-bolt text-amber-500 mt-0.5"></i>
                            <span>Hover item di keranjang untuk tombol hapus</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50">
                <p class="text-[11px] text-slate-400 text-center">Tekan <kbd class="kbd kbd-xs">Esc</kbd> atau <kbd class="kbd kbd-xs">F1</kbd> untuk menutup</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>

    // Shift Manager component
    function shiftManager() {
        return {
            isOpen: localStorage.getItem('pos_shift_open') === 'true',
            openedAt: localStorage.getItem('pos_shift_opened_at') || null,
            shiftDuration: '',
            _timer: null,
            init() {
                if (this.isOpen && this.openedAt) {
                    this.startTimer();
                }
            },
            toggleShift() {
                if (this.isOpen) {
                    // Close shift
                    const duration = this.shiftDuration;
                    this.isOpen = false;
                    this.openedAt = null;
                    localStorage.setItem('pos_shift_open', 'false');
                    localStorage.removeItem('pos_shift_opened_at');
                    if (this._timer) clearInterval(this._timer);
                    this.shiftDuration = '';
                    showToast('Shift ditutup — Durasi: ' + duration, 'info', 3000);
                } else {
                    // Open shift
                    this.isOpen = true;
                    this.openedAt = new Date().toISOString();
                    localStorage.setItem('pos_shift_open', 'true');
                    localStorage.setItem('pos_shift_opened_at', this.openedAt);
                    this.startTimer();
                    showToast('Shift dibuka', 'success', 2000);
                }
            },
            startTimer() {
                this.updateDuration();
                this._timer = setInterval(() => this.updateDuration(), 60000);
            },
            updateDuration() {
                if (!this.openedAt) return;
                const diff = Date.now() - new Date(this.openedAt).getTime();
                const h = Math.floor(diff / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                this.shiftDuration = (h > 0 ? h + 'j ' : '') + m + 'm';
            }
        };
    }

    // Clock component
    function clock() {
        return {
            time: '',
            date: '',
            start() {
                this.tick();
                setInterval(() => this.tick(), 1000);
            },
            tick() {
                const now = new Date();
                this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            }
        };
    }

    // POS Store with cart logic
    function posStore() {
        return {
            // Products data from server
            allProducts: @json($productsWithDetails->values()),
            filteredProducts: [],
            searchQuery: '{{ $searchTerm }}',
            activeCategory: '{{ $categoryName }}',
            gridCols: 4,

            // Cart data from server session
            cartItems: @json($cartDetails),
            paymentMethod: 'cash',

            // Modal
            showModal: false,
            showHelp: false,
            modalProduct: null,
            modalSelectedVariantId: null,
            modalQty: 1,
            modalNote: '',
            showClearConfirm: false,

            // Sound
            soundEnabled: typeof PosSound !== 'undefined' ? PosSound.enabled : true,

            // Barcode scanner detection
            barcodeMode: false,
            barcodeBuffer: '',
            barcodeTimer: null,
            lastKeyTime: 0,

            // Favorites (localStorage)
            favorites: JSON.parse(localStorage.getItem('pos_favorites') || '[]'),

            init() {
                this.filterProducts();
            },

            // ===== Favorites =====
            isFavorite(productId) {
                return this.favorites.includes(productId);
            },
            toggleFavorite(productId) {
                const idx = this.favorites.indexOf(productId);
                if (idx > -1) {
                    this.favorites.splice(idx, 1);
                    showToast('Dihapus dari favorit', 'info', 1500);
                } else {
                    this.favorites.push(productId);
                    showToast('Ditambahkan ke favorit', 'success', 1500);
                    PosSound.addToCart();
                }
                localStorage.setItem('pos_favorites', JSON.stringify(this.favorites));
                if (this.activeCategory === 'Favorit') this.filterProducts();
            },

            // ===== Barcode Scanner Detection =====
            detectBarcode(e) {
                if (!this.barcodeMode) return;
                if (e.key === 'Enter' && this.barcodeBuffer.length > 3) {
                    e.preventDefault();
                    PosSound.scan();
                    this.searchQuery = this.barcodeBuffer;
                    this.filterProducts();
                    // Auto-add first result if exact match
                    if (this.filteredProducts.length === 1) {
                        const item = this.filteredProducts[0];
                        if (!item.isSoldOut) {
                            this.openVariantModal(item);
                        }
                    }
                    this.barcodeBuffer = '';
                    return;
                }
                if (e.key.length === 1) {
                    const now = Date.now();
                    if (now - this.lastKeyTime > 100) {
                        this.barcodeBuffer = '';
                    }
                    this.barcodeBuffer += e.key;
                    this.lastKeyTime = now;
                }
            },

            // ===== Product Filtering =====
            filterProducts() {
                let products = [...this.allProducts];

                // Category filter
                if (this.activeCategory === 'Favorit') {
                    products = products.filter(p => this.favorites.includes(p.product.id));
                } else if (this.activeCategory === 'Promo') {
                    products = products.filter(p => p.isPromo === 'yes');
                } else if (this.activeCategory !== 'All Products') {
                    const cat = this.activeCategory;
                    products = products.filter(p => {
                        // Check via category relation or product's category
                        if (p.product.category && p.product.category.category_name === cat) return true;
                        if (p.product.product_category && p.product.product_category.category_name === cat) return true;
                        return false;
                    });
                }

                // Search filter
                if (this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase();
                    products = products.filter(p => p.product.name.toLowerCase().includes(q));
                }

                this.filteredProducts = products;
            },

            // ===== Modal =====
            openVariantModal(item) {
                // Quick-add: if product has only 1 variant, add directly without modal
                if (item.variants && item.variants.length === 1) {
                    const variant = item.variants[0];
                    if (variant.quantity > 0) {
                        this.quickAddToCart(item.product.id, variant);
                        return;
                    }
                }

                this.modalProduct = item;
                this.modalQty = 1;
                this.modalNote = '';
                if (item.variants && item.variants.length > 0) {
                    this.modalSelectedVariantId = item.variants[0].id;
                }
                this.showModal = true;
            },

            get modalSelectedVariant() {
                if (!this.modalProduct || !this.modalProduct.variants) return null;
                return this.modalProduct.variants.find(v => v.id == this.modalSelectedVariantId);
            },

            updateModalVariant() {
                // Triggered on select change
            },

            // ===== Cart Operations =====
            quickAddToCart(productId, variant) {
                const variantId = variant.id;
                fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity: 1 })
                })
                .then(res => {
                    const existing = this.cartItems.find(i => i.variant_id == variantId);
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        const product = this.allProducts.find(p => p.product.id == productId);
                        this.cartItems.push({
                            product_id: productId,
                            variant_id: variantId,
                            product_name: product?.product?.name || 'Produk',
                            variant_summary: variant.variant_options?.join(', ') || 'Default',
                            quantity: 1,
                            price: variant.final_price,
                            normal_price: variant.price,
                            note: '',
                        });
                    }
                    PosSound.addToCart();
                    showToast((this.allProducts.find(p => p.product.id == productId)?.product?.name || 'Produk') + ' ' + t('added_to_cart'), 'success', 1500);
                    this.$nextTick(() => {
                        if (this.$refs.cartList) this.$refs.cartList.scrollTop = this.$refs.cartList.scrollHeight;
                    });
                })
                .catch(err => {
                    PosSound.error();
                    showToast(t('add_failed'), 'error');
                    console.error(err);
                });
            },

            addToCartFromModal() {
                if (!this.modalSelectedVariant || !this.modalProduct) return;

                const productId = this.modalProduct.product.id;
                const variantId = this.modalSelectedVariant.id;
                const qty = this.modalQty;
                const note = this.modalNote.trim();

                // POST to server
                fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: qty
                    })
                })
                .then(res => {
                    // Update local cart optimistically
                    const existing = this.cartItems.find(i => i.variant_id == variantId);
                    if (existing) {
                        existing.quantity += qty;
                        if (note) existing.note = note;
                    } else {
                        this.cartItems.push({
                            product_id: productId,
                            variant_id: variantId,
                            product_name: this.modalProduct.product.name,
                            variant_summary: this.modalSelectedVariant.variant_options.join(', '),
                            quantity: qty,
                            price: this.modalSelectedVariant.final_price,
                            normal_price: this.modalSelectedVariant.price,
                            note: note,
                        });
                    }

                    // Save note to server if present
                    if (note) {
                        fetch('{{ route('pos.cart.itemNote') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ variant_id: variantId, note: note })
                        }).catch(() => {});
                    }

                    this.showModal = false;
                    showToast(this.modalProduct.product.name + ' ' + t('added_to_cart'), 'success');

                    // Scroll cart to bottom
                    this.$nextTick(() => {
                        if (this.$refs.cartList) {
                            this.$refs.cartList.scrollTop = this.$refs.cartList.scrollHeight;
                        }
                    });
                })
                .catch(err => {
                    PosSound.error();
                    showToast(t('add_failed'), 'error');
                    console.error(err);
                });
            },

            increaseQty(item) {
                item.quantity++;
                this.syncCartQuantity(item);
            },

            decreaseQty(item) {
                if (item.quantity > 1) {
                    item.quantity--;
                    this.syncCartQuantity(item);
                }
            },

            syncCartQuantity(item) {
                fetch('{{ route('pos.cart.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        quantity: item.quantity
                    })
                }).catch(err => console.error('Cart sync failed:', err));
            },

            removeCartItem(item) {
                this.cartItems = this.cartItems.filter(i => i.variant_id !== item.variant_id);
                PosSound.removeItem();
                showToast(t('item_removed'), 'info');

                fetch('{{ route('pos.cart.remove') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: item.product_id,
                        variant_id: item.variant_id
                    })
                }).catch(err => console.error('Remove item failed:', err));
            },

            confirmClearCart() {
                this.showClearConfirm = true;
            },

            clearCart() {
                this.cartItems = [];
                this.showClearConfirm = false;
                PosSound.removeItem();
                showToast(t('cart_cleared'), 'info');

                fetch('{{ route('cart.clear') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).catch(err => console.error('Clear cart failed:', err));
            },

            // ===== Computed Properties =====
            get cartTotalQty() {
                return this.cartItems.reduce((sum, i) => sum + i.quantity, 0);
            },

            get cartSubtotal() {
                return this.cartItems.reduce((sum, i) => sum + (i.normal_price || i.price) * i.quantity, 0);
            },

            get cartDiscount() {
                return this.cartItems.reduce((sum, i) => {
                    if (i.normal_price && i.normal_price > i.price) {
                        return sum + (i.normal_price - i.price) * i.quantity;
                    }
                    return sum;
                }, 0);
            },

            get cartTax() {
                return 0; // PPN 0% as defined in controller
            },

            get cartTotal() {
                return this.cartSubtotal - this.cartDiscount + this.cartTax;
            },

            // ===== Keyboard Shortcuts =====
            handleShortcut(e) {
                // Ctrl+K: Focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.querySelector('input[x-model="searchQuery"]')?.focus();
                }
                // Escape: Close modal
                if (e.key === 'Escape') {
                    this.showModal = false;
                    this.showClearConfirm = false;
                    this.showHelp = false;
                }
                // F1: Toggle help
                if (e.key === 'F1') {
                    e.preventDefault();
                    this.showHelp = !this.showHelp;
                }
                // F2: Quick checkout
                if (e.key === 'F2' && this.cartItems.length > 0) {
                    e.preventDefault();
                    window.location.href = '{{ route('pos.checkout') }}';
                }
                // F4: Clear search & reset
                if (e.key === 'F4') {
                    e.preventDefault();
                    this.searchQuery = '';
                    this.activeCategory = 'All Products';
                    this.filterProducts();
                }
            }
        };
    }
</script>
@endsection