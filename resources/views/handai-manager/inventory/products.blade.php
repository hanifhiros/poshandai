@extends('layouts.master')

@section('title', 'Produk')

@section('content')
@php $tab = $tab ?? 'produk_jadi'; @endphp
<style>
    [x-cloak] { display: none !important; }
    .prd-table th { position: sticky; top: 0; z-index: 5; }
    .prd-row { transition: background-color 0.15s ease; }
    .prd-row:hover .prd-actions { opacity: 1; }
    .prd-actions { opacity: 0; transition: opacity 0.15s ease; }
    @media (max-width: 767px) { .prd-actions { opacity: 1; } }
    .prd-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .prd-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .prd-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .prd-card-stat { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
    .tab-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; color: #6b7280; cursor: pointer; transition: all .15s; text-decoration: none; }
    .tab-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
    .tab-btn.active { background: #ecfdf5; border-color: #10b981; color: #059669; }
    .tab-btn.active-purple { background: #f5f3ff; border-color: #8b5cf6; color: #7c3aed; }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto"
     x-data="{
        @if($tab === 'produk_jadi')
        showFilter: {{ request()->hasAny(['search','status','category','expired_range']) ? 'true' : 'false' }},
        showExpiredModal: false, expiredBatches: [],
        expiredData: @js(isset($variants) ? $variants->pluck('nearly_expired_batches', 'id') : []),
        openExpired(id) {
            this.expiredBatches = this.expiredData[id] || [];
            this.showExpiredModal = true;
        }
        @else
        showFilter: {{ request()->hasAny(['search','status']) ? 'true' : 'false' }}
        @endif
     }">

    {{-- â”€â”€ FLASH MESSAGES â”€â”€ --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2" x-data x-init="setTimeout(() => $el.remove(), 4000)">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- â”€â”€ HEADER â”€â”€ --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Produk</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">Monitoring produk jadi & produk setengah jadi</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFilter = !showFilter"
                    class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium border rounded-lg transition cursor-pointer"
                    :class="showFilter ? 'bg-gray-100 border-gray-300 text-gray-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-600'">
                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
                @if(request()->hasAny(['search','status','category','expired_range']))
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </button>
            @if($tab === 'produk_jadi')
            @include('handai-manager.partials.import-export-modal', ['type' => 'product', 'label' => 'Produk Jadi'])
            <a href="{{ route('manager.products.create') }}"
               class="h-9 inline-flex items-center gap-1.5 px-4 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Produk
            </a>
            @else
            <a href="{{ route('manager.inventory.semi-finished.create') }}"
               class="h-9 inline-flex items-center gap-1.5 px-4 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Setengah Jadi
            </a>
            @endif
        </div>
    </div>

    {{-- â”€â”€ TAB TOGGLE â”€â”€ --}}
    <div class="flex items-center gap-2 mb-5">
        <a href="{{ route('manager.inventory.products', ['tab' => 'produk_jadi']) }}"
           class="tab-btn {{ $tab === 'produk_jadi' ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Produk Jadi
        </a>
        <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}"
           class="tab-btn {{ $tab === 'setengah_jadi' ? 'active-purple' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
            Produk Setengah Jadi
        </a>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         TAB: PRODUK JADI
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($tab === 'produk_jadi')

    {{-- â”€â”€ STAT CARDS â”€â”€ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Varian</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($productStats->total ?? 0) }}</p>
        </div>
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Ready</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($productStats->ready ?? 0) }}</p>
        </div>
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-amber-500 uppercase tracking-wider leading-none">Low Stock</p>
            <p class="text-xl font-bold text-amber-600 mt-1.5 leading-none tabular-nums">{{ number_format($productStats->low_stock ?? 0) }}</p>
        </div>
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider leading-none">Habis</p>
            <p class="text-xl font-bold text-red-500 mt-1.5 leading-none tabular-nums">{{ number_format($productStats->out_of_stock ?? 0) }}</p>
        </div>
    </div>

    {{-- â”€â”€ FILTER â”€â”€ --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.inventory.products') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..."
                               class="prd-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="prd-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="Ready" {{ request('status')==='Ready'?'selected':'' }}>Ready</option>
                        <option value="Low Stock" {{ request('status')==='Low Stock'?'selected':'' }}>Low Stock</option>
                        <option value="Out of Stock" {{ request('status')==='Out of Stock'?'selected':'' }}>Habis</option>
                    </select>
                </div>
                <div class="min-w-[120px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Near Exp. (hari)</label>
                    <input type="number" name="expired_range" min="1" max="365"
                           value="{{ request('expired_range', 3) }}" class="prd-input" />
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Terapkan</button>
                    <a href="{{ route('manager.inventory.products') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- â”€â”€ MAIN TABLE â”€â”€ --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $variants->firstItem() ?? 0 }}â€“{{ $variants->lastItem() ?? 0 }}</span> dari {{ $variants->total() }} varian
            </p>
            @if(request()->hasAny(['search','status','category','expired_range']))
            <a href="{{ route('manager.inventory.products') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] prd-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Produk</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Varian</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Stok</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Harga</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">HPP</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Near Exp.</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-center py-2.5 px-3 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider w-[80px]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variants as $idx => $variant)
                    @php
                        $status = 'Ready';
                        if ($variant->quantity == 0) $status = 'Out of Stock';
                        elseif ($variant->quantity < 10) $status = 'Low Stock';
                        $isOut = $status === 'Out of Stock';
                        $isLow = $status === 'Low Stock';
                        $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40';
                        $rowBg = $isOut ? 'bg-red-50/30' : ($isLow ? 'bg-amber-50/20' : $stripe);
                    @endphp
                    <tr class="prd-row {{ $rowBg }} border-b border-gray-50 last:border-b-0">
                        {{-- Product --}}
                        <td class="py-3 px-5">
                            <p class="font-medium text-gray-800 leading-snug">{{ $variant->product->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-none">{{ $variant->product->category->category_name ?? 'Uncategorized' }}</p>
                        </td>
                        {{-- Type --}}
                        <td class="py-3 px-4 text-center">
                            <span class="prd-badge bg-emerald-50 text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Produk Jadi
                            </span>
                        </td>
                        {{-- Variant --}}
                        <td class="py-3 px-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($variant->variantOptions->take(3) as $opt)
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">{{ $opt->name }}</span>
                                @endforeach
                                @if($variant->variantOptions->count() > 3)
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-400 rounded text-[10px]">+{{ $variant->variantOptions->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        {{-- Stock --}}
                        <td class="py-3 px-4 text-right">
                            <span class="font-semibold text-gray-700 tabular-nums">{{ number_format($variant->quantity) }}</span>
                        </td>
                        {{-- Price --}}
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-gray-700">Rp{{ number_format($variant->price, 0, ',', '.') }}</td>
                        {{-- HPP --}}
                        <td class="py-3 px-4 text-right tabular-nums text-gray-500 hidden lg:table-cell">Rp{{ number_format($variant->hpp, 0, ',', '.') }}</td>
                        {{-- Nearly Expired --}}
                        <td class="py-3 px-4 text-right">
                            @if($variant->nearly_expired > 0)
                            <button @click="openExpired({{ $variant->id }})"
                                    class="text-amber-600 font-semibold hover:text-amber-700 tabular-nums cursor-pointer">
                                {{ number_format($variant->nearly_expired) }}
                            </button>
                            @else
                            <span class="text-gray-300">â€”</span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="py-3 px-4 text-center">
                            @if($isOut)
                            <span class="prd-badge bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Habis</span>
                            @elseif($isLow)
                            <span class="prd-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Low</span>
                            @else
                            <span class="prd-badge bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="py-3 px-3">
                            <div class="prd-actions flex items-center justify-center gap-0.5">
                                <a href="{{ route('manager.products.edit', $variant->product->id) }}"
                                   class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('manager.variants.destroy', $variant->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus varian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus">
                                        <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Belum ada data produk</p>
                            <p class="text-xs text-gray-300 mt-0.5">Tambahkan produk untuk memulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($variants as $variant)
            @php
                $status = 'Ready';
                if ($variant->quantity == 0) $status = 'Out of Stock';
                elseif ($variant->quantity < 10) $status = 'Low Stock';
                $isOut = $status === 'Out of Stock';
                $isLow = $status === 'Low Stock';
                $accent = $isOut ? 'border-l-red-400' : ($isLow ? 'border-l-amber-400' : 'border-l-emerald-400');
            @endphp
            <div class="p-4 border-l-[3px] {{ $accent }} {{ $isOut ? 'bg-red-50/20' : '' }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 text-[13px] leading-snug truncate">{{ $variant->product->name }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $variant->variantOptions->pluck('name')->implode(', ') ?: 'â€”' }}</p>
                    </div>
                    @if($isOut)
                    <span class="prd-badge bg-red-50 text-red-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Habis</span>
                    @elseif($isLow)
                    <span class="prd-badge bg-amber-50 text-amber-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Low</span>
                    @else
                    <span class="prd-badge bg-emerald-50 text-emerald-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12px]">
                    <div><span class="text-gray-400">Stok:</span> <span class="font-semibold text-gray-700">{{ number_format($variant->quantity) }}</span></div>
                    <div><span class="text-gray-400">Harga:</span> <span class="font-semibold text-gray-700">Rp{{ number_format($variant->price, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-400">HPP:</span> <span class="text-gray-600">Rp{{ number_format($variant->hpp, 0, ',', '.') }}</span></div>
                    <div>
                        <span class="text-gray-400">Near Exp:</span>
                        @if($variant->nearly_expired > 0)
                        <button @click="openExpired({{ $variant->id }})" class="text-amber-600 font-semibold hover:text-amber-700 cursor-pointer">{{ number_format($variant->nearly_expired) }}</button>
                        @else
                        <span class="text-gray-300">â€”</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-3 pt-2.5 border-t border-gray-100">
                    <a href="{{ route('manager.products.edit', $variant->product->id) }}" class="text-[11px] text-blue-500 hover:text-blue-600 font-medium inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form action="{{ route('manager.variants.destroy', $variant->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus varian ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[11px] text-red-400 hover:text-red-600 font-medium inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Belum ada data produk.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($variants->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $variants->currentPage() }} / {{ $variants->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($variants->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $variants->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($variants->getUrlRange(max(1, $variants->currentPage()-2), min($variants->lastPage(), $variants->currentPage()+2)) as $page => $url)
                    @if($page == $variants->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($variants->hasMorePages())
                <a href="{{ $variants->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

    {{-- â”€â”€ EXPIRED VARIANTS SECTION â”€â”€ --}}
    @if($expiredVariants->count())
    <div class="mt-8">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-red-500 rounded-full"></div>
            <h2 class="text-[15px] font-bold text-red-700">Varian Expired yang Masih Disimpan</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($expiredVariants as $item)
            @php $variant = $item['variant']; $history = $item['history']; @endphp
            <div class="bg-white rounded-xl border border-red-100 shadow-sm p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-bold text-gray-800 text-[14px]">{{ $variant->product->name }}</p>
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $variant->variantOptions->pluck('name')->implode(', ') }}</p>
                    </div>
                    <span class="prd-badge bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Expired</span>
                </div>
                <div class="text-[12px] text-gray-500 mb-4">
                    Jumlah tersimpan: <span class="font-semibold text-red-600">{{ $history->quantity_produced }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <form action="{{ route('manager.products.variant.discard', $history->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="h-8 px-4 text-[12px] font-semibold text-white bg-red-500 hover:bg-red-600 rounded-lg transition cursor-pointer">Buang</button>
                    </form>
                    <form action="{{ route('manager.products.variant.ignore', $history->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="h-8 px-4 text-[12px] font-medium text-gray-500 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition cursor-pointer">Biarkan</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if($expiredVariants->hasPages())
        <div class="mt-4 flex justify-end">
            <div class="flex items-center gap-1">
                @if($expiredVariants->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $expiredVariants->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @if($expiredVariants->hasMorePages())
                <a href="{{ $expiredVariants->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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
    @endif

    {{-- â”€â”€ EXPIRED BATCH DETAIL MODAL â”€â”€ --}}
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
                            <span class="font-semibold text-amber-600" x-text="batch.qty"></span>
                        </div>
                        <div class="flex items-center justify-between text-[12px] mt-1">
                            <span class="text-gray-500">Tanggal Produksi</span>
                            <span class="text-gray-600" x-text="batch.date"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @endif {{-- end tab produk_jadi --}}

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         TAB: PRODUK SETENGAH JADI
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($tab === 'setengah_jadi')

    {{-- â”€â”€ STAT CARDS â”€â”€ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Produk</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($sfpStats->total ?? 0) }}</p>
        </div>
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Ready</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($sfpStats->ready ?? 0) }}</p>
        </div>
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-amber-500 uppercase tracking-wider leading-none">Hampir Habis</p>
            <p class="text-xl font-bold text-amber-600 mt-1.5 leading-none tabular-nums">{{ number_format($sfpStats->low_stock ?? 0) }}</p>
        </div>
        <div class="prd-card-stat">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider leading-none">Habis</p>
            <p class="text-xl font-bold text-red-500 mt-1.5 leading-none tabular-nums">{{ number_format($sfpStats->out_of_stock ?? 0) }}</p>
        </div>
    </div>

    {{-- â”€â”€ FILTER â”€â”€ --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.inventory.products') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <input type="hidden" name="tab" value="setengah_jadi" />
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk setengah jadi..."
                               class="prd-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="prd-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="Ready" {{ request('status')==='Ready'?'selected':'' }}>Ready</option>
                        <option value="Low Stock" {{ request('status')==='Low Stock'?'selected':'' }}>Hampir Habis</option>
                        <option value="Out of Stock" {{ request('status')==='Out of Stock'?'selected':'' }}>Habis</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition cursor-pointer">Terapkan</button>
                    <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- â”€â”€ MAIN TABLE â”€â”€ --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $semiFinishedProducts->firstItem() ?? 0 }}â€“{{ $semiFinishedProducts->lastItem() ?? 0 }}</span> dari {{ $semiFinishedProducts->total() }} produk
            </p>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}" class="text-[11px] text-violet-600 hover:text-violet-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] prd-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Tipe</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Satuan</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Stok</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Min Stok</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">HPP/Unit</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Upah/Batch</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Output/Batch</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Bahan</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-center py-2.5 px-3 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider w-[100px]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semiFinishedProducts as $idx => $sfp)
                    @php
                        $status = $sfp->stock_status; // 'Tersedia', 'Hampir Habis', 'Habis'
                        $isOut  = $status === 'Habis';
                        $isLow  = $status === 'Hampir Habis';
                        $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40';
                        $rowBg  = $isOut ? 'bg-red-50/30' : ($isLow ? 'bg-amber-50/20' : $stripe);
                    @endphp
                    <tr class="prd-row {{ $rowBg }} border-b border-gray-50 last:border-b-0">
                        {{-- Name --}}
                        <td class="py-3 px-5">
                            <p class="font-medium text-gray-800 leading-snug">{{ $sfp->name }}</p>
                            @if($sfp->description)
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-none truncate max-w-[250px]">{{ $sfp->description }}</p>
                            @endif
                        </td>
                        {{-- Type badge --}}
                        <td class="py-3 px-4 text-center">
                            <span class="prd-badge bg-violet-50 text-violet-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>Setengah Jadi
                            </span>
                        </td>
                        {{-- Unit --}}
                        <td class="py-3 px-4 text-gray-600">{{ $sfp->unit?->symbol ?? '-' }}</td>
                        {{-- Stock --}}
                        <td class="py-3 px-4 text-right">
                            <span class="font-semibold tabular-nums {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-gray-700') }}">
                                {{ number_format($sfp->current_qty, 1) }}
                            </span>
                            <span class="text-[10px] text-gray-400 ml-0.5">{{ $sfp->unit?->symbol ?? '' }}</span>
                        </td>
                        {{-- Min Stock --}}
                        <td class="py-3 px-4 text-right tabular-nums text-[12px] hidden lg:table-cell">
                            @if($sfp->min_stock > 0)
                            <span class="{{ $sfp->current_qty <= $sfp->min_stock ? 'text-red-500 font-semibold' : 'text-gray-500' }}">{{ number_format($sfp->min_stock, 1) }}</span>
                            @else
                            <span class="text-gray-300">â€”</span>
                            @endif
                        </td>
                        {{-- HPP/Unit --}}
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-emerald-700 hidden lg:table-cell">Rp{{ number_format($sfp->price_per_unit, 0, ',', '.') }}</td>
                        {{-- Labor/Batch --}}
                        <td class="py-3 px-4 text-right tabular-nums text-gray-600 hidden lg:table-cell">Rp{{ number_format($sfp->labor_cost, 0, ',', '.') }}</td>
                        {{-- Output/Batch --}}
                        <td class="py-3 px-4 text-right tabular-nums text-gray-600 hidden lg:table-cell">{{ number_format($sfp->output_qty, 1) }}</td>
                        {{-- Materials count --}}
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                {{ $sfp->materials->count() }}
                            </span>
                        </td>
                        {{-- Status --}}
                        <td class="py-3 px-4 text-center">
                            @if($isOut)
                            <span class="prd-badge bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Habis</span>
                            @elseif($isLow)
                            <span class="prd-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Low</span>
                            @else
                            <span class="prd-badge bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="py-3 px-3">
                            <div class="prd-actions flex items-center justify-center gap-0.5">
                                {{-- Produce --}}
                                <a href="{{ route('manager.inventory.semi-finished.produce', $sfp->id) }}"
                                   class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-amber-500 hover:bg-amber-50 transition" title="Produksi">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg>
                                </a>
                                {{-- Edit --}}
                                <a href="{{ route('manager.inventory.semi-finished.edit', $sfp->id) }}"
                                   class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                {{-- Delete --}}
                                <form action="{{ route('manager.inventory.semi-finished.destroy', $sfp->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus produk setengah jadi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus">
                                        <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Belum ada produk setengah jadi</p>
                            <p class="text-xs text-gray-300 mt-0.5">Tambahkan produk setengah jadi untuk memulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($semiFinishedProducts as $sfp)
            @php
                $status = $sfp->stock_status;
                $isOut  = $status === 'Habis';
                $isLow  = $status === 'Hampir Habis';
                $accent = $isOut ? 'border-l-red-400' : ($isLow ? 'border-l-amber-400' : 'border-l-violet-400');
            @endphp
            <div class="p-4 border-l-[3px] {{ $accent }} {{ $isOut ? 'bg-red-50/20' : '' }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="prd-badge bg-violet-50 text-violet-600 text-[9px]">PSJ</span>
                            <p class="font-semibold text-gray-800 text-[13px] leading-snug truncate">{{ $sfp->name }}</p>
                        </div>
                        @if($sfp->description)
                        <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $sfp->description }}</p>
                        @endif
                    </div>
                    @if($isOut)
                    <span class="prd-badge bg-red-50 text-red-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Habis</span>
                    @elseif($isLow)
                    <span class="prd-badge bg-amber-50 text-amber-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Low</span>
                    @else
                    <span class="prd-badge bg-emerald-50 text-emerald-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12px]">
                    <div><span class="text-gray-400">Stok:</span> <span class="font-semibold text-gray-700">{{ number_format($sfp->current_qty, 1) }} {{ $sfp->unit?->symbol ?? '' }}</span></div>
                    <div><span class="text-gray-400">HPP:</span> <span class="font-semibold text-emerald-700">Rp{{ number_format($sfp->price_per_unit, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-400">Min Stok:</span> <span class="text-gray-600">{{ $sfp->min_stock > 0 ? number_format($sfp->min_stock, 1) : 'â€”' }}</span></div>
                    <div><span class="text-gray-400">Bahan:</span> <span class="text-gray-600">{{ $sfp->materials->count() }} item</span></div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-3 pt-2.5 border-t border-gray-100">
                    <a href="{{ route('manager.inventory.semi-finished.produce', $sfp->id) }}" class="text-[11px] text-amber-500 hover:text-amber-600 font-medium inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg>
                        Produksi
                    </a>
                    <a href="{{ route('manager.inventory.semi-finished.edit', $sfp->id) }}" class="text-[11px] text-blue-500 hover:text-blue-600 font-medium inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form action="{{ route('manager.inventory.semi-finished.destroy', $sfp->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[11px] text-red-400 hover:text-red-600 font-medium inline-flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Belum ada produk setengah jadi.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($semiFinishedProducts->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $semiFinishedProducts->currentPage() }} / {{ $semiFinishedProducts->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($semiFinishedProducts->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $semiFinishedProducts->appends(['tab' => 'setengah_jadi'])->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($semiFinishedProducts->appends(['tab' => 'setengah_jadi'])->getUrlRange(max(1, $semiFinishedProducts->currentPage()-2), min($semiFinishedProducts->lastPage(), $semiFinishedProducts->currentPage()+2)) as $page => $url)
                    @if($page == $semiFinishedProducts->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-violet-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($semiFinishedProducts->hasMorePages())
                <a href="{{ $semiFinishedProducts->appends(['tab' => 'setengah_jadi'])->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

    @endif {{-- end tab setengah_jadi --}}

</div>
@endsection
