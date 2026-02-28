@extends('handai-manager.layouts.master')

@section('title', 'Stok Gudang')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .stk-table th { position: sticky; top: 0; z-index: 5; }
    .stk-row { transition: background-color 0.15s ease; }
    .stk-row:hover .stk-actions { opacity: 1; }
    .stk-actions { opacity: 0; transition: opacity 0.15s ease; }
    @media (max-width: 767px) { .stk-actions { opacity: 1; } }
    .stk-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .stk-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .stk-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .stk-card-stat { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto"
     x-data="{
        showFilter: {{ request()->hasAny(['search','category','status','almost_expired_threshold']) ? 'true' : 'false' }},
        deleteId: null, deleteName: '', showDeleteModal: false,
        showExpiredModal: false, expiredBatches: [],
        expiredData: @js($stocks->pluck('almost_expired_batches', 'id')),
        openExpired(id) {
            this.expiredBatches = this.expiredData[id] || [];
            this.showExpiredModal = true;
        }
     }">

    {{-- ── FLASH MESSAGES ── --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── HEADER ── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Stok Gudang</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">Monitoring persediaan bahan baku</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFilter = !showFilter"
                    class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium border rounded-lg transition cursor-pointer"
                    :class="showFilter ? 'bg-gray-100 border-gray-300 text-gray-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-600'">
                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
                @if(request()->hasAny(['search','category','status','almost_expired_threshold']))
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </button>
            <a href="{{ route('manager.inventory.stock.create') }}"
               class="h-9 inline-flex items-center gap-1.5 px-4 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Stok
            </a>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="stk-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Item</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($stockStats->total ?? 0) }}</p>
        </div>
        <div class="stk-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Ready</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($stockStats->ready ?? 0) }}</p>
        </div>
        <div class="stk-card-stat">
            <p class="text-[11px] font-medium text-amber-500 uppercase tracking-wider leading-none">Low Stock</p>
            <p class="text-xl font-bold text-amber-600 mt-1.5 leading-none tabular-nums">{{ number_format($stockStats->low_stock ?? 0) }}</p>
        </div>
        <div class="stk-card-stat">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider leading-none">Habis</p>
            <p class="text-xl font-bold text-red-500 mt-1.5 leading-none tabular-nums">{{ number_format($stockStats->out_of_stock ?? 0) }}</p>
        </div>
    </div>

    {{-- ── FILTER ── --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.inventory.stock') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bahan..."
                               class="stk-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Kategori</label>
                    <select name="category" class="stk-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        @foreach ($stockCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="stk-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="ready" {{ request('status')==='ready'?'selected':'' }}>Ready</option>
                        <option value="low_stock" {{ request('status')==='low_stock'?'selected':'' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('status')==='out_of_stock'?'selected':'' }}>Habis</option>
                    </select>
                </div>
                <div class="min-w-[120px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Near Exp. (hari)</label>
                    <input type="number" name="almost_expired_threshold" min="1" max="365"
                           value="{{ request('almost_expired_threshold', 3) }}" class="stk-input" />
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Terapkan</button>
                    <a href="{{ route('manager.inventory.stock') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>
  
    {{-- ── MAIN TABLE ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $stocks->firstItem() ?? 0 }}–{{ $stocks->lastItem() ?? 0 }}</span> dari {{ $stocks->total() }} item
            </p>
            @if(request()->hasAny(['search','category','status','almost_expired_threshold']))
            <a href="{{ route('manager.inventory.stock') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] stk-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Nama Bahan</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Harga/Unit</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Stok</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell">Exp. Duration</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Hampir Exp.</th>
                        <th class="text-center py-2.5 px-3 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider w-[80px]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $idx => $stock)
                    @php
                        $status = $stock->calculated_status ?? ($stock->status ?? 'Ready');
                        $isOutOfStock = $status === 'Out of Stock';
                        $isLow = $status === 'Low Stock';
                        $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40';
                        $rowBg = $isOutOfStock ? 'bg-red-50/30' : ($isLow ? 'bg-amber-50/20' : $stripe);
                    @endphp
                    <tr class="stk-row {{ $rowBg }} border-b border-gray-50 last:border-b-0">
                        {{-- Name --}}
                        <td class="py-3 px-5">
                            <p class="font-medium text-gray-800 leading-snug">{{ $stock->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-none">{{ $stock->unit->symbol ?? '-' }}</p>
                        </td>
                        {{-- Category --}}
                        <td class="py-3 px-4 text-gray-500">{{ $stock->category->name ?? 'Uncategorized' }}</td>
                        {{-- Price --}}
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-gray-700">Rp{{ number_format($stock->price_per_unit ?? 0, 0, ',', '.') }}</td>
                        {{-- Quantity --}}
                        <td class="py-3 px-4 text-right">
                            <span class="font-semibold text-gray-700 tabular-nums">{{ number_format($stock->unit_qty, $stock->unit_qty == intval($stock->unit_qty) ? 0 : 1) }}</span>
                            <span class="text-gray-400 text-[11px] ml-0.5">{{ $stock->unit->symbol ?? '' }}</span>
                        </td>
                        {{-- Status --}}
                        <td class="py-3 px-4 text-center">
                            @if($isOutOfStock)
                            <span class="stk-badge bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Habis</span>
                            @elseif($isLow)
                            <span class="stk-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Low</span>
                            @else
                            <span class="stk-badge bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                            @endif
                        </td>
                        {{-- Expired Duration --}}
                        <td class="py-3 px-4 text-gray-500 hidden xl:table-cell">{{ $stock->display_expired_duration ?? ($stock->expired_duration ? $stock->expired_duration . ' hari' : '—') }}</td>
                        {{-- Almost Expired --}}
                        <td class="py-3 px-4 text-right">
                            @if($stock->almost_expired > 0)
                            <button @click="openExpired({{ $stock->id }})"
                                    class="text-amber-600 font-semibold hover:text-amber-700 tabular-nums cursor-pointer">
                                {{ number_format($stock->almost_expired, $stock->almost_expired == intval($stock->almost_expired) ? 0 : 1) }}
                                <span class="block text-[10px] text-amber-400 font-normal mt-0.5 leading-none">{{ $stock->days_left }} hari lagi</span>
                            </button>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="py-3 px-3">
                            <div class="stk-actions flex items-center justify-center gap-0.5">
                                <a href="{{ route('manager.inventory.stock.batch.create', $stock->id) }}"
                                   class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition" title="Tambah Batch">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </a>
                                <button @click="deleteId = {{ $stock->id }}; deleteName = '{{ addslashes($stock->name) }}'; showDeleteModal = true"
                                        class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Belum ada data stok</p>
                            <p class="text-xs text-gray-300 mt-0.5">Tambahkan bahan baku untuk memulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($stocks as $stock)
            @php
                $status = $stock->calculated_status ?? ($stock->status ?? 'Ready');
                $isOutOfStock = $status === 'Out of Stock';
                $isLow = $status === 'Low Stock';
                $accent = $isOutOfStock ? 'border-l-red-400' : ($isLow ? 'border-l-amber-400' : 'border-l-emerald-400');
            @endphp
            <div class="p-4 border-l-[3px] {{ $accent }} {{ $isOutOfStock ? 'bg-red-50/20' : '' }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 text-[13px] leading-snug truncate">{{ $stock->name }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $stock->category->name ?? 'Uncategorized' }}</p>
                    </div>
                    @if($isOutOfStock)
                    <span class="stk-badge bg-red-50 text-red-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Habis</span>
                    @elseif($isLow)
                    <span class="stk-badge bg-amber-50 text-amber-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Low</span>
                    @else
                    <span class="stk-badge bg-emerald-50 text-emerald-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12px]">
                    <div><span class="text-gray-400">Stok:</span> <span class="font-semibold text-gray-700">{{ number_format($stock->unit_qty, $stock->unit_qty == intval($stock->unit_qty) ? 0 : 1) }} {{ $stock->unit->symbol ?? '' }}</span></div>
                    <div><span class="text-gray-400">Harga:</span> <span class="font-semibold text-gray-700">Rp{{ number_format($stock->price_per_unit ?? 0, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-400">Exp. Dur:</span> <span class="text-gray-600">{{ $stock->display_expired_duration ?? '-' }}</span></div>
                    <div>
                        <span class="text-gray-400">Near Exp:</span>
                        @if($stock->almost_expired > 0)
                        <button @click="openExpired({{ $stock->id }})" class="text-amber-600 font-semibold hover:text-amber-700 cursor-pointer">{{ number_format($stock->almost_expired, 0) }}</button>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-3 pt-2.5 border-t border-gray-100">
                    <a href="{{ route('manager.inventory.stock.batch.create', $stock->id) }}" class="text-[11px] text-emerald-500 hover:text-emerald-600 font-medium inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Batch
                    </a>
                    <button @click="deleteId = {{ $stock->id }}; deleteName = '{{ addslashes($stock->name) }}'; showDeleteModal = true"
                            class="text-[11px] text-red-400 hover:text-red-600 font-medium inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </div>
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Belum ada data stok.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($stocks->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $stocks->currentPage() }} / {{ $stocks->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($stocks->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $stocks->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($stocks->getUrlRange(max(1, $stocks->currentPage()-2), min($stocks->lastPage(), $stocks->currentPage()+2)) as $page => $url)
                    @if($page == $stocks->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($stocks->hasMorePages())
                <a href="{{ $stocks->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                @else
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── APPROVED R&D SECTION ── --}}
    @if($approvedProjects->isNotEmpty())
    <div class="mt-8">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
            <h2 class="text-[15px] font-bold text-gray-800">Permintaan R&D yang Telah Disetujui</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($approvedProjects as $project)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-[14px] font-bold text-gray-800">{{ $project->id }}</h3>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $project->deskripsi }}</p>
                    </div>
                    <span class="stk-badge bg-blue-50 text-blue-600"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Approved</span>
                </div>
                <div class="overflow-x-auto mb-4 -mx-1">
                    <table class="w-full text-[12px]">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Bahan</th>
                                <th class="text-right py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Jumlah</th>
                                <th class="text-left py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Satuan</th>
                                <th class="text-right py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($project->stockUsages as $usage)
                            <tr class="border-b border-gray-50">
                                <td class="py-1.5 px-1 text-gray-700">{{ $usage->stock->name ?? $usage->manual_name }}</td>
                                <td class="py-1.5 px-1 text-right tabular-nums text-gray-600">{{ $usage->quantity_used }}</td>
                                <td class="py-1.5 px-1 text-gray-500">{{ $usage->unit->symbol ?? '-' }}</td>
                                <td class="py-1.5 px-1 text-right tabular-nums text-gray-600">{{ $usage->cost }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('manager.inventory.stock.batch.createFromRnd', $project->id) }}"
                   class="h-8 inline-flex items-center gap-1.5 px-4 text-[12px] font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Isi Stok dari R&D Ini
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── EXPIRED & STORED SECTION ── --}}
    @if($stocks->where('stored_expired', '>', 0)->count())
    <div class="mt-8">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-red-500 rounded-full"></div>
            <h2 class="text-[15px] font-bold text-red-700">Stok Expired & Masih Disimpan</h2>
        </div>
        <div class="bg-white rounded-xl border border-red-100 shadow-sm overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="bg-red-50/60 border-b border-red-100">
                            <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-red-400 uppercase tracking-wider">Nama Stok</th>
                            <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-red-400 uppercase tracking-wider">Jml Expired</th>
                            <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-red-400 uppercase tracking-wider">Qty Disimpan</th>
                            <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-red-400 uppercase tracking-wider">Unit</th>
                            <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-red-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stocks->where('stored_expired', '>', 0) as $stock)
                        <tr class="border-b border-red-50 bg-red-50/20">
                            <td class="py-3 px-5 font-medium text-gray-800">{{ $stock->name }}</td>
                            <td class="py-3 px-4 text-right tabular-nums text-red-600 font-semibold">{{ number_format($stock->expired, 0) }}</td>
                            <td class="py-3 px-4 text-right tabular-nums text-red-600 font-semibold">{{ number_format($stock->stored_expired, 0) }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $stock->unit->symbol ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                <form method="POST" action="{{ route('manager.inventory.stock.reduceExpiredStored', $stock->id) }}">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Kurangi stok expired yang masih disimpan?')"
                                            class="h-7 px-3 text-[11px] font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition cursor-pointer">
                                        Kurangi {{ number_format($stock->stored_expired, 0) }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Mobile expired cards --}}
            <div class="md:hidden divide-y divide-red-50">
                @foreach ($stocks->where('stored_expired', '>', 0) as $stock)
                <div class="p-4 border-l-[3px] border-l-red-400 bg-red-50/20">
                    <p class="font-semibold text-gray-800 text-[13px]">{{ $stock->name }}</p>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[12px] mt-2">
                        <div><span class="text-gray-400">Expired:</span> <span class="font-semibold text-red-600">{{ number_format($stock->expired, 0) }}</span></div>
                        <div><span class="text-gray-400">Disimpan:</span> <span class="font-semibold text-red-600">{{ number_format($stock->stored_expired, 0) }} {{ $stock->unit->symbol ?? '' }}</span></div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-red-100">
                        <form method="POST" action="{{ route('manager.inventory.stock.reduceExpiredStored', $stock->id) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Kurangi stok expired?')"
                                    class="h-7 px-3 text-[11px] font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition cursor-pointer">
                                Kurangi {{ number_format($stock->stored_expired, 0) }}
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── DELETE MODAL ── --}}
    <div x-show="showDeleteModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
         @click.self="showDeleteModal = false" @keydown.escape.window="showDeleteModal = false">
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-xl w-full max-w-[360px] mx-4 p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-[15px] font-bold text-gray-800 mb-1">Hapus Stok?</h3>
            <p class="text-[13px] text-gray-400 mb-5 leading-relaxed">Stok <span class="font-semibold text-gray-600" x-text="deleteName"></span> akan dihapus permanen.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 h-10 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-500 hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <form :action="'/manager/inventory/stock/' + deleteId" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full h-10 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[13px] font-semibold transition cursor-pointer">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── EXPIRED BATCH DETAIL MODAL ── --}}
    <div x-show="showExpiredModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
         @click.self="showExpiredModal = false" @keydown.escape.window="showExpiredModal = false">
        <div x-show="showExpiredModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-xl w-full max-w-[400px] mx-4 p-6 relative">
            <button @click="showExpiredModal = false" class="absolute top-3 right-3 w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-gray-800">Detail Hampir Expired</h3>
            </div>
            <template x-if="expiredBatches.length === 0">
                <p class="text-[13px] text-gray-400 py-4 text-center">Tidak ada batch yang hampir expired.</p>
            </template>
            <div class="space-y-2 max-h-[300px] overflow-y-auto">
                <template x-for="(batch, idx) in expiredBatches" :key="idx">
                    <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-lg">
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-gray-500">Batch ID</span>
                            <span class="font-semibold text-gray-700" x-text="'#' + batch.id"></span>
                        </div>
                        <div class="flex items-center justify-between text-[12px] mt-1">
                            <span class="text-gray-500">Kuantitas</span>
                            <span class="font-semibold text-amber-600" x-text="batch.qty + ' ' + (batch.unit || '')"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
