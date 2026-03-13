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

    /* ========= GRID LAYOUT — explicit grid-template-columns ========= */
    .product-grid-wrap {
        display: grid;
        width: 100%;
        max-width: 100%;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .grid-mode-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .grid-mode-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    .grid-mode-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .grid-mode-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }

    /* Product grid card */
    .prod-card {
        transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        width: 100%;
        min-width: 0;
        max-width: 100%;
    }
    .prod-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -8px rgba(0,0,0,0.1), 0 4px 10px -4px rgba(0,0,0,0.04);
        border-color: rgba(12,144,68,0.4);
    }
    .prod-card:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px -2px rgba(0,0,0,0.06);
    }
    .prod-card.sold-out {
        opacity: 0.5;
        pointer-events: none;
        filter: grayscale(0.3);
    }

    /* Card image container */
    .prod-img {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
    }
    .prod-img img {
        max-width: 75%;
        max-height: 75%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }
    .prod-card:hover .prod-img img {
        transform: scale(1.05);
    }

    /* 2 cols — spacious */
    .grid-mode-2 .prod-img { height: 160px; }
    .grid-mode-2 .prod-info { padding: 12px 14px; }
    .grid-mode-2 .prod-name { font-size: 15px; }
    .grid-mode-2 .prod-price { font-size: 15px; }

    /* 3 cols */
    .grid-mode-3 .prod-img { height: 130px; }
    .grid-mode-3 .prod-info { padding: 10px 12px; }
    .grid-mode-3 .prod-name { font-size: 13.5px; }
    .grid-mode-3 .prod-price { font-size: 13.5px; }

    /* 4 cols — balanced default */
    .grid-mode-4 .prod-img { height: 110px; }
    .grid-mode-4 .prod-info { padding: 10px 12px; }
    .grid-mode-4 .prod-name { font-size: 12.5px; }
    .grid-mode-4 .prod-price { font-size: 12.5px; }

    /* 5 cols — compact */
    .grid-mode-5 .prod-img { height: 90px; }
    .grid-mode-5 .prod-info { padding: 8px 10px; }
    .grid-mode-5 .prod-name { font-size: 11.5px; }
    .grid-mode-5 .prod-price { font-size: 11.5px; }
    .grid-mode-5 .prod-badge-fav { width: 22px; height: 22px; }
    .grid-mode-5 .prod-badge-fav i { font-size: 10px; }
    .grid-mode-5 .prod-badge-promo { font-size: 8px; padding: 1px 5px; }

    /* List view card */
    .prod-card-list {
        flex-direction: row;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
    }
    .prod-card-list:hover {
        box-shadow: 0 4px 16px -4px rgba(0,0,0,0.08);
        border-color: rgba(12,144,68,0.3);
        background: #f0fdf4;
    }
    .prod-card-list:active {
        background: #dcfce7;
    }
    .prod-card-list.sold-out {
        opacity: 0.5;
        pointer-events: none;
        filter: grayscale(0.3);
    }

    /* (grid-wrap styles moved to main grid section above) */

    /* Line clamp utility */
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* Layout control buttons */
    .layout-btn {
        transition: all 0.15s ease;
    }
    .layout-btn:hover:not(.layout-active) {
        background-color: #f1f5f9;
        color: #475569;
    }
    .layout-active {
        background-color: #0C9044;
        color: white;
        box-shadow: 0 2px 8px rgba(12,144,68,0.25);
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

    /* Qty press (micro interaction) */
    .qty-press {
        transform: scale(0.92);
        transition: transform 0.08s ease;
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

    /* Checkout pulse when total updates */
    @keyframes checkoutPulseKey {
        0% { transform: scale(1); box-shadow: 0 4px 18px rgba(12,144,68,0.25); }
        50% { transform: scale(1.03); box-shadow: 0 6px 28px rgba(12,144,68,0.35); }
        100% { transform: scale(1); box-shadow: 0 4px 18px rgba(12,144,68,0.25); }
    }
    .checkout-pulse {
        animation: checkoutPulseKey 0.45s ease-in-out;
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

    /* Image lazy placeholder */
    .img-loading { filter: blur(8px) saturate(0.9); transform: scale(1.02); transition: filter 0.32s ease, transform 0.32s ease, opacity 0.32s ease; }
    .img-loaded { filter: none; transform: none; opacity: 1; }

    /* Larger touch targets on small screens */
    @media (max-width: 768px) {
        .qty-btn { width: 44px !important; height: 44px !important; }
        .mobile-cart-fab { width: 64px !important; height: 64px !important; }
        .checkout-btn { height: 56px !important; }
    }

    /* Bottom-sheet behavior on narrow screens (mobile) */
    @media (max-width: 768px) {
        .cart-drawer-panel {
            left: 0 !important;
            right: 0 !important;
            top: auto !important;
            bottom: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            height: 72vh !important;
            max-height: 86vh !important;
            border-left: none !important;
            border-top: 1px solid rgba(226,232,240,0.6) !important;
            border-radius: 12px 12px 0 0 !important;
            transform: translateY(0) !important;
        }
        .cart-drawer-panel .cart-items { overflow-y: auto; }
        .cart-summary { position: sticky; bottom: 0; z-index: 40; box-shadow: 0 -10px 30px rgba(0,0,0,0.06); }
    }

    /* Mobile header adjustments */
    @media (max-width: 767px) {
        .header-divider { display: none; }
        .header-user-info { display: none; }
    }

    /* ========= MOBILE RESPONSIVE: force 1-2 cols on small screens ========= */
    @media (max-width: 479px) {
        .grid-mode-3,
        .grid-mode-4,
        .grid-mode-5 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
        .grid-mode-3 .prod-img,
        .grid-mode-4 .prod-img,
        .grid-mode-5 .prod-img { height: 120px !important; }
        .grid-mode-3 .prod-info,
        .grid-mode-4 .prod-info,
        .grid-mode-5 .prod-info { padding: 8px 10px !important; }
        .grid-mode-3 .prod-name,
        .grid-mode-4 .prod-name,
        .grid-mode-5 .prod-name { font-size: 12px !important; }
        .grid-mode-3 .prod-price,
        .grid-mode-4 .prod-price,
        .grid-mode-5 .prod-price { font-size: 12px !important; }
    }

    /* Tablet: max 3 cols */
    @media (min-width: 480px) and (max-width: 767px) {
        .grid-mode-4,
        .grid-mode-5 {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }
        .grid-mode-4 .prod-img,
        .grid-mode-5 .prod-img { height: 110px !important; }
    }

    /* ========= MOBILE CART MINI BAR ========= */
    .mobile-cart-bar {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ========= HORIZONTAL SCROLL MODE (Desktop) ========= */
    .product-hscroll-wrap {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }
    .product-hscroll-wrap::-webkit-scrollbar { height: 5px; }
    .product-hscroll-wrap::-webkit-scrollbar-track { background: transparent; }
    .product-hscroll-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }
    .product-hscroll-wrap::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    .product-hscroll-wrap .prod-card {
        scroll-snap-align: start;
        flex-shrink: 0;
    }
    .hscroll-sm .prod-card { width: 160px; }
    .hscroll-sm .prod-img { height: 100px; }
    .hscroll-sm .prod-info { padding: 8px 10px; }
    .hscroll-sm .prod-name { font-size: 12px; }
    .hscroll-sm .prod-price { font-size: 12px; }

    .hscroll-md .prod-card { width: 200px; }
    .hscroll-md .prod-img { height: 120px; }
    .hscroll-md .prod-info { padding: 10px 12px; }
    .hscroll-md .prod-name { font-size: 13px; }
    .hscroll-md .prod-price { font-size: 13px; }

    .hscroll-lg .prod-card { width: 240px; }
    .hscroll-lg .prod-img { height: 140px; }
    .hscroll-lg .prod-info { padding: 12px 14px; }
    .hscroll-lg .prod-name { font-size: 14px; }
    .hscroll-lg .prod-price { font-size: 14px; }
</style>
@endsection

@section('header')
{{-- Top Info Bar --}}
<div class="h-[52px] bg-white border-b border-slate-200/80 flex items-center justify-between px-3 md:px-5 shrink-0">
    {{-- Left: Store info --}}
    <div class="flex items-center gap-2 md:gap-4 min-w-0 overflow-x-auto">
        <div class="flex items-center gap-2 shrink-0">
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
        <div class="w-px h-6 bg-slate-200 header-divider"></div>
        <div class="flex items-center gap-2 header-user-info shrink-0">
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
        <div class="w-px h-6 bg-slate-200 header-divider"></div>
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
<div class="flex flex-1 overflow-hidden" x-data="posStore()" x-init="init()" @keydown.window="handleShortcut($event)" @open-help.window="showHelp = true" @pos-sidebar-toggle.window="sidebarCollapsed = $event.detail.collapsed" @pos-mobile-close.window="mobileCartOpen = false">

    {{-- ===== LEFT: Product Selection Area ===== --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Search + Category Bar --}}
        <div class="bg-white border-b border-slate-100 px-3 md:px-5 py-3 shrink-0">
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
            </div>

            {{-- === LAYOUT CONTROLS BAR === --}}
            <div class="flex items-center gap-2 sm:gap-3 mb-3">
                {{-- View mode tabs: Kolom / Baris / Scroll --}}
                <div class="flex items-center gap-0.5 bg-slate-100 rounded-lg p-0.5 shrink-0">
                    <button @click="setViewMode('grid')"
                            class="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-md text-[11px] sm:text-xs font-semibold transition-all duration-200 cursor-pointer"
                            :class="viewMode === 'grid' ? 'bg-white text-[#0C9044] shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                        <i class="ti ti-layout-grid text-sm"></i>
                        <span class="hidden xs:inline">Kolom</span>
                    </button>
                    <button @click="setViewMode('list')"
                            class="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-md text-[11px] sm:text-xs font-semibold transition-all duration-200 cursor-pointer"
                            :class="viewMode === 'list' ? 'bg-white text-[#0C9044] shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                        <i class="ti ti-list text-sm"></i>
                        <span class="hidden xs:inline">Baris</span>
                    </button>
                    {{-- Scroll view removed per UX update --}}
                </div>

                {{-- Column count selector — grid mode --}}
                <div class="flex items-center gap-1 shrink-0" x-show="viewMode === 'grid'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="w-px h-5 bg-slate-200"></div>
                    <template x-for="n in gridColOptions" :key="n">
                        <button @click="setGridCols(n)"
                                class="layout-btn w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] sm:text-xs font-bold text-slate-500 cursor-pointer"
                                :class="gridCols === n ? 'layout-active' : ''"
                                x-text="n">
                        </button>
                    </template>
                </div>

                {{-- Row size selector — list mode --}}
                <div class="flex items-center gap-1 shrink-0" x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="w-px h-5 bg-slate-200"></div>
                    <button @click="listSize = 'compact'; localStorage.setItem('pos_list_size', 'compact')"
                            class="layout-btn h-7 sm:h-8 px-2 sm:px-2.5 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 cursor-pointer gap-1"
                            :class="listSize === 'compact' ? 'layout-active' : ''">
                        <i class="ti ti-layout-rows text-xs"></i> <span class="hidden sm:inline">Kecil</span>
                    </button>
                    <button @click="listSize = 'normal'; localStorage.setItem('pos_list_size', 'normal')"
                            class="layout-btn h-7 sm:h-8 px-2 sm:px-2.5 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 cursor-pointer gap-1"
                            :class="listSize === 'normal' ? 'layout-active' : ''">
                        <i class="ti ti-layout-list text-xs"></i> <span class="hidden sm:inline">Normal</span>
                    </button>
                    <button @click="listSize = 'large'; localStorage.setItem('pos_list_size', 'large')"
                            class="layout-btn h-7 sm:h-8 px-2 sm:px-2.5 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 cursor-pointer gap-1"
                            :class="listSize === 'large' ? 'layout-active' : ''">
                        <i class="ti ti-layout-bottombar text-xs"></i> <span class="hidden sm:inline">Besar</span>
                    </button>
                </div>

                {{-- Scroll card size selector — hscroll mode --}}
                {{-- Horizontal scroll controls removed — feature deprecated. --}}
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
        <div class="flex-1 overflow-y-auto overflow-x-hidden pos-scroll p-3 md:p-5">
            {{-- Results info --}}
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs text-slate-400 font-medium">
                    <span x-text="filteredProducts.length"></span> produk ditemukan
                </p>
                <p x-show="searchQuery.length > 0" class="text-xs text-slate-400">
                    Hasil pencarian: "<span class="font-semibold text-slate-600" x-text="searchQuery"></span>"
                </p>
            </div>

            {{-- === GRID VIEW === --}}
                <div id="main-products" x-show="viewMode === 'grid'" class="product-grid-wrap"
                 :class="{
                    'grid-mode-2': gridCols === 2,
                    'grid-mode-3': gridCols === 3,
                    'grid-mode-4': gridCols === 4,
                    'grid-mode-5': gridCols === 5
                 }">
                <template x-for="item in filteredProducts" :key="'grid-'+item.product.id">
                      <div :data-product-id="item.product.id" tabindex="0" class="prod-card pos-focusable bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                          :class="{ 'sold-out': item.isSoldOut }"
                          @pointerdown="startCardPress(item)"
                          @pointerup="endCardPress(item)"
                          @pointercancel="cancelCardPress()"
                          @click="if(!_suppressClick && !item.isSoldOut) openVariantModal(item)"
                          @keydown.enter.prevent="if(!item.isSoldOut) openVariantModal(item)"
                          @keydown.space.prevent="if(!item.isSoldOut) openVariantModal(item)"
                          :aria-label="item.product.name">

                        {{-- Image — fixed height container --}}
                        <div class="prod-img rounded-t-xl">
                               <img :src="item.product.image_url ? '{{ asset('') }}' + item.product.image_url : '{{ asset('assets/image.png') }}'"
                                   :alt="item.product.name"
                                   loading="lazy"
                                   class="img-loading"
                                   onload="this.classList.remove('img-loading'); this.classList.add('img-loaded')"
                                   onerror="this.src='{{ asset('assets/image.png') }}'">

                            {{-- Favorite star --}}
                            <button @click.stop="toggleFavorite(item.product.id)"
                                      class="prod-badge-fav absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center bg-white border border-slate-200/80 shadow-sm transition-all duration-200 cursor-pointer hover:shadow-md hover:scale-110 focus:outline-none"
                                                :aria-label="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'"
                                                :title="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'">
                                <svg x-show="!isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" stroke="#cbd5e1" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg x-show="isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="#f59e0b"/>
                                </svg>
                            </button>

                            {{-- Promo badge --}}
                            <div x-show="item.isPromo === 'yes'" class="absolute top-2 left-2">
                                <span class="prod-badge-promo inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-red-500 text-white text-[9px] font-bold uppercase tracking-wide shadow-sm">
                                    <i class="ti ti-discount-2 text-[10px]"></i> Promo
                                </span>
                            </div>

                            {{-- Sold out overlay --}}
                            <div x-show="item.isSoldOut" class="absolute inset-0 bg-white/70 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="px-3 py-1 rounded-lg bg-slate-800/85 text-white text-[10px] font-bold shadow-lg" x-text="t('sold_out')"></span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="prod-info border-t border-slate-100/60 flex flex-col flex-1">
                            <h4 class="prod-name font-semibold text-slate-800 line-clamp-2 leading-snug"
                                x-text="item.product.name"></h4>
                            <div class="mt-auto pt-1.5 flex items-baseline gap-1.5">
                                <span class="prod-price font-bold"
                                      :class="item.isPromo === 'yes' ? 'text-red-600' : 'text-[#0C9044]'"
                                      x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')">
                                </span>
                                <span x-show="item.isPromo === 'yes' && item.normal_price"
                                      class="text-[10px] text-slate-400 line-through"
                                      x-text="'Rp ' + Number(item.normal_price).toLocaleString('id-ID')">
                                </span>
                            </div>
                            {{-- Stock indicator --}}
                            <div class="mt-1.5 flex items-center gap-1.5" x-show="!item.isSoldOut">
                                <div class="w-1.5 h-1.5 rounded-full" :class="item.totalStock > 10 ? 'bg-emerald-400' : item.totalStock > 0 ? 'bg-amber-400' : 'bg-red-400'"></div>
                                <span class="text-[10px] text-slate-400 font-medium" x-text="item.totalStock > 10 ? t('available') : (item.totalStock > 0 ? 'Stok: ' + item.totalStock : t('sold_out'))"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- === LIST / BARIS VIEW === --}}
            <div x-show="viewMode === 'list'" class="flex flex-col"
                 :class="{
                    'gap-1.5': listSize === 'compact',
                    'gap-2.5': listSize === 'normal',
                    'gap-3': listSize === 'large'
                 }">
                <template x-for="item in filteredProducts" :key="'list-'+item.product.id">
                          <div :data-product-id="item.product.id" class="prod-card-list pos-focusable bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.04)] flex"
                                  :class="{
                                      'sold-out': item.isSoldOut,
                                      'rounded-lg': listSize === 'compact'
                                  }"
                                  tabindex="0"
                                  @pointerdown="startCardPress(item)"
                                  @pointerup="endCardPress(item)"
                                  @pointercancel="cancelCardPress()"
                                  @click="if(!_suppressClick && !item.isSoldOut) openVariantModal(item)"
                                  @keydown.enter.prevent="if(!item.isSoldOut) openVariantModal(item)"
                                  @keydown.space.prevent="if(!item.isSoldOut) openVariantModal(item)"
                                  :aria-label="item.product.name">

                        {{-- Image (adaptive thumbnail) --}}
                        <div class="relative bg-slate-50 flex items-center justify-center shrink-0"
                             :class="{
                                'w-14 h-14 p-1.5': listSize === 'compact',
                                'w-20 h-20 sm:w-24 sm:h-24 p-2': listSize === 'normal',
                                'w-28 h-28 sm:w-32 sm:h-32 p-3': listSize === 'large'
                             }">
                            <img :src="item.product.image_url ? '{{ asset('') }}' + item.product.image_url : '{{ asset('assets/image.png') }}'"
                                 :alt="item.product.name"
                                 class="max-h-full max-w-full object-contain"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('assets/image.png') }}'">

                            {{-- Promo badge --}}
                            <div x-show="item.isPromo === 'yes'" class="absolute top-1 left-1">
                                <span class="inline-flex items-center rounded bg-red-500 text-white font-bold uppercase"
                                      :class="listSize === 'compact' ? 'px-1 py-0 text-[7px]' : 'px-1.5 py-0.5 text-[9px]'">
                                    Promo
                                </span>
                            </div>

                            {{-- Sold out overlay --}}
                            <div x-show="item.isSoldOut" class="absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center">
                                <span class="px-2 py-0.5 rounded-md bg-slate-800/85 text-white text-[10px] font-bold" x-text="t('sold_out')"></span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 flex items-center min-w-0 border-l border-slate-100/60"
                             :class="{
                                'px-2.5 py-1.5': listSize === 'compact',
                                'px-3 py-2.5': listSize === 'normal',
                                'px-4 py-3': listSize === 'large'
                             }">
                            <div class="flex-1 min-w-0">
                                {{-- Product name --}}
                                <h4 class="font-semibold text-slate-800 line-clamp-1"
                                    :class="{
                                        'text-xs': listSize === 'compact',
                                        'text-sm': listSize === 'normal',
                                        'text-base': listSize === 'large'
                                    }"
                                    x-text="item.product.name"></h4>

                                {{-- Category label — normal & large only --}}
                                <p x-show="listSize !== 'compact'"
                                   class="text-slate-400 mt-0.5 line-clamp-1"
                                   :class="listSize === 'large' ? 'text-xs' : 'text-[11px]'"
                                   x-text="item.product.category?.category_name || item.product.product_category?.category_name || ''"></p>

                                {{-- Price row --}}
                                <div class="flex items-baseline gap-1.5"
                                     :class="listSize === 'compact' ? 'mt-0.5' : 'mt-1.5'">
                                    <span class="font-bold"
                                          :class="{
                                            'text-xs': listSize === 'compact',
                                            'text-sm': listSize === 'normal',
                                            'text-base': listSize === 'large'
                                          }"
                                          :style="item.isPromo === 'yes' ? 'color: #dc2626' : 'color: #0C9044'"
                                          x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')">
                                    </span>
                                    <span x-show="item.isPromo === 'yes' && item.normal_price"
                                          class="text-[10px] text-slate-400 line-through"
                                          x-text="'Rp ' + Number(item.normal_price).toLocaleString('id-ID')">
                                    </span>
                                </div>

                                {{-- Stock indicator — only on large --}}
                                <div x-show="listSize === 'large' && !item.isSoldOut" class="mt-1 flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="item.totalStock > 10 ? 'bg-emerald-400' : item.totalStock > 0 ? 'bg-amber-400' : 'bg-red-400'"></div>
                                    <span class="text-[10px] text-slate-400 font-medium" x-text="item.totalStock > 10 ? t('available') : 'Stok: ' + item.totalStock"></span>
                                </div>
                            </div>

                            {{-- Right side: stock badge + favorite --}}
                            <div class="flex items-center gap-2 shrink-0 ml-2">
                                {{-- Stock badge — compact & normal --}}
                                <div x-show="listSize !== 'large' && !item.isSoldOut" class="flex items-center gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="item.totalStock > 10 ? 'bg-emerald-400' : item.totalStock > 0 ? 'bg-amber-400' : 'bg-red-400'"></div>
                                    <span x-show="listSize === 'normal'" class="text-[10px] text-slate-400 font-medium" x-text="item.totalStock > 10 ? t('available') : 'Stok: ' + item.totalStock"></span>
                                </div>

                                {{-- Favorite --}}
                                        <button @click.stop="toggleFavorite(item.product.id)"
                                            class="rounded-full flex items-center justify-center shrink-0 bg-transparent cursor-pointer hover:bg-slate-50 focus:outline-none"
                                            :class="listSize === 'compact' ? 'w-6 h-6' : 'w-7 h-7'"
                                            :aria-label="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'"
                                            :title="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'">
                                    <svg x-show="!isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" :class="listSize === 'compact' ? 'w-3 h-3' : 'w-4 h-4'" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" stroke="#cbd5e1" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg x-show="isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" :class="listSize === 'compact' ? 'w-3 h-3' : 'w-4 h-4'" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="#f59e0b"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- === HORIZONTAL SCROLL VIEW (Desktop) === --}}
            <div x-show="viewMode === 'hscroll'" class="product-hscroll-wrap"
                 :class="'hscroll-' + hscrollSize">
                <template x-for="item in filteredProducts" :key="'hs-'+item.product.id">
                      <div :data-product-id="item.product.id" class="prod-card pos-focusable bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
                          :class="{ 'sold-out': item.isSoldOut }"
                          tabindex="0"
                          @pointerdown="startCardPress(item)"
                          @pointerup="endCardPress(item)"
                          @pointercancel="cancelCardPress()"
                          @click="if(!_suppressClick && !item.isSoldOut) openVariantModal(item)"
                          @keydown.enter.prevent="if(!item.isSoldOut) openVariantModal(item)"
                          @keydown.space.prevent="if(!item.isSoldOut) openVariantModal(item)"
                          :aria-label="item.product.name">

                        {{-- Image --}}
                        <div class="prod-img rounded-t-xl">
                               <img :src="item.product.image_url ? '{{ asset('') }}' + item.product.image_url : '{{ asset('assets/image.png') }}'"
                                   :alt="item.product.name"
                                   loading="lazy"
                                   class="img-loading"
                                   onload="this.classList.remove('img-loading'); this.classList.add('img-loaded')"
                                   onerror="this.src='{{ asset('assets/image.png') }}'">

                            {{-- Favorite star --}}
                                <button @click.stop="toggleFavorite(item.product.id)"
                                    class="prod-badge-fav absolute top-1.5 right-1.5 w-6 h-6 rounded-full flex items-center justify-center bg-white border border-slate-200/80 shadow-sm transition-all duration-200 cursor-pointer hover:shadow-md hover:scale-110 focus:outline-none"
                                    :aria-label="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'"
                                    :title="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'">
                                <svg x-show="!isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" stroke="#cbd5e1" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <svg x-show="isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="#f59e0b"/>
                                </svg>
                            </button>

                            {{-- Promo badge --}}
                            <div x-show="item.isPromo === 'yes'" class="absolute top-1.5 left-1.5">
                                <span class="prod-badge-promo inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-red-500 text-white text-[8px] font-bold uppercase tracking-wide shadow-sm">
                                    <i class="ti ti-discount-2 text-[9px]"></i> Promo
                                </span>
                            </div>

                            {{-- Sold out overlay --}}
                            <div x-show="item.isSoldOut" class="absolute inset-0 bg-white/70 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-800/85 text-white text-[9px] font-bold shadow-lg" x-text="t('sold_out')"></span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="prod-info border-t border-slate-100/60 flex flex-col flex-1">
                            <h4 class="prod-name font-semibold text-slate-800 line-clamp-2 leading-snug"
                                x-text="item.product.name"></h4>
                            <div class="mt-auto pt-1 flex items-baseline gap-1">
                                <span class="prod-price font-bold"
                                      :class="item.isPromo === 'yes' ? 'text-red-600' : 'text-[#0C9044]'"
                                      x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')">
                                </span>
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

    {{-- ===== MOBILE CART FAB ===== --}}
        <button aria-label="Buka Keranjang" @click.prevent="openCartAlpine()"
            x-show="!mobileCartOpen"
            class="lg:hidden fixed bottom-20 right-4 z-30 w-14 h-14 rounded-full bg-[#0C9044] text-white flex items-center justify-center mobile-cart-fab cursor-pointer">
        <i class="ti ti-shopping-cart text-xl"></i>
        <span x-show="cartItems.length > 0"
              class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center badge-bounce"
              x-text="cartTotalQty"></span>
    </button>

    {{-- ===== MOBILE CART BACKDROP ===== --}}
    <div x-show="mobileCartOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileCartOpen = false"
         class="lg:hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-40"></div>

    {{-- Quick open button when sidebar collapsed (desktop) --}}
    <!-- Desktop quick-open -->
    <button x-show="sidebarCollapsed"
            @click.prevent="openCartAlpine()"
            aria-label="Buka Keranjang"
            class="hidden lg:flex fixed right-4 top-28 z-50 w-14 h-14 rounded-l-full bg-green-600 text-white items-center justify-center shadow-lg cursor-pointer ring-2 ring-green-300/40"
            title="Buka Keranjang">
        <i class="ti ti-chevron-left text-lg"></i>
    </button>

    <!-- Mobile quick-open (visible on small screens) -->
    <button x-show="sidebarCollapsed"
            @click.prevent="openCartAlpine()"
            aria-label="Buka Keranjang"
            class="lg:hidden fixed bottom-20 right-4 z-50 w-14 h-14 rounded-full bg-green-600 text-white flex items-center justify-center shadow-lg cursor-pointer ring-2 ring-green-300/40"
            title="Buka Keranjang">
        <i class="ti ti-chevron-left text-lg"></i>
    </button>

    {{-- ===== RIGHT: Cart & Checkout Panel ===== --}}
                        <div id="cartPanel" x-ref="cartPanel" @touchstart.prevent="startDrag($event)" @touchmove.prevent="onDrag($event)" @touchend.prevent="endDrag($event)"
                                class="cart-drawer-panel fixed inset-y-0 right-0 z-50 w-[85vw] max-w-[380px]
                            md:relative md:z-auto md:w-[360px] xl:md:w-[380px] md:max-w-none
                            bg-white border-l border-slate-200/80 flex flex-col shrink-0 overflow-hidden"
                                :class="(mobileCartOpen || !sidebarCollapsed) ? 'translate-x-0' : 'translate-x-full'"
                                :aria-hidden="!(mobileCartOpen || !sidebarCollapsed)"
                                :tabindex="(mobileCartOpen || !sidebarCollapsed) ? 0 : -1">

        {{-- Cart Header --}}
        <div class="px-5 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    {{-- Close button (collapses sidebar) --}}
                            <button id="cart-close-btn" @click="closeCart()" aria-label="Tutup Keranjang"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer -ml-1"
                                        title="Tutup Keranjang">
                                <i class="ti ti-arrow-right text-lg"></i>
                            </button>
                    <div class="relative">
                        <i class="ti ti-shopping-cart text-xl text-slate-600"></i>
                        <span x-show="cartItems.length > 0"
                              class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full bg-[#0C9044] text-white text-[10px] font-bold flex items-center justify-center badge-bounce"
                              x-text="cartTotalQty">
                        </span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800" x-text="t('cart')"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <button x-show="cartItems.length > 0"
                            @click="confirmClearCart()"
                            aria-label="Hapus semua item dalam keranjang"
                            class="text-xs text-red-400 hover:text-red-600 font-medium transition cursor-pointer flex items-center gap-1 px-2 py-1.5 rounded-lg hover:bg-red-50">
                        <i class="ti ti-trash text-sm"></i> <span x-text="t('delete_all')"></span>
                    </button>

                    <!-- Sync inspector toggle (visible when debug enabled) -->
                    <button x-show="true" @click="showSyncInspector = !showSyncInspector"
                            aria-label="Toggle sync inspector"
                            class="ml-2 px-2 py-1.5 rounded-md bg-slate-50 text-slate-600 text-xs border border-slate-100 hover:bg-slate-100 transition">
                        Sync: <span x-text="pendingSync.length"></span> / T: <span x-text="telemetryQueue.length"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="cart-items flex-1 overflow-y-auto pos-scroll px-4 py-4" x-ref="cartList">
            {{-- Empty cart --}}
            <div x-show="cartItems.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                    <i class="ti ti-shopping-cart-off text-2xl text-slate-300"></i>
                </div>
                <p class="text-sm text-slate-400 font-medium" x-text="t('empty_cart')"></p>
                <p class="text-xs text-slate-300 mt-1" x-text="t('pick_product')"></p>
            </div>

            {{-- Cart item list --}}
            <div class="space-y-3">
                <template x-for="(item, idx) in cartItems" :key="item.variant_id">
                    <div class="cart-item rounded-xl border border-slate-100 p-3.5 group">
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
                                <button @pointerdown.prevent="startHoldIncrement(item, -1)" @pointerup.prevent="stopHoldIncrement()" @pointercancel.prevent="stopHoldIncrement()" @click="if(!_suppressClick) decreaseQty(item); _suppressClick=false" :disabled="item.quantity <= 1"
                                    class="qty-btn w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                                    <i class="ti ti-minus text-xs"></i>
                                </button>
                                <span class="w-8 text-center text-sm font-bold text-slate-700" x-text="item.quantity"></span>
                                <button @pointerdown.prevent="startHoldIncrement(item, 1)" @pointerup.prevent="stopHoldIncrement()" @pointercancel.prevent="stopHoldIncrement()" @click="if(!_suppressClick) increaseQty(item); _suppressClick=false"
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

            {{-- Sync Inspector Panel (collapsible) --}}
            <div x-show="showSyncInspector" x-transition class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-100 text-sm">
                <div class="flex items-center justify-between mb-2">
                    <strong class="text-sm">Sync Inspector</strong>
                    <button @click="showSyncInspector = false" aria-label="Tutup Inspector" class="text-xs text-slate-500 hover:text-slate-700">Tutup</button>
                </div>
                <div class="mb-2">Pending tasks: <span class="font-semibold" x-text="pendingSync.length"></span></div>
                <template x-if="pendingSync.length">
                    <div class="mb-2 max-h-36 overflow-y-auto border-t border-slate-100 pt-2">
                        <template x-for="(t, i) in pendingSync" :key="i">
                            <div class="py-1 flex items-center justify-between">
                                <div class="truncate text-xs"><span class="font-medium" x-text="t.type"></span> — <span x-text="JSON.stringify(t.payload)"></span></div>
                                <div class="ml-2 flex items-center gap-2">
                                    <button @click="(function(idx){ const task = pendingSync.splice(idx,1)[0]; pendingSync.unshift(task); try{ if(window.localforage) localforage.setItem('pos_pending_sync', pendingSync);}catch(e){}; processSyncQueue(); })(i)" class="px-2 py-1 text-xs bg-green-50 text-green-700 rounded">Retry</button>
                                    <button @click="(function(idx){ pendingSync.splice(idx,1); try{ if(window.localforage) localforage.setItem('pos_pending_sync', pendingSync);}catch(e){}; })(i)" class="px-2 py-1 text-xs bg-red-50 text-red-700 rounded">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <div class="flex items-center gap-2 mt-3">
                    <button @click="manualRetryPending()" class="px-3 py-1.5 rounded bg-green-600 text-white text-sm">Process Now</button>
                    <button @click="clearPendingSync()" class="px-3 py-1.5 rounded bg-red-600 text-white text-sm">Clear Pending</button>
                    <button @click="clearTelemetryQueue()" class="px-3 py-1.5 rounded bg-slate-200 text-sm">Clear Telemetry</button>
                    <button @click="flushTelemetry()" class="px-3 py-1.5 rounded bg-amber-500 text-white text-sm">Flush Telemetry</button>
                </div>
            </div>
        </div>

        {{-- Cart Summary & Checkout --}}
        <div class="cart-summary border-t border-slate-200 bg-white px-5 py-5 shrink-0 space-y-3.5">
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
             <button aria-label="Checkout" @click.prevent="openCheckoutConfirm()"
                 class="checkout-btn w-full h-12 rounded-xl flex items-center justify-center gap-2 text-sm font-bold text-white cursor-pointer"
                 :class="cartItems.length > 0 ? 'bg-[#0C9044] hover:bg-green-700' : 'bg-slate-300 cursor-not-allowed'"
                 :disabled="cartItems.length === 0">
                <i class="ti ti-arrow-right text-lg"></i>
                <span x-text="t('checkout')"></span>
                <span x-show="cartItems.length > 0" class="ml-1 px-2 py-0.5 rounded-md bg-white/20 text-xs"
                      x-text="'Rp ' + Number(cartTotal).toLocaleString('id-ID')"></span>
             </button>
        </div>
    </div>

    {{-- ===== VARIANT MODAL ===== --}}
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showModal = false" @keydown.escape.window="showModal = false">

            <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             x-ref="variantModal" role="dialog" aria-modal="true" aria-label="Pilih varian"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800" x-text="modalProduct?.product?.name"></h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="t('variant') + ' & ' + t('quantity')"></p>
                </div>
                <button @click="showModal = false" aria-label="Tutup" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
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
                                <button @click="modalQty > 1 && (modalQty--, (navigator.vibrate ? navigator.vibrate(8) : null))" :disabled="modalQty <= 1"
                                    @pointerdown.prevent="$event.currentTarget.classList.add('qty-press')"
                                    @pointerup.prevent="$event.currentTarget.classList.remove('qty-press')"
                                    @pointercancel.prevent="$event.currentTarget.classList.remove('qty-press')"
                                    class="qty-btn w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-500 cursor-pointer">
                                <i class="ti ti-minus text-sm"></i>
                            </button>
                            <input type="number" x-model.number="modalQty" min="1"
                                   class="w-16 h-10 rounded-xl border border-slate-200 text-center text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0C9044]/20 focus:border-[#0C9044]/50" />
                                <button @click="modalQty++ , (navigator.vibrate ? navigator.vibrate(8) : null)"
                                    @pointerdown.prevent="$event.currentTarget.classList.add('qty-press')"
                                    @pointerup.prevent="$event.currentTarget.classList.remove('qty-press')"
                                    @pointercancel.prevent="$event.currentTarget.classList.remove('qty-press')"
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
               x-ref="clearModal" role="dialog" aria-modal="true" aria-label="Konfirmasi Hapus Semua"
               class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="ti ti-alert-triangle text-2xl text-red-500"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Hapus Semua Item?</h3>
            <p class="text-sm text-slate-400 mb-5">Semua item dalam keranjang akan dihapus. Tindakan ini tidak bisa dibatalkan.</p>
            <div class="flex gap-3">
                <button @click="showClearConfirm = false" aria-label="Tutup"
                        class="flex-1 h-10 rounded-xl border border-slate-200 text-sm font-semibold text-slate-500 hover:bg-slate-50 hover:bg-gray-100 transition cursor-pointer">
                    ← Batal
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
               x-ref="helpModal" role="dialog" aria-modal="true" aria-label="Panduan Shortcut"
               class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="ti ti-keyboard text-[#0C9044] text-lg"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Keyboard Shortcuts</h3>
                </div>
                <button @click="showHelp = false" aria-label="Tutup" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
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
                            <span class="text-sm text-slate-600">Toggle Kolom / Baris view</span>
                            <div class="flex items-center gap-1">
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">Alt</kbd>
                                <span class="text-slate-400">+</span>
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">V</kbd>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-slate-600">Ubah jumlah kolom / ukuran baris</span>
                            <div class="flex items-center gap-1">
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">Alt</kbd>
                                <span class="text-slate-400">+</span>
                                <kbd class="kbd kbd-sm bg-slate-100 border-slate-200 text-slate-600">G</kbd>
                            </div>
                        </div>
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
                            <i class="ti ti-layout-grid text-emerald-500 mt-0.5"></i>
                            <span>Pilih tampilan Kolom atau Baris dan atur jumlah kolom / ukuran baris sesuai preferensi. Pengaturan otomatis tersimpan.</span>
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
    {{-- Checkout Confirmation Modal (mobile/desktop) --}}
    <div x-show="showCheckoutConfirm" x-transition.opacity class="fixed inset-0 z-60 flex items-center justify-center bg-black/40" @click.self="showCheckoutConfirm = false">
        <div x-ref="checkoutModal" role="dialog" aria-modal="true" aria-label="Konfirmasi Checkout" class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-5">
            <h3 class="text-lg font-bold mb-2">Konfirmasi Checkout</h3>
            <p class="text-sm text-slate-500 mb-4">Periksa ringkasan pesanan sebelum melanjutkan.</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between"><span class="text-sm text-slate-500">Subtotal</span><span class="font-semibold">Rp <span x-text="Number(cartSubtotal).toLocaleString('id-ID')"></span></span></div>
                <div class="flex justify-between" x-show="cartDiscount>0"><span class="text-sm text-emerald-500">Diskon</span><span class="font-semibold text-emerald-600">- Rp <span x-text="Number(cartDiscount).toLocaleString('id-ID')"></span></span></div>
                <div class="flex justify-between"><span class="text-sm font-bold">Total</span><span class="font-bold text-lg">Rp <span x-text="Number(cartTotal).toLocaleString('id-ID')"></span></span></div>
            </div>
            <div class="flex gap-3">
                <button @click="showCheckoutConfirm = false" class="flex-1 h-11 rounded-lg border border-slate-200 hover:bg-gray-100">← Batal</button>
                <button @click="confirmCheckout()" class="flex-1 h-11 rounded-lg bg-[#0C9044] text-white font-bold">Lanjutkan</button>
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
            activeCategory: localStorage.getItem('pos_active_category') || '{{ $categoryName }}',

            // Layout preferences (persisted in localStorage)
            gridCols: parseInt(localStorage.getItem('pos_grid_cols')) || 4,
            viewMode: localStorage.getItem('pos_view_mode') || 'grid',
            listSize: localStorage.getItem('pos_list_size') || 'normal',
            hscrollSize: localStorage.getItem('pos_hscroll_size') || 'md',

            // Cart data from server session
            cartItems: @json($cartDetails),
            paymentMethod: 'cash',

            // Modal
            showModal: false,
            // Sync inspector UI
            showSyncInspector: false,
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

            // Persisted customer selection
            selectedCustomerName: localStorage.getItem('pos_selected_customer_name') || '',

            // Offline / sync queue
            pendingSync: [],
            processingSync: false,
            // Telemetry batching
            telemetryQueue: [],
            telemetryTimer: null,

            // Mobile cart drawer
            mobileCartOpen: false,
            // Desktop collapse state (allow closing sidebar on larger screens)
            sidebarCollapsed: false,

            // Card long-press helpers
            _cardPressTimer: null,
            _cardPressSecondTimer: null,
            _cardLongPress: false,
            _suppressClick: false,

            // Hold-to-increment helpers
            _holdTimer: null,
            _holdInterval: null,

            // Undo stack for multiple removed items
            undoStack: [], // each entry: { id, item, timer }
            undoExpireMs: 7000,
            showCheckoutConfirm: false,

            // Micro-interaction state
            checkoutPulse: false,

            // Drag / swipe for mobile drawer
            dragStartY: 0,
            dragCurrentY: 0,
            isDragging: false,
            dragThreshold: 80,

            // Focus trap helpers
            _focusTrapContainer: null,
            _focusTrapHandler: null,
            _prevFocusedElement: null,

            init() {
                this.filterProducts();
                // Apply responsive defaults if no preference saved
                if (!localStorage.getItem('pos_grid_cols')) {
                    this.gridCols = window.innerWidth < 640 ? 2 : window.innerWidth < 1024 ? 3 : 4;
                }
                // Load persisted sidebar collapse preference
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    this.sidebarCollapsed = true;
                } else {
                    this.sidebarCollapsed = false;
                    this.$nextTick && this.$nextTick(() => { if (this.$refs && this.$refs.cartPanel) this.$refs.cartPanel.style.display = ''; });
                }

                // Persist sidebar collapse changes (if Alpine $watch available)
                try {
                    this.$watch && this.$watch('sidebarCollapsed', (v) => localStorage.setItem('sidebarCollapsed', v ? 'true' : 'false'));
                    this.$watch && this.$watch('activeCategory', (v) => localStorage.setItem('pos_active_category', v));
                    this.$watch && this.$watch('selectedCustomerName', (v) => localStorage.setItem('pos_selected_customer_name', v));
                    this.$watch && this.$watch('showModal', (v) => v ? this.trapFocus('variantModal') : this.releaseFocus());
                    this.$watch && this.$watch('showClearConfirm', (v) => v ? this.trapFocus('clearModal') : this.releaseFocus());
                    this.$watch && this.$watch('showHelp', (v) => v ? this.trapFocus('helpModal') : this.releaseFocus());
                    this.$watch && this.$watch('showCheckoutConfirm', (v) => v ? this.trapFocus('checkoutModal') : this.releaseFocus());
                    this.$watch && this.$watch('mobileCartOpen', (v) => v ? this.trapFocus('cartPanel') : this.releaseFocus());
                } catch (e) {
                    // ignore if $watch not available
                }

                // Start processing sync queue
                // Load persisted pendingSync from localforage (if available)
                try {
                    if (window.localforage) {
                        localforage.getItem('pos_pending_sync').then((data) => {
                            if (Array.isArray(data) && data.length) {
                                this.pendingSync = data.concat(this.pendingSync || []);
                            }
                            this.processSyncQueue();
                        }).catch(() => { this.processSyncQueue(); });
                    } else { this.processSyncQueue(); }
                } catch (e) { this.processSyncQueue(); }

                // Restore telemetry queue if any
                try {
                    if (window.localforage) {
                        localforage.getItem('pos_telemetry_queue').then((q) => { if (Array.isArray(q)) this.telemetryQueue = q; });
                    }
                } catch (e) {}
            },

            // ===== Focus trap utilities =====
            trapFocus(refName) {
                try {
                    const container = this.$refs && this.$refs[refName] ? this.$refs[refName] : null;
                    if (!container) return;
                    this._prevFocusedElement = document.activeElement;
                    const focusable = container.querySelectorAll('a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])');
                    if (focusable && focusable.length) focusable[0].focus();

                    this._focusTrapContainer = container;
                    this._focusTrapHandler = (e) => {
                        if (e.key === 'Escape') {
                            // close commonly used modals
                            if (refName === 'variantModal') this.showModal = false;
                            if (refName === 'clearModal') this.showClearConfirm = false;
                            if (refName === 'helpModal') this.showHelp = false;
                            if (refName === 'checkoutModal') this.showCheckoutConfirm = false;
                            if (refName === 'cartPanel') this.mobileCartOpen = false;
                            return;
                        }
                        if (e.key === 'Tab') {
                            const focusableEls = Array.from(container.querySelectorAll('a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'));
                            if (!focusableEls.length) { e.preventDefault(); return; }
                            const firstEl = focusableEls[0];
                            const lastEl = focusableEls[focusableEls.length - 1];
                            if (e.shiftKey) {
                                if (document.activeElement === firstEl) { e.preventDefault(); lastEl.focus(); }
                            } else {
                                if (document.activeElement === lastEl) { e.preventDefault(); firstEl.focus(); }
                            }
                        }
                    };
                    document.addEventListener('keydown', this._focusTrapHandler);
                } catch (e) { console.error(e); }
            },

            releaseFocus() {
                try {
                    if (this._focusTrapHandler) {
                        document.removeEventListener('keydown', this._focusTrapHandler);
                        this._focusTrapHandler = null;
                    }
                    if (this._prevFocusedElement && this._prevFocusedElement.focus) {
                        this._prevFocusedElement.focus();
                    }
                    this._prevFocusedElement = null;
                    this._focusTrapContainer = null;
                } catch (e) { console.error(e); }
            },

            // ===== Offline Sync Queue =====
            pushSync(task) {
                // Annotate task with retry count
                task.retryCount = task.retryCount || 0;
                this.pendingSync.push(task);
                // persist
                try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e) {}
                this.processSyncQueue();
            },
            processSyncQueue() {
                if (this.processingSync) return;
                if (!this.pendingSync.length) return;
                this.processingSync = true;
                const task = this.pendingSync.shift();

                const doNext = (delay = 150) => setTimeout(() => { this.processingSync = false; this.processSyncQueue(); }, delay);

                if (task.type === 'update') {
                    fetch('{{ route("pos.cart.update", [], false) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(task.payload)
                    }).then(() => { try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}; doNext(); }).catch(() => {
                        // exponential backoff & retry
                        task.retryCount = (task.retryCount || 0) + 1;
                        const backoff = Math.min(30000, 500 * Math.pow(2, Math.min(task.retryCount, 6)) );
                        this.pendingSync.unshift(task);
                        try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}
                        setTimeout(() => { this.processingSync = false; this.processSyncQueue(); }, backoff);
                    });
                } else if (task.type === 'add') {
                    fetch('{{ route("cart.add", [], false) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(task.payload)
                    }).then(() => { try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}; doNext(); }).catch(() => {
                        task.retryCount = (task.retryCount || 0) + 1;
                        const backoff = Math.min(30000, 500 * Math.pow(2, Math.min(task.retryCount, 6)) );
                        this.pendingSync.unshift(task);
                        try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}
                        setTimeout(() => { this.processingSync = false; this.processSyncQueue(); }, backoff);
                    });
                } else if (task.type === 'remove') {
                    fetch('{{ route("pos.cart.remove", [], false) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(task.payload)
                    }).then(() => { try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}; doNext(); }).catch(() => {
                        task.retryCount = (task.retryCount || 0) + 1;
                        const backoff = Math.min(30000, 500 * Math.pow(2, Math.min(task.retryCount, 6)) );
                        this.pendingSync.unshift(task);
                        try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}
                        setTimeout(() => { this.processingSync = false; this.processSyncQueue(); }, backoff);
                    });
                } else if (task.type === 'note') {
                    fetch('{{ route("pos.cart.itemNote", [], false) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify(task.payload)
                    }).then(() => { try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}; doNext(); }).catch(() => {
                        task.retryCount = (task.retryCount || 0) + 1;
                        const backoff = Math.min(30000, 500 * Math.pow(2, Math.min(task.retryCount, 6)) );
                        this.pendingSync.unshift(task);
                        try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}
                        setTimeout(() => { this.processingSync = false; this.processSyncQueue(); }, backoff);
                    });
                } else {
                    doNext();
                }
            },

            // ===== Telemetry batching =====
            trackEventBuffered(name, props) {
                try {
                    const evt = { name, props: props || {}, ts: Date.now() };
                    this.telemetryQueue.push(evt);
                    // persist telemetry queue
                    try { if (window.localforage) localforage.setItem('pos_telemetry_queue', this.telemetryQueue); } catch(e){}
                    if (this.telemetryTimer) return;
                    this.telemetryTimer = setTimeout(() => { this.flushTelemetry(); }, 3000);
                } catch (e) {}
            },
            flushTelemetry() {
                if (!this.telemetryQueue.length) { clearTimeout(this.telemetryTimer); this.telemetryTimer = null; return; }
                const batch = this.telemetryQueue.splice(0, 25);
                try { if (window.trackEventBatch) { window.trackEventBatch(batch); } else if (window.trackEvent) { batch.forEach(b => window.trackEvent(b.name, b.props)); } }
                catch(e){}
                try { if (window.localforage) localforage.setItem('pos_telemetry_queue', this.telemetryQueue); } catch(e){}
                clearTimeout(this.telemetryTimer); this.telemetryTimer = null;
            },

            // Sync inspector controls
            manualRetryPending() {
                try {
                    if (!this.pendingSync.length) return;
                    // bump retryCount and reprocess immediately
                    this.pendingSync.forEach(t => t.retryCount = (t.retryCount || 0));
                    try { if (window.localforage) localforage.setItem('pos_pending_sync', this.pendingSync); } catch(e){}
                    this.processSyncQueue();
                } catch (e) { console.error(e); }
            },
            clearPendingSync() {
                this.pendingSync = [];
                try { if (window.localforage) localforage.removeItem('pos_pending_sync'); } catch(e){}
            },
            clearTelemetryQueue() {
                this.telemetryQueue = [];
                try { if (window.localforage) localforage.removeItem('pos_telemetry_queue'); } catch(e){}
            },
            openSyncInspector() { this.showSyncInspector = true; },
            closeSyncInspector() { this.showSyncInspector = false; },

            // Touch/drag handlers
            startDrag(e) {
                this.isDragging = true;
                this.dragStartY = (e.touches && e.touches[0]) ? e.touches[0].clientY : (e.clientY || 0);
                this.dragCurrentY = this.dragStartY;
            },
            onDrag(e) {
                if (!this.isDragging) return;
                this.dragCurrentY = (e.touches && e.touches[0]) ? e.touches[0].clientY : (e.clientY || 0);
                const delta = this.dragCurrentY - this.dragStartY;
                if (delta > 0 && this.$refs && this.$refs.cartPanel) {
                    this.$refs.cartPanel.style.transform = `translateY(${delta}px)`;
                }
            },
            endDrag(e) {
                if (!this.isDragging) return;
                this.isDragging = false;
                const delta = this.dragCurrentY - this.dragStartY;
                if (this.$refs && this.$refs.cartPanel) this.$refs.cartPanel.style.transform = '';
                if (delta > this.dragThreshold) {
                    // close when dragged down far enough
                    this.mobileCartOpen = false;
                    this.sidebarCollapsed = true;
                }
                this.dragStartY = 0; this.dragCurrentY = 0;
            },

            // Close cart with Alpine-controlled animation
            closeCart() {
                try { console.log('closeCart clicked'); } catch(e) {}
                const panel = this.$refs && this.$refs.cartPanel ? this.$refs.cartPanel : null;
                if (panel) {
                    panel.style.display = '';
                    panel.classList.remove('cart-anim-in');
                    panel.classList.add('cart-anim-out');
                    setTimeout(() => {
                        panel.style.display = 'none';
                        panel.classList.remove('cart-anim-out');
                        this.mobileCartOpen = false;
                        this.sidebarCollapsed = true;
                        try { localStorage.setItem('sidebarCollapsed','true'); } catch(e){}
                    }, 320);
                } else {
                    this.mobileCartOpen = false;
                    this.sidebarCollapsed = true;
                }
            },

            // Open cart with Alpine-controlled animation
            openCartAlpine() {
                try { console.log('openCartAlpine invoked'); } catch(e) {}
                const panel = this.$refs && this.$refs.cartPanel ? this.$refs.cartPanel : null;
                if (panel) {
                    panel.style.display = '';
                    panel.classList.remove('cart-anim-out');
                    // ensure start offscreen
                    panel.style.transform = 'translateX(100%)';
                    requestAnimationFrame(() => {
                        panel.classList.add('cart-anim-in');
                    });
                    setTimeout(() => {
                        panel.classList.remove('cart-anim-in');
                        panel.style.transform = '';
                        this.mobileCartOpen = true;
                        this.sidebarCollapsed = false;
                        try { localStorage.setItem('sidebarCollapsed','false'); } catch(e){}
                    }, 420);
                } else {
                    this.mobileCartOpen = true;
                    this.sidebarCollapsed = false;
                }
            },

            // ===== Layout Controls =====
            setGridCols(n) {
                this.gridCols = n;
                this.viewMode = 'grid';
                localStorage.setItem('pos_grid_cols', n);
                localStorage.setItem('pos_view_mode', 'grid');
            },
            setViewMode(mode) {
                this.viewMode = mode;
                localStorage.setItem('pos_view_mode', mode);
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
                    window.trackEvent && window.trackEvent('favorite_removed', { product_id: productId });
                } else {
                    this.favorites.push(productId);
                    showToast('Ditambahkan ke favorit', 'success', 1500);
                    PosSound.addToCart();
                    window.trackEvent && window.trackEvent('favorite_added', { product_id: productId });
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

                // Sort A-Z by product name
                products.sort((a, b) => a.product.name.localeCompare(b.product.name, 'id'));

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

            // ===== Long-press on product card (quick add x5 / x10) =====
            startCardPress(item) {
                try { if (this._cardPressTimer) clearTimeout(this._cardPressTimer); } catch(e){}
                this._cardLongPress = false;
                // after 600ms -> quick add 5
                this._cardPressTimer = setTimeout(() => {
                    this._cardLongPress = true;
                    const qty1 = 5;
                    const variantId = (item.variants && item.variants.length === 1) ? item.variants[0].id : (item.variant_id || null);
                    const payload = variantId ? { product_id: item.product.id, variant_id: variantId, quantity: qty1 } : { product_id: item.product.id, quantity: qty1 };
                    this.pushSync({ type: 'add', payload });
                    PosSound.addToCart();
                    showToast((item.product.name || 'Produk') + ' +'+qty1, 'success', 1400);
                    this.animateProductAdded && this.animateProductAdded(item.product.id);

                    // after additional 600ms -> add another +5 (total 10)
                    this._cardPressSecondTimer = setTimeout(() => {
                        const qty2 = 5;
                        const payload2 = variantId ? { product_id: item.product.id, variant_id: variantId, quantity: qty2 } : { product_id: item.product.id, quantity: qty2 };
                        this.pushSync({ type: 'add', payload: payload2 });
                        PosSound.addToCart();
                        showToast((item.product.name || 'Produk') + ' +'+(qty1+qty2), 'success', 1400);
                        this.animateProductAdded && this.animateProductAdded(item.product.id);
                    }, 600);
                }, 600);
            },
            endCardPress(item) {
                if (this._cardPressTimer) { clearTimeout(this._cardPressTimer); this._cardPressTimer = null; }
                if (this._cardPressSecondTimer) { clearTimeout(this._cardPressSecondTimer); this._cardPressSecondTimer = null; }
                if (this._cardLongPress) {
                    // prevent click after long press
                    this._suppressClick = true;
                    setTimeout(() => { this._suppressClick = false; }, 50);
                    this._cardLongPress = false;
                }
            },
            cancelCardPress() {
                if (this._cardPressTimer) { clearTimeout(this._cardPressTimer); this._cardPressTimer = null; }
                if (this._cardPressSecondTimer) { clearTimeout(this._cardPressSecondTimer); this._cardPressSecondTimer = null; }
                this._cardLongPress = false;
            },

            // ===== Hold-to-increment / hold-to-decrement for qty buttons =====
            startHoldIncrement(item, delta) {
                // delta: +1 or -1
                if (this._holdTimer) clearTimeout(this._holdTimer);
                if (this._holdInterval) { clearInterval(this._holdInterval); this._holdInterval = null; }
                // immediate step
                if (delta > 0) this.increaseQty(item); else this.decreaseQty(item);
                this._suppressClick = true;
                // after short delay, start repeating
                this._holdTimer = setTimeout(() => {
                    let speed = 300;
                    this._holdInterval = setInterval(() => {
                        if (delta > 0) this.increaseQty(item); else this.decreaseQty(item);
                        speed = Math.max(80, speed - 20);
                        // if speed changed, restart interval for next tick speed
                    }, speed);
                }, 350);
            },
            stopHoldIncrement() {
                if (this._holdTimer) { clearTimeout(this._holdTimer); this._holdTimer = null; }
                if (this._holdInterval) { clearInterval(this._holdInterval); this._holdInterval = null; }
                setTimeout(() => { this._suppressClick = false; }, 50);
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
                // Optimistic local update
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
                this.$nextTick(() => { if (this.$refs.cartList) this.$refs.cartList.scrollTop = this.$refs.cartList.scrollHeight; });

                // Push to offline sync queue and analytics
                this.pushSync({ type: 'add', payload: { product_id: productId, variant_id: variantId, quantity: 1 } });
                window.trackEvent && window.trackEvent('add_to_cart', { product_id: productId, variant_id: variantId, quantity: 1 });
                this.animateProductAdded && this.animateProductAdded(productId);
            },

            addToCartFromModal() {
                if (!this.modalSelectedVariant || !this.modalProduct) return;

                const productId = this.modalProduct.product.id;
                const variantId = this.modalSelectedVariant.id;
                const qty = this.modalQty;
                const note = this.modalNote.trim();
                // Optimistic local update
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

                // Save note via sync queue
                if (note) {
                    this.pushSync({ type: 'note', payload: { variant_id: variantId, note: note } });
                }

                this.showModal = false;
                showToast(this.modalProduct.product.name + ' ' + t('added_to_cart'), 'success');

                // Scroll cart to bottom
                this.$nextTick(() => { if (this.$refs.cartList) { this.$refs.cartList.scrollTop = this.$refs.cartList.scrollHeight; } });

                // Push add to sync queue + analytics
                this.pushSync({ type: 'add', payload: { product_id: productId, variant_id: variantId, quantity: qty } });
                window.trackEvent && window.trackEvent('add_to_cart', { product_id: productId, variant_id: variantId, quantity: qty });
                this.animateProductAdded && this.animateProductAdded(productId);
            },

            increaseQty(item) {
                item.quantity++;
                try { if (navigator && navigator.vibrate) navigator.vibrate(8); } catch(e){}
                this.checkoutPulse = true;
                setTimeout(() => { this.checkoutPulse = false; }, 420);
                this.pushSync({ type: 'update', payload: { product_id: item.product_id, variant_id: item.variant_id, quantity: item.quantity } });
                window.trackEvent && window.trackEvent('update_quantity', { product_id: item.product_id, variant_id: item.variant_id, quantity: item.quantity });
            },

            decreaseQty(item) {
                if (item.quantity > 1) {
                    item.quantity--;
                    try { if (navigator && navigator.vibrate) navigator.vibrate(8); } catch(e){}
                    this.checkoutPulse = true;
                    setTimeout(() => { this.checkoutPulse = false; }, 420);
                    this.pushSync({ type: 'update', payload: { product_id: item.product_id, variant_id: item.variant_id, quantity: item.quantity } });
                    window.trackEvent && window.trackEvent('update_quantity', { product_id: item.product_id, variant_id: item.variant_id, quantity: item.quantity });
                }
            },

            // syncCartQuantity replaced by pending sync queue
            syncCartQuantity(item) {
                this.pushSync({ type: 'update', payload: { product_id: item.product_id, variant_id: item.variant_id, quantity: item.quantity } });
            },

            removeCartItem(item) {
                // Push to undo stack
                const entryId = 'undo_' + (Math.random().toString(36).slice(2,9));
                const cloned = JSON.parse(JSON.stringify(item));
                const timer = setTimeout(() => {
                    // expire: remove entry from stack
                    this.undoStack = this.undoStack.filter(s => s.id !== entryId);
                }, this.undoExpireMs);
                this.undoStack.push({ id: entryId, item: cloned, timer });

                // Optimistically remove locally
                this.cartItems = this.cartItems.filter(i => i.variant_id !== item.variant_id);
                PosSound.removeItem();

                // Show undo toast (localized)
                try {
                    showUndoToast(t('item_removed'), () => { this.restoreRemovedById(entryId); }, this.undoExpireMs);
                } catch (e) {
                    showToast(t('item_removed'), 'info');
                }

                // Push remove to sync queue and analytics
                this.pushSync({ type: 'remove', payload: { product_id: item.product_id, variant_id: item.variant_id } });
                window.trackEvent && window.trackEvent('remove_from_cart', { product_id: item.product_id, variant_id: item.variant_id });
            },
            restoreRemovedById(id) {
                const idx = this.undoStack.findIndex(s => s.id === id);
                if (idx === -1) return;
                const entry = this.undoStack[idx];
                clearTimeout(entry.timer);
                const item = entry.item;

                // Restore locally
                const exists = this.cartItems.find(i => i.variant_id === item.variant_id);
                if (exists) {
                    exists.quantity = (exists.quantity || 0) + (item.quantity || 1);
                } else {
                    this.cartItems.push(item);
                }
                PosSound.addToCart();
                showToast((item.product_name || 'Produk') + ' ' + t('item_restored'), 'success');

                // Attempt to restore on server
                fetch('{{ route("cart.add", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        quantity: item.quantity || 1
                    })
                }).catch(err => console.error('Restore item failed:', err));

                window.trackEvent && window.trackEvent('undo_restore', { product_id: item.product_id, variant_id: item.variant_id, quantity: item.quantity });
                // Remove from stack
                this.undoStack.splice(idx, 1);
            },

            animateProductAdded(productId) {
                try {
                    const el = document.querySelector('[data-product-id="' + productId + '"]');
                    if (!el) return;
                    el.classList.add('ring', 'ring-4', 'ring-green-200/50');
                    el.style.transform = 'scale(1.03)';
                    setTimeout(() => { el.classList.remove('ring', 'ring-4', 'ring-green-200/50'); el.style.transform = ''; }, 420);
                } catch (e) { /* ignore */ }
            },

            openCheckoutConfirm() {
                if (!this.cartItems || this.cartItems.length === 0) {
                    typeof PosSound !== 'undefined' && PosSound.error();
                    showToast(t('cart_empty_warning'), 'warning');
                    return;
                }
                this.showCheckoutConfirm = true;
            },

            confirmCheckout() {
                // Proceed to checkout page using route helper (avoid hardcoding host)
                typeof PosSound !== 'undefined' && PosSound.checkout();
                console.debug('POS: confirmCheckout invoked, navigating to cart');
                window.trackEvent && window.trackEvent('checkout_initiated', { total: this.cartTotal, items: this.cartTotalQty });
                window.location.href = '{{ route('pos.checkout') }}';
            },

            confirmClearCart() {
                this.showClearConfirm = true;
            },

            clearCart() {
                this.cartItems = [];
                this.showClearConfirm = false;
                PosSound.removeItem();
                showToast(t('cart_cleared'), 'info');

                window.trackEvent && window.trackEvent('clear_cart', { items: 0, total: 0 });

                fetch('{{ route("cart.clear", [], false) }}', {
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
                // Alt+V: Toggle Grid/List view
                if (e.altKey && (e.key === 'v' || e.key === 'V')) {
                    e.preventDefault();
                    this.setViewMode(this.viewMode === 'grid' ? 'list' : 'grid');
                    showToast(this.viewMode === 'grid' ? 'Mode: Kolom' : 'Mode: Baris', 'info', 1200);
                }
                // Alt+G: Cycle grid columns (2→3→4→5→2) or list sizes (compact→normal→large→compact)
                if (e.altKey && (e.key === 'g' || e.key === 'G')) {
                    e.preventDefault();
                    if (this.viewMode === 'grid') {
                        const next = this.gridCols >= 5 ? 2 : this.gridCols + 1;
                        this.setGridCols(next);
                        showToast(next + ' kolom', 'info', 1200);
                    } else {
                        const sizes = ['compact', 'normal', 'large'];
                        const idx = sizes.indexOf(this.listSize);
                        this.listSize = sizes[(idx + 1) % sizes.length];
                        localStorage.setItem('pos_list_size', this.listSize);
                        const labels = { compact: 'Kecil', normal: 'Normal', large: 'Besar' };
                        showToast('Baris: ' + labels[this.listSize], 'info', 1200);
                    }
                }
            }
        };
    }
</script>

<style>
/* Cart slide bounce animations */
@keyframes cartSlideIn {
    0% { transform: translateX(100%); }
    60% { transform: translateX(-12px); }
    100% { transform: translateX(0); }
}
@keyframes cartSlideOut {
    0% { transform: translateX(0); }
    60% { transform: translateX(8px); }
    100% { transform: translateX(100%); }
}
.cart-anim-in { animation: cartSlideIn 380ms cubic-bezier(.2,.9,.2,1) forwards; }
.cart-anim-out { animation: cartSlideOut 260ms cubic-bezier(.4,.0,.2,1) forwards; }
</style>
@endsection