@extends('handai-manager.layouts.master')

@section('title', 'Stock Batches')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .sb-table th { position: sticky; top: 0; z-index: 5; }
    .sb-row { transition: background-color 0.15s ease; }
    .sb-row:hover .sb-actions { opacity: 1; }
    .sb-actions { opacity: 0; transition: opacity 0.15s ease; }
    @media (max-width: 767px) { .sb-actions { opacity: 1; } }
    .sb-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .sb-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .sb-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .sb-card-stat { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto" x-data="{ showFilter: {{ request()->hasAny(['search','status','date_from','date_to']) ? 'true' : 'false' }}, deleteId: null, showDeleteModal: false }">

    {{-- ── HEADER ── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Stock Batches</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">Monitoring batch & expired stock</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFilter = !showFilter"
                    class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium border rounded-lg transition"
                    :class="showFilter ? 'bg-gray-100 border-gray-300 text-gray-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-600'">
                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
                @if(request()->hasAny(['search','status','date_from','date_to']))
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </button>
            @include('handai-manager.partials.import-export-modal', ['type' => 'purchase', 'label' => 'Pembelian / Stock Batch'])
            <a href="{{ route('manager.inventory.stock-batches.create') }}"
               class="h-9 inline-flex items-center gap-1.5 px-4 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Pembelian
            </a>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="sb-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Batch</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($totalBatches) }}</p>
        </div>
        <div class="sb-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Aktif</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($activeBatches) }}</p>
        </div>
        <div class="sb-card-stat">
            <p class="text-[11px] font-medium text-amber-500 uppercase tracking-wider leading-none">Hampir Expired</p>
            <p class="text-xl font-bold text-amber-600 mt-1.5 leading-none tabular-nums">{{ number_format($nearExpiredBatches) }}</p>
        </div>
        <div class="sb-card-stat">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider leading-none">Expired</p>
            <p class="text-xl font-bold text-red-500 mt-1.5 leading-none tabular-nums">{{ number_format($expiredBatches) }}</p>
        </div>
    </div>

    {{-- ── FILTER ── --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.inventory.stock-batches.index') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama bahan, supplier, invoice..."
                               class="sb-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="sb-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="active" {{ request('status')==='active'?'selected':'' }}>Aktif</option>
                        <option value="near_expired" {{ request('status')==='near_expired'?'selected':'' }}>Hampir Expired</option>
                        <option value="expired" {{ request('status')==='expired'?'selected':'' }}>Expired</option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="sb-input" />
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="sb-input" />
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">Terapkan</button>
                    <a href="{{ route('manager.inventory.stock-batches.index') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── TABLE ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $stockBatches->firstItem() ?? 0 }}–{{ $stockBatches->lastItem() ?? 0 }}</span> dari {{ $stockBatches->total() }} batch
            </p>
            @if(request()->hasAny(['search','status','date_from','date_to']))
            <a href="{{ route('manager.inventory.stock-batches.index') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] sb-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Nama Bahan</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell">Supplier</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Qty</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Biaya</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Tgl Masuk</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Expired</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-center py-2.5 px-3 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider w-[72px]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockBatches as $idx => $batch)
                    @php
                        $isExpired = $batch->expired_status === 'expired';
                        $isNear = $batch->expired_status === 'near_expired';
                        $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40';
                        $rowBg = $isExpired ? 'bg-red-50/30' : ($isNear ? 'bg-amber-50/20' : $stripe);
                    @endphp
                    <tr class="sb-row {{ $rowBg }} border-b border-gray-50 last:border-b-0">
                        {{-- Name --}}
                        <td class="py-3 px-5">
                            <p class="font-medium text-gray-800 leading-snug">{{ $batch->stock->name ?? $batch->stock_name ?? '-' }}</p>
                            @if($batch->invoice_ref)
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-none">{{ $batch->invoice_ref }}</p>
                            @endif
                        </td>
                        {{-- Supplier --}}
                        <td class="py-3 px-4 text-gray-500 hidden xl:table-cell">{{ $batch->supplier_name ?: '—' }}</td>
                        {{-- Qty --}}
                        <td class="py-3 px-4 text-right tabular-nums">
                            <span class="font-semibold text-gray-700">{{ number_format($batch->unit_qty, $batch->unit_qty == intval($batch->unit_qty) ? 0 : 1) }}</span>
                            <span class="text-gray-400 font-normal text-[11px] ml-0.5">{{ $batch->unit->symbol ?? '' }}</span>
                        </td>
                        {{-- Cost --}}
                        <td class="py-3 px-4 text-right font-semibold text-gray-700 tabular-nums">Rp{{ number_format($batch->cost, 0, ',', '.') }}</td>
                        {{-- Buy Date --}}
                        <td class="py-3 px-4 text-gray-500 tabular-nums">{{ $batch->buy_date ? $batch->buy_date->format('d M Y') : '—' }}</td>
                        {{-- Expired --}}
                        <td class="py-3 px-4">
                            @if($batch->computed_expired_date)
                            <span class="tabular-nums {{ $isExpired ? 'text-red-500 font-medium' : ($isNear ? 'text-amber-600 font-medium' : 'text-gray-500') }}">
                                {{ $batch->computed_expired_date->format('d M Y') }}
                            </span>
                            @if($batch->days_left !== null)
                            <p class="text-[10px] mt-0.5 leading-none {{ $isExpired ? 'text-red-400' : ($isNear ? 'text-amber-400' : 'text-gray-300') }}">
                                @if($batch->days_left < 0) {{ abs($batch->days_left) }} hari lalu
                                @elseif($batch->days_left === 0) Hari ini!
                                @else {{ $batch->days_left }} hari lagi
                                @endif
                            </p>
                            @endif
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="py-3 px-4 text-center">
                            @if($isExpired)
                            <span class="sb-badge bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Expired</span>
                            @elseif($isNear)
                            <span class="sb-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Near Exp.</span>
                            @else
                            <span class="sb-badge bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Aktif</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="py-3 px-3">
                            <div class="sb-actions flex items-center justify-center gap-0.5">
                                @if($batch->nota_url && $batch->nota_url !== 'belum ada gambar')
                                <a href="{{ asset('storage/assets/nota/' . $batch->nota_url) }}" target="_blank"
                                   class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition" title="Lihat Nota">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </a>
                                @endif
                                <button @click="deleteId = {{ $batch->id }}; showDeleteModal = true"
                                        class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition" title="Hapus">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Belum ada data batch</p>
                            <p class="text-xs text-gray-300 mt-0.5">Data akan muncul setelah pembelian stok.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($stockBatches as $batch)
            @php
                $isExpired = $batch->expired_status === 'expired';
                $isNear = $batch->expired_status === 'near_expired';
                $accent = $isExpired ? 'border-l-red-400' : ($isNear ? 'border-l-amber-400' : 'border-l-emerald-400');
            @endphp
            <div class="p-4 border-l-[3px] {{ $accent }} {{ $isExpired ? 'bg-red-50/20' : '' }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 text-[13px] leading-snug truncate">{{ $batch->stock->name ?? $batch->stock_name ?? '-' }}</p>
                        @if($batch->supplier_name)
                        <p class="text-[11px] text-gray-400 truncate">{{ $batch->supplier_name }}</p>
                        @endif
                    </div>
                    @if($isExpired)
                    <span class="sb-badge bg-red-50 text-red-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Expired</span>
                    @elseif($isNear)
                    <span class="sb-badge bg-amber-50 text-amber-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Near Exp.</span>
                    @else
                    <span class="sb-badge bg-emerald-50 text-emerald-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Aktif</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12px]">
                    <div><span class="text-gray-400">Qty:</span> <span class="font-semibold text-gray-700">{{ number_format($batch->unit_qty, $batch->unit_qty == intval($batch->unit_qty) ? 0 : 1) }} {{ $batch->unit->symbol ?? '' }}</span></div>
                    <div><span class="text-gray-400">Biaya:</span> <span class="font-semibold text-gray-700">Rp{{ number_format($batch->cost, 0, ',', '.') }}</span></div>
                    <div><span class="text-gray-400">Masuk:</span> <span class="text-gray-600">{{ $batch->buy_date ? $batch->buy_date->format('d M Y') : '—' }}</span></div>
                    <div>
                        <span class="text-gray-400">Expired:</span>
                        @if($batch->computed_expired_date)
                        <span class="{{ $isExpired ? 'text-red-500 font-medium' : ($isNear ? 'text-amber-600 font-medium' : 'text-gray-600') }}">{{ $batch->computed_expired_date->format('d M Y') }}</span>
                        @else <span class="text-gray-300">—</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-3 pt-2.5 border-t border-gray-100">
                    @if($batch->nota_url && $batch->nota_url !== 'belum ada gambar')
                    <a href="{{ asset('storage/assets/nota/' . $batch->nota_url) }}" target="_blank" class="text-[11px] text-blue-500 hover:text-blue-600 font-medium inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Nota
                    </a>
                    @endif
                    <button @click="deleteId = {{ $batch->id }}; showDeleteModal = true" class="text-[11px] text-red-400 hover:text-red-600 font-medium inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </div>
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Belum ada data batch.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($stockBatches->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $stockBatches->currentPage() }} / {{ $stockBatches->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($stockBatches->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $stockBatches->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($stockBatches->getUrlRange(max(1, $stockBatches->currentPage()-2), min($stockBatches->lastPage(), $stockBatches->currentPage()+2)) as $page => $url)
                    @if($page == $stockBatches->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($stockBatches->hasMorePages())
                <a href="{{ $stockBatches->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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
            <h3 class="text-[15px] font-bold text-gray-800 mb-1">Hapus Batch?</h3>
            <p class="text-[13px] text-gray-400 mb-5 leading-relaxed">Data batch ini akan dihapus permanen<br>dan tidak bisa dikembalikan.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 h-10 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-500 hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <form :action="'{{ route('manager.inventory.stock-batches.index') }}/' + deleteId" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full h-10 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[13px] font-semibold transition cursor-pointer">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection