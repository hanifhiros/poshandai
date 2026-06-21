@extends('layouts.layoutPos')

@section('title', 'POS Dashboard Handai Coffee')

@section('page-style')
@vite('resources/css/handai-pos-dashboard.css')
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
                @click="soundEnabled = !soundEnabled; alert(soundEnabled ? 'Suara aktif' : 'Suara nonaktif')"
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
                </div>

                {{-- Column count selector — grid mode --}}
                <div class="flex items-center gap-1 shrink-0" x-show="viewMode === 'grid'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="w-px h-5 bg-slate-200"></div>
                    <template x-for="n in gridColOptions" :key="n">
                        <button @click="setGridCols(n)"
                                class="layout-btn w-7 h-7 sm:w-8 sm:h-8 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] sm:text-xs font-bold text-slate-500 cursor-pointer"
                                :class="gridCols === n ? 'bg-[#0C9044] text-white border-transparent' : 'bg-white'"
                                x-text="n">
                        </button>
                    </template>
                </div>

                {{-- Row size selector — list mode --}}
                <div class="flex items-center gap-1 shrink-0" x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="w-px h-5 bg-slate-200"></div>
                    <button @click="listSize = 'compact'; localStorage.setItem('pos_list_size', 'compact')"
                            class="layout-btn h-7 sm:h-8 px-2 sm:px-2.5 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 cursor-pointer gap-1"
                            :class="listSize === 'compact' ? 'bg-[#0C9044] text-white border-transparent' : 'bg-white'">
                        <i class="ti ti-layout-rows text-xs"></i> <span class="hidden sm:inline">Kecil</span>
                    </button>
                    <button @click="listSize = 'normal'; localStorage.setItem('pos_list_size', 'normal')"
                            class="layout-btn h-7 sm:h-8 px-2 sm:px-2.5 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 cursor-pointer gap-1"
                            :class="listSize === 'normal' ? 'bg-[#0C9044] text-white border-transparent' : 'bg-white'">
                        <i class="ti ti-layout-list text-xs"></i> <span class="hidden sm:inline">Normal</span>
                    </button>
                    <button @click="listSize = 'large'; localStorage.setItem('pos_list_size', 'large')"
                            class="layout-btn h-7 sm:h-8 px-2 sm:px-2.5 rounded-lg border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 cursor-pointer gap-1"
                            :class="listSize === 'large' ? 'bg-[#0C9044] text-white border-transparent' : 'bg-white'">
                        <i class="ti ti-layout-bottombar text-xs"></i> <span class="hidden sm:inline">Besar</span>
                    </button>
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-1">
                <button @click="activeCategory = 'All Products'; filterProducts()"
                        :class="activeCategory === 'All Products' ? 'bg-[#0C9044] text-white border-[#0C9044]' : 'border-slate-200 text-slate-500 bg-white'"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border cursor-pointer transition">
                    <i class="ti ti-apps text-sm"></i> <span>Semua</span>
                </button>
                <button @click="activeCategory = 'Favorit'; filterProducts()"
                        :class="activeCategory === 'Favorit' ? 'bg-[#0C9044] text-white border-[#0C9044]' : 'border-slate-200 text-slate-500 bg-white'"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border cursor-pointer transition">
                    <i class="ti ti-star-filled text-sm" :class="activeCategory === 'Favorit' ? 'text-white' : 'text-amber-400'"></i> <span>Favorit</span>
                </button>
                @foreach($categories as $cat)
                <button @click="activeCategory = '{{ $cat->category_name }}'; filterProducts()"
                        :class="activeCategory === '{{ $cat->category_name }}' ? 'bg-[#0C9044] text-white border-[#0C9044]' : 'border-slate-200 text-slate-500 bg-white'"
                        class="cat-pill inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border cursor-pointer transition">
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
            <div id="main-products" x-show="viewMode === 'grid'" class="grid gap-3 sm:gap-4"
                 :class="{
                    'grid-cols-2': gridCols === 2,
                    'grid-cols-3': gridCols === 3,
                    'grid-cols-4': gridCols === 4,
                    'grid-cols-5': gridCols === 5
                 }">
                <template x-for="item in filteredProducts" :key="'grid-'+item.product.id">
                      <div :data-product-id="item.product.id" tabindex="0" class="prod-card pos-focusable bg-white rounded-xl border border-slate-200/60 overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.04)] relative cursor-pointer hover:shadow-md hover:border-green-200 transition"
                          :class="{ 'opacity-50 grayscale': item.isSoldOut }"
                          @click="if(!item.isSoldOut) openVariantModal(item)"
                          @keydown.enter.prevent="if(!item.isSoldOut) openVariantModal(item)"
                          @keydown.space.prevent="if(!item.isSoldOut) openVariantModal(item)"
                          :aria-label="item.product.name">

                        {{-- Image — fixed height container --}}
                        <div class="w-full h-32 bg-slate-50 flex items-center justify-center p-2 relative">
                               <img :src="item.product.image_url ? '{{ asset('') }}' + item.product.image_url : '{{ asset('assets/image.png') }}'"
                                   :alt="item.product.name"
                                   loading="lazy"
                                   class="max-w-full max-h-full object-contain mix-blend-multiply"
                                   onerror="this.src='{{ asset('assets/image.png') }}'">

                            {{-- Favorite star --}}
                            <button @click.stop="toggleFavorite(item.product.id)"
                                      class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center bg-white border border-slate-200 shadow-sm transition-transform hover:scale-110"
                                      :title="isFavorite(item.product.id) ? 'Hapus dari favorit' : 'Tambah ke favorit'">
                                <svg x-show="!isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <svg x-show="isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            </button>

                            {{-- Sold out overlay --}}
                            <div x-show="item.isSoldOut" class="absolute inset-0 bg-white/70 backdrop-blur-[2px] flex items-center justify-center">
                                <span class="px-3 py-1 rounded-lg bg-red-600 text-white text-[10px] font-bold shadow-lg uppercase">Habis</span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="p-3 border-t border-slate-100 flex flex-col justify-between h-20">
                            <h4 class="font-semibold text-slate-800 text-xs sm:text-sm line-clamp-2 leading-tight" x-text="item.product.name"></h4>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-bold text-[#0C9044] text-xs sm:text-sm" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                                <div class="w-1.5 h-1.5 rounded-full" :class="item.totalStock > 10 ? 'bg-emerald-400' : item.totalStock > 0 ? 'bg-amber-400' : 'bg-red-400'"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- === LIST / BARIS VIEW === --}}
            <div x-show="viewMode === 'list'" class="flex flex-col"
                 :class="{
                    'gap-2': listSize === 'compact',
                    'gap-3': listSize === 'normal',
                    'gap-4': listSize === 'large'
                 }">
                <template x-for="item in filteredProducts" :key="'list-'+item.product.id">
                          <div :data-product-id="item.product.id" class="pos-focusable bg-white border border-slate-200/60 overflow-hidden shadow-sm flex items-center cursor-pointer hover:bg-slate-50 transition"
                                  :class="{
                                      'opacity-50 grayscale': item.isSoldOut,
                                      'rounded-lg': listSize === 'compact',
                                      'rounded-xl': listSize !== 'compact'
                                  }"
                                  tabindex="0"
                                  @click="if(!item.isSoldOut) openVariantModal(item)">

                        {{-- Image (adaptive thumbnail) --}}
                        <div class="relative bg-slate-50 flex items-center justify-center shrink-0 border-r border-slate-100"
                             :class="{
                                'w-16 h-16 p-2': listSize === 'compact',
                                'w-24 h-24 p-3': listSize === 'normal',
                                'w-32 h-32 p-4': listSize === 'large'
                             }">
                            <img :src="item.product.image_url ? '{{ asset('') }}' + item.product.image_url : '{{ asset('assets/image.png') }}'"
                                 :alt="item.product.name"
                                 class="max-h-full max-w-full object-contain mix-blend-multiply"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('assets/image.png') }}'">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0 px-4 py-2">
                            <h4 class="font-semibold text-slate-800 line-clamp-1"
                                :class="{
                                    'text-sm': listSize === 'compact',
                                    'text-base': listSize === 'normal',
                                    'text-lg': listSize === 'large'
                                }"
                                x-text="item.product.name"></h4>

                            <p x-show="listSize !== 'compact'" class="text-slate-400 mt-0.5 text-xs line-clamp-1" x-text="item.product.category?.category_name || item.product.product_category?.category_name || ''"></p>

                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="font-bold text-[#0C9044]"
                                      :class="{
                                          'text-sm': listSize === 'compact',
                                          'text-base': listSize === 'normal',
                                          'text-lg': listSize === 'large'
                                      }"
                                      x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')">
                                </span>
                            </div>
                        </div>

                        {{-- Right side: stock badge + favorite --}}
                        <div class="flex items-center gap-4 pr-4 shrink-0">
                            <div class="flex items-center gap-1.5" x-show="!item.isSoldOut">
                                <div class="w-2 h-2 rounded-full" :class="item.totalStock > 10 ? 'bg-emerald-400' : item.totalStock > 0 ? 'bg-amber-400' : 'bg-red-400'"></div>
                                <span x-show="listSize !== 'compact'" class="text-xs text-slate-500 font-medium" x-text="item.totalStock > 10 ? 'Tersedia' : 'Sisa: ' + item.totalStock"></span>
                            </div>

                            {{-- Favorite --}}
                            <button @click.stop="toggleFavorite(item.product.id)"
                                class="rounded-full flex items-center justify-center shrink-0 w-8 h-8 hover:bg-slate-100 transition">
                                <svg x-show="!isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg x-show="isFavorite(item.product.id)" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty state --}}
            <div x-show="filteredProducts.length === 0" class="flex flex-col items-center justify-center py-20">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <i class="ti ti-coffee-off text-3xl text-slate-300"></i>
                </div>
                <p class="text-slate-500 font-medium text-sm">Tidak ada produk ditemukan.</p>
                <button @click="searchQuery = ''; activeCategory = 'All Products'; filterProducts()"
                        class="mt-3 text-xs text-[#0C9044] hover:text-green-700 font-semibold cursor-pointer">
                    <i class="ti ti-arrow-left text-xs"></i> Tampilkan Semua
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE CART FAB ===== --}}
    <button aria-label="Buka Keranjang" @click.prevent="openCartAlpine()"
            x-show="!mobileCartOpen"
            class="lg:hidden fixed bottom-6 right-6 z-30 w-16 h-16 rounded-full bg-[#0C9044] text-white shadow-xl flex items-center justify-center cursor-pointer hover:scale-105 transition-transform">
        <i class="ti ti-shopping-cart text-2xl"></i>
        <span x-show="cartItems.length > 0"
              class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center border-2 border-white"
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
         class="lg:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-40"></div>

    {{-- Quick open button when sidebar collapsed (desktop) --}}
    <button x-show="sidebarCollapsed"
            @click.prevent="openCartAlpine()"
            class="hidden lg:flex fixed right-0 top-1/2 -translate-y-1/2 z-50 w-10 h-24 rounded-l-2xl bg-white border border-slate-200 border-r-0 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 items-center justify-center shadow-[-4px_0_15px_rgba(0,0,0,0.05)] cursor-pointer transition-colors"
            title="Buka Keranjang">
        <i class="ti ti-layout-sidebar-right-expand text-2xl"></i>
    </button>

    {{-- ===== RIGHT: Cart & Checkout Panel ===== --}}
    <div id="cartPanel" x-ref="cartPanel"
         class="cart-drawer-panel fixed inset-y-0 right-0 z-50 w-[85vw] max-w-[400px] shadow-2xl
                md:relative md:z-auto md:w-[380px] xl:md:w-[420px] md:max-w-none md:shadow-none
                bg-white border-l border-slate-200/80 flex flex-col shrink-0 overflow-hidden transition-transform duration-300"
         :class="(mobileCartOpen || !sidebarCollapsed) ? 'translate-x-0' : 'translate-x-full md:translate-x-0 md:hidden'"
         :aria-hidden="!(mobileCartOpen || !sidebarCollapsed)">

        {{-- Cart Header --}}
        <div class="px-5 py-4 border-b border-slate-100 shrink-0 bg-white z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Close button (collapses sidebar) --}}
                    <button @click="closeCart()" aria-label="Tutup Keranjang"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer -ml-2">
                        <i class="ti ti-arrow-right text-xl"></i>
                    </button>
                    <div class="relative">
                        <i class="ti ti-shopping-cart text-2xl text-slate-700"></i>
                        <span x-show="cartItems.length > 0"
                              class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center border border-white"
                              x-text="cartTotalQty">
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Keranjang</h3>
                </div>
                <div class="flex items-center">
                    <button x-show="cartItems.length > 0"
                            @click="confirmClearCart()"
                            class="text-xs text-red-500 hover:text-red-700 font-semibold transition cursor-pointer flex items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-red-50">
                        <i class="ti ti-trash"></i> Kosongkan
                    </button>
                </div>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="cart-items flex-1 overflow-y-auto pos-scroll bg-slate-50/50 p-4" x-ref="cartList">
            {{-- Empty cart --}}
            <div x-show="cartItems.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10">
                <img src="{{ asset('assets/svg/empty-cart.svg') }}" onerror="this.style.display='none'" class="w-32 opacity-50 mb-4" alt="Empty Cart">
                <p class="text-sm text-slate-500 font-medium">Keranjang Anda masih kosong.</p>
                <p class="text-xs text-slate-400 mt-1">Pilih produk dari etalase untuk memulai transaksi.</p>
            </div>

            {{-- Cart item list --}}
            <div class="space-y-3">
                <template x-for="(item, idx) in cartItems" :key="item.variant_id">
                    <div class="bg-white rounded-xl border border-slate-200/60 p-3 shadow-sm relative group">
                        <div class="flex gap-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 line-clamp-1 pr-6" x-text="item.product_name"></h4>
                                <p class="text-xs text-slate-500 mt-0.5" x-text="item.variant_summary === 'Default' ? 'Regular' : item.variant_summary.replace('â€”', '—')"></p>
                                
                                <div class="flex items-center justify-between mt-3">
                                    <span class="text-sm font-bold text-[#0C9044]" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                                    
                                    {{-- Qty Controls --}}
                                    <div class="flex items-center bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                                        <button @click="decreaseQty(item)" :disabled="item.quantity <= 1"
                                            class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center text-slate-600 hover:text-[#0C9044] disabled:opacity-50 transition cursor-pointer">
                                            <i class="ti ti-minus text-xs"></i>
                                        </button>
                                        <span class="w-8 text-center text-sm font-bold text-slate-800" x-text="item.quantity"></span>
                                        <button @click="increaseQty(item)"
                                            class="w-7 h-7 rounded-md bg-white shadow-sm flex items-center justify-center text-slate-600 hover:text-[#0C9044] transition cursor-pointer">
                                            <i class="ti ti-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Remove button --}}
                        <button @click="removeCartItem(item)"
                                class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center bg-white border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 shadow-sm transition opacity-0 group-hover:opacity-100 cursor-pointer">
                            <i class="ti ti-trash text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Cart Summary & Checkout --}}
        <div class="border-t border-slate-200 bg-white p-5 shrink-0 shadow-[0_-4px_15px_rgba(0,0,0,0.03)] z-20">
            {{-- Summary rows --}}
            <div class="space-y-2.5 mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 font-medium">Subtotal</span>
                    <span class="text-sm font-bold text-slate-700" x-text="'Rp ' + Number(cartSubtotal).toLocaleString('id-ID')"></span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-dashed border-slate-300 mb-3"></div>

            {{-- Total --}}
            <div class="flex items-end justify-between mb-4">
                <span class="text-sm font-bold text-slate-800 uppercase tracking-wider">Total</span>
                <span class="text-2xl font-black text-[#0C9044]" x-text="'Rp ' + Number(cartTotal).toLocaleString('id-ID')"></span>
            </div>

             {{-- Checkout Button --}}
             <button aria-label="Checkout" @click.prevent="openCheckoutConfirm()"
                 class="w-full h-14 rounded-xl flex items-center justify-center gap-2 text-base font-bold text-white shadow-lg transition-all"
                 :class="cartItems.length > 0 ? 'bg-[#0C9044] hover:bg-green-700 hover:shadow-[#0C9044]/30 cursor-pointer hover:-translate-y-0.5' : 'bg-slate-300 cursor-not-allowed'"
                 :disabled="cartItems.length === 0">
                <span>BAYAR SEKARANG</span>
                <i class="ti ti-arrow-right text-xl"></i>
             </button>
        </div>
    </div>

    {{-- ===== VARIANT MODAL ===== --}}
    <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showModal = false" @keydown.escape.window="showModal = false">

            <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             x-ref="variantModal" role="dialog" aria-modal="true" aria-label="Pilih varian"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden flex flex-col max-h-[90vh]">

            {{-- Modal Header --}}
            <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between bg-slate-50/50">
                <div class="flex gap-3">
                    <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0">
                        <img :src="modalProduct?.product?.image_url ? '{{ asset('') }}' + modalProduct.product.image_url : '{{ asset('assets/image.png') }}'" class="w-8 h-8 object-contain mix-blend-multiply" onerror="this.src='{{ asset('assets/image.png') }}'">
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 leading-tight line-clamp-2" x-text="modalProduct?.product?.name"></h3>
                        <p class="text-[11px] text-slate-500 mt-1">Pilih ukuran & jumlah</p>
                    </div>
                </div>
                <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-200/50 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition shrink-0 cursor-pointer">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5 space-y-5 overflow-y-auto">
                {{-- Variant Select --}}
                <div>
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 block">Ukuran / Varian</label>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="v in modalProduct?.variants || []" :key="v.id">
                            <label class="relative flex items-center justify-between p-3 border rounded-xl cursor-pointer transition-all"
                                   :class="modalSelectedVariantId == v.id ? 'border-[#0C9044] bg-green-50 ring-1 ring-[#0C9044]' : 'border-slate-200 hover:border-slate-300'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="variant_selection" :value="v.id" x-model="modalSelectedVariantId" class="w-4 h-4 text-[#0C9044] focus:ring-[#0C9044] border-slate-300">
                                    <span class="text-sm font-semibold text-slate-700" x-text="v.variant_options.join(', ').replace('â€”', '—') || 'Regular'"></span>
                                </div>
                                <span class="text-sm font-bold text-[#0C9044]" x-text="'Rp ' + Number(v.final_price).toLocaleString('id-ID')"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="border-t border-slate-100"></div>

                {{-- Quantity --}}
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Jumlah</label>
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <button @click="modalQty > 1 && (modalQty--, (navigator.vibrate ? navigator.vibrate(8) : null))" :disabled="modalQty <= 1"
                                class="w-10 h-10 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 hover:text-[#0C9044] disabled:opacity-50 transition cursor-pointer">
                            <i class="ti ti-minus"></i>
                        </button>
                        <input type="number" x-model.number="modalQty" min="1" readonly
                               class="w-12 h-10 bg-transparent border-0 text-center text-base font-bold text-slate-800 focus:ring-0" />
                        <button @click="modalQty++ , (navigator.vibrate ? navigator.vibrate(8) : null)"
                                class="w-10 h-10 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-600 hover:text-[#0C9044] transition cursor-pointer">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 border-t border-slate-100 bg-white shrink-0">
                <button @click="addToCartFromModal()"
                        :disabled="!modalSelectedVariant || modalSelectedVariant.quantity <= 0"
                        class="w-full h-12 rounded-xl bg-[#0C9044] hover:bg-green-700 text-white text-base font-bold transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-between px-5 shadow-lg shadow-green-600/20">
                    <span>Tambah ke Keranjang</span>
                    <span x-text="'Rp ' + Number((modalSelectedVariant?.final_price || 0) * modalQty).toLocaleString('id-ID')"></span>
                </button>
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
                    const duration = this.shiftDuration;
                    this.isOpen = false;
                    this.openedAt = null;
                    localStorage.setItem('pos_shift_open', 'false');
                    localStorage.removeItem('pos_shift_opened_at');
                    if (this._timer) clearInterval(this._timer);
                    this.shiftDuration = '';
                    alert('Shift ditutup — Durasi: ' + duration);
                } else {
                    this.isOpen = true;
                    this.openedAt = new Date().toISOString();
                    localStorage.setItem('pos_shift_open', 'true');
                    localStorage.setItem('pos_shift_opened_at', this.openedAt);
                    this.startTimer();
                    alert('Shift dibuka');
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
            gridColOptions: [2, 3, 4, 5],
            allProducts: @json($productsWithDetails->values()),
            filteredProducts: [],
            searchQuery: '{{ $searchTerm }}',
            activeCategory: localStorage.getItem('pos_active_category') || 'All Products',

            gridCols: parseInt(localStorage.getItem('pos_grid_cols')) || 4,
            viewMode: localStorage.getItem('pos_view_mode') || 'grid',
            listSize: localStorage.getItem('pos_list_size') || 'normal',

            cartItems: @json($cartDetails),

            showModal: false,
            modalProduct: null,
            modalSelectedVariantId: null,
            modalQty: 1,

            barcodeMode: false,
            barcodeBuffer: '',
            lastKeyTime: 0,

            favorites: JSON.parse(localStorage.getItem('pos_favorites') || '[]'),
            mobileCartOpen: false,
            sidebarCollapsed: false,

            init() {
                this.filterProducts();
                if (!localStorage.getItem('pos_grid_cols')) {
                    this.gridCols = window.innerWidth < 640 ? 2 : window.innerWidth < 1024 ? 3 : 4;
                }
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    this.sidebarCollapsed = true;
                } else {
                    this.sidebarCollapsed = false;
                    this.$nextTick && this.$nextTick(() => { if (this.$refs && this.$refs.cartPanel) this.$refs.cartPanel.style.display = ''; });
                }

                try {
                    this.$watch && this.$watch('sidebarCollapsed', (v) => localStorage.setItem('sidebarCollapsed', v ? 'true' : 'false'));
                    this.$watch && this.$watch('activeCategory', (v) => localStorage.setItem('pos_active_category', v));
                } catch (e) {}
            },

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

            isFavorite(productId) {
                return this.favorites.includes(productId);
            },
            toggleFavorite(productId) {
                const idx = this.favorites.indexOf(productId);
                if (idx > -1) {
                    this.favorites.splice(idx, 1);
                } else {
                    this.favorites.push(productId);
                }
                localStorage.setItem('pos_favorites', JSON.stringify(this.favorites));
                if (this.activeCategory === 'Favorit') this.filterProducts();
            },

            detectBarcode(e) {
                if (!this.barcodeMode) return;
                if (e.key === 'Enter' && this.barcodeBuffer.length > 3) {
                    e.preventDefault();
                    this.searchQuery = this.barcodeBuffer;
                    this.filterProducts();
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

            filterProducts() {
                let products = [...this.allProducts];

                if (this.activeCategory === 'Favorit') {
                    products = products.filter(p => this.favorites.includes(p.product.id));
                } else if (this.activeCategory === 'Promo') {
                    products = products.filter(p => p.isPromo === 'yes');
                } else if (this.activeCategory !== 'All Products') {
                    const cat = this.activeCategory;
                    products = products.filter(p => {
                        if (p.product.category && p.product.category.category_name === cat) return true;
                        if (p.product.product_category && p.product.product_category.category_name === cat) return true;
                        return false;
                    });
                }

                if (this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase();
                    products = products.filter(p => p.product.name.toLowerCase().includes(q));
                }

                products.sort((a, b) => a.product.name.localeCompare(b.product.name, 'id'));
                this.filteredProducts = products;
            },

            openVariantModal(item) {
                if (item.variants && item.variants.length === 1) {
                    const variant = item.variants[0];
                    if (variant.quantity > 0 || true) { 
                        this.quickAddToCart(item.product.id, variant);
                        return;
                    }
                }

                this.modalProduct = item;
                this.modalQty = 1;
                if (item.variants && item.variants.length > 0) {
                    this.modalSelectedVariantId = item.variants[0].id;
                }
                this.showModal = true;
            },

            get modalSelectedVariant() {
                if (!this.modalProduct || !this.modalProduct.variants) return null;
                return this.modalProduct.variants.find(v => v.id == this.modalSelectedVariantId);
            },

            quickAddToCart(productId, variant) {
                this.sendAddToCart(productId, variant.id, 1);
            },

            addToCartFromModal() {
                if (!this.modalSelectedVariant || !this.modalProduct) return;
                this.sendAddToCart(this.modalProduct.product.id, this.modalSelectedVariant.id, this.modalQty);
                this.showModal = false;
            },

            sendAddToCart(productId, variantId, quantity) {
                fetch('{{ route("cart.add", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: quantity
                    })
                })
                .then(async response => {
                    // Cek apakah balasan dari server berupa JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // Jika bukan JSON (karena Controller melakukan Redirect 302),
                        // kita anggap sukses dan langsung muat ulang halaman.
                        window.location.reload();
                        return { handled: true };
                    }
                })
                .then(data => {
                    if (data.handled) return; // Halaman sedang dimuat ulang
                    
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Gagal menambahkan produk.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan atau server.');
                });
            },

            increaseQty(item) {
                this.updateQuantity(item.product_id, item.variant_id, 1);
            },

            decreaseQty(item) {
                if (item.quantity > 1) {
                    this.updateQuantity(item.product_id, item.variant_id, -1);
                } else {
                    this.removeCartItem(item);
                }
            },

            updateQuantity(productId, variantId, change) {
                const item = this.cartItems.find(i => i.variant_id == variantId);
                let currentQty = item ? item.quantity : 1;
                let newQty = currentQty + change;
                if (newQty < 1) newQty = 1;

                fetch('{{ route("pos.cart.update", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: newQty
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error_detail || data.error || 'Gagal memperbarui quantity');
                    }
                })
                .catch(error => alert(error.message));
            },

            removeCartItem(item) {
                if (!confirm("Hapus produk ini dari keranjang?")) return;
                fetch('{{ route("pos.cart.remove", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_id: item.product_id,
                        variant_id: item.variant_id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else alert(data.error || 'Gagal menghapus item.');
                })
                .catch(error => console.error('Error:', error));
            },

            openCheckoutConfirm() {
                if (!this.cartItems || this.cartItems.length === 0) return;
                window.location.href = '{{ route("pos.checkout") }}';
            },

            confirmClearCart() {
                if(confirm("Hapus SEMUA produk dari keranjang?")) {
                    this.clearCart();
                }
            },

            clearCart() {
                fetch('{{ route("cart.clear", [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(() => window.location.reload())
                .catch(err => console.error('Clear cart failed:', err));
            },

            closeCart() {
                this.mobileCartOpen = false;
                this.sidebarCollapsed = true;
                localStorage.setItem('sidebarCollapsed','true');
            },
            openCartAlpine() {
                this.mobileCartOpen = true;
                this.sidebarCollapsed = false;
                localStorage.setItem('sidebarCollapsed','false');
            },

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
            get cartTax() { return 0; },
            get cartTotal() {
                return this.cartSubtotal - this.cartDiscount + this.cartTax;
            },

            handleShortcut(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.querySelector('input[x-model="searchQuery"]')?.focus();
                }
                if (e.key === 'Escape') {
                    this.showModal = false;
                }
            }
        };
    }
</script>
@endsection