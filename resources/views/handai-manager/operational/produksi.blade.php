@extends('handai-manager.layouts.master')

@section('title', 'Riwayat Produksi')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .pdk-table th { position: sticky; top: 0; z-index: 5; }
    .pdk-row { transition: background-color 0.15s ease; }
    .pdk-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .pdk-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .pdk-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .pdk-card-stat { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto"
     x-data="{
        showFilter: {{ request()->hasAny(['search','from','to']) ? 'true' : 'false' }},
        showDetail: false,
        detailData: null,
        openDetail(d) { this.detailData = d; this.showDetail = true; }
     }">

    {{-- ── FLASH ── --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── HEADER ── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Riwayat Produksi</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">Catatan seluruh aktivitas produksi</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFilter = !showFilter"
                    class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium border rounded-lg transition cursor-pointer"
                    :class="showFilter ? 'bg-gray-100 border-gray-300 text-gray-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-600'">
                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
                @if(request()->hasAny(['search','from','to']))
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </button>
            <a href="{{ route('manager.operational.produksi.create') }}"
               class="h-9 inline-flex items-center gap-1.5 px-4 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Produksi
            </a>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-5">
        <div class="pdk-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Produksi</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($prodStats->total ?? 0) }}</p>
        </div>
        <div class="pdk-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Total Qty</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($prodStats->total_qty ?? 0) }}</p>
        </div>
        <div class="pdk-card-stat hidden lg:block">
            <p class="text-[11px] font-medium text-blue-500 uppercase tracking-wider leading-none">PIC Terlibat</p>
            <p class="text-xl font-bold text-blue-600 mt-1.5 leading-none tabular-nums">{{ number_format($prodStats->total_pic ?? 0) }}</p>
        </div>
    </div>

    {{-- ── FILTER ── --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.operational.produksi') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk / PIC..."
                               class="pdk-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="pdk-input" />
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="pdk-input" />
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Terapkan</button>
                    <a href="{{ route('manager.operational.produksi') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── MAIN TABLE ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $productions->firstItem() ?? 0 }}–{{ $productions->lastItem() ?? 0 }}</span> dari {{ $productions->total() }} catatan
            </p>
            @if(request()->hasAny(['search','from','to']))
            <a href="{{ route('manager.operational.produksi') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] pdk-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">PIC</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Produk</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">SKU</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Qty</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell">Bahan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productions as $idx => $production)
                    @php $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40'; @endphp
                    <tr class="pdk-row {{ $stripe }} border-b border-gray-50 last:border-b-0 cursor-pointer hover:bg-emerald-50/30"
                        @click="openDetail({
                            date: '{{ \Carbon\Carbon::parse($production->production_date)->format('d M Y') }}',
                            pic: '{{ addslashes($production->pic->name ?? '-') }}',
                            product: '{{ addslashes($production->product_name ?? $production->productVariants->product->name ?? '-') }}',
                            variant: '{{ addslashes($production->variant_option_summary ?? $production->productVariants->options->pluck('name')->join(', ') ?? '-') }}',
                            sku: '{{ $production->productVariants->sku->sku_code ?? '-' }}',
                            qty: {{ $production->quantity_produced }},
                            usages: [
                                @foreach($production->usages as $u)
                                { name: '{{ addslashes($u->stock->name ?? $u->stock_name ?? '-') }}', qty: '{{ $u->quantity }}', unit: '{{ $u->unit->symbol ?? '' }}' },
                                @endforeach
                            ]
                        })">
                        <td class="py-3 px-5 text-gray-500 tabular-nums whitespace-nowrap">{{ \Carbon\Carbon::parse($production->production_date)->format('d/m/Y') }}</td>
                        <td class="py-3 px-4">
                            <span class="pdk-badge bg-blue-50 text-blue-600">{{ $production->pic->name ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <p class="font-medium text-gray-800 leading-snug">{{ $production->product_name ?? $production->productVariants->product->name ?? '-' }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-none">{{ $production->variant_option_summary ?? $production->productVariants->options->pluck('name')->join(', ') ?? '-' }}</p>
                        </td>
                        <td class="py-3 px-4 hidden lg:table-cell">
                            <span class="font-mono text-[11px] text-gray-400">{{ $production->productVariants->sku->sku_code ?? '-' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="font-semibold text-gray-700 tabular-nums">{{ number_format($production->quantity_produced) }}</span>
                        </td>
                        <td class="py-3 px-4 hidden xl:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @foreach($production->usages->take(3) as $usage)
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">{{ $usage->stock->name ?? '-' }}: {{ $usage->quantity }}{{ $usage->unit->symbol ?? '' }}</span>
                                @endforeach
                                @if($production->usages->count() > 3)
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-400 rounded text-[10px]">+{{ $production->usages->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Belum ada data produksi</p>
                            <p class="text-xs text-gray-300 mt-0.5">Tambahkan produksi untuk memulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($productions as $production)
            <div class="p-4 border-l-[3px] border-l-emerald-400"
                 @click="openDetail({
                    date: '{{ \Carbon\Carbon::parse($production->production_date)->format('d M Y') }}',
                    pic: '{{ addslashes($production->pic->name ?? '-') }}',
                    product: '{{ addslashes($production->product_name ?? $production->productVariants->product->name ?? '-') }}',
                    variant: '{{ addslashes($production->variant_option_summary ?? $production->productVariants->options->pluck('name')->join(', ') ?? '-') }}',
                    sku: '{{ $production->productVariants->sku->sku_code ?? '-' }}',
                    qty: {{ $production->quantity_produced }},
                    usages: [
                        @foreach($production->usages as $u)
                        { name: '{{ addslashes($u->stock->name ?? $u->stock_name ?? '-') }}', qty: '{{ $u->quantity }}', unit: '{{ $u->unit->symbol ?? '' }}' },
                        @endforeach
                    ]
                 })">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 text-[13px] leading-snug truncate">{{ $production->product_name ?? $production->productVariants->product->name ?? '-' }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $production->variant_option_summary ?? $production->productVariants->options->pluck('name')->join(', ') ?? '-' }}</p>
                    </div>
                    <span class="pdk-badge bg-emerald-50 text-emerald-600 shrink-0">{{ number_format($production->quantity_produced) }} pcs</span>
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[12px]">
                    <div><span class="text-gray-400">Tanggal:</span> <span class="text-gray-600">{{ \Carbon\Carbon::parse($production->production_date)->format('d/m/Y') }}</span></div>
                    <div><span class="text-gray-400">PIC:</span> <span class="font-medium text-gray-700">{{ $production->pic->name ?? '-' }}</span></div>
                </div>
                @if($production->usages->count())
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($production->usages->take(2) as $usage)
                    <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px]">{{ $usage->stock->name ?? '-' }}: {{ $usage->quantity }}{{ $usage->unit->symbol ?? '' }}</span>
                    @endforeach
                    @if($production->usages->count() > 2)
                    <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-400 rounded text-[10px]">+{{ $production->usages->count() - 2 }}</span>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Belum ada data produksi.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($productions->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $productions->currentPage() }} / {{ $productions->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($productions->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $productions->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($productions->getUrlRange(max(1, $productions->currentPage()-2), min($productions->lastPage(), $productions->currentPage()+2)) as $page => $url)
                    @if($page == $productions->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($productions->hasMorePages())
                <a href="{{ $productions->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

    {{-- ── DETAIL MODAL ── --}}
    <div x-show="showDetail" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
         @click.self="showDetail = false" @keydown.escape.window="showDetail = false">
        <div x-show="showDetail"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-xl w-full max-w-[420px] mx-4 p-6 relative">
            <button @click="showDetail = false" class="absolute top-3 right-3 w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-gray-800">Detail Produksi</h3>
            </div>
            <template x-if="detailData">
                <div>
                    <div class="space-y-2.5 mb-4">
                        <div class="flex justify-between text-[12px]"><span class="text-gray-400">Tanggal</span><span class="font-medium text-gray-700" x-text="detailData.date"></span></div>
                        <div class="flex justify-between text-[12px]"><span class="text-gray-400">PIC</span><span class="font-medium text-gray-700" x-text="detailData.pic"></span></div>
                        <div class="flex justify-between text-[12px]"><span class="text-gray-400">Produk</span><span class="font-medium text-gray-700" x-text="detailData.product"></span></div>
                        <div class="flex justify-between text-[12px]"><span class="text-gray-400">Varian</span><span class="text-gray-600" x-text="detailData.variant"></span></div>
                        <div class="flex justify-between text-[12px]"><span class="text-gray-400">SKU</span><span class="font-mono text-gray-500" x-text="detailData.sku"></span></div>
                        <div class="flex justify-between text-[12px]"><span class="text-gray-400">Qty Diproduksi</span><span class="font-bold text-emerald-600" x-text="detailData.qty"></span></div>
                    </div>
                    <template x-if="detailData.usages && detailData.usages.length > 0">
                        <div>
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Bahan Digunakan</p>
                            <div class="space-y-1.5 max-h-[200px] overflow-y-auto">
                                <template x-for="(u, i) in detailData.usages" :key="i">
                                    <div class="p-2.5 bg-gray-50 rounded-lg flex items-center justify-between text-[12px]">
                                        <span class="text-gray-700 font-medium" x-text="u.name"></span>
                                        <span class="text-gray-500"><span class="font-semibold text-gray-700" x-text="u.qty"></span> <span x-text="u.unit"></span></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
