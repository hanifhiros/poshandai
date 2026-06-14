@extends('layouts.master')

@section('title', 'Daftar Order')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .ord-table th { position: sticky; top: 0; z-index: 5; }
    .ord-row { transition: background-color 0.15s ease; }
    .ord-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .ord-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .ord-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .ord-card-stat { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
    .ord-card { transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .ord-card:active { transform: scale(0.98); }
    @media (hover: hover) { .ord-card:hover { box-shadow: 0 4px 20px -4px rgba(0,0,0,0.08); } }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto"
     x-data="{
        showFilter: {{ request()->hasAny(['search','status']) && request('status') !== '' ? 'true' : 'false' }}
     }">

    {{-- â”€â”€ FLASH â”€â”€ --}}
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

    {{-- â”€â”€ HEADER â”€â”€ --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Daftar Order Customer</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">{{ $selected_store ? $selected_store->store_name : 'Semua Toko' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFilter = !showFilter"
                    class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium border rounded-lg transition cursor-pointer"
                    :class="showFilter ? 'bg-gray-100 border-gray-300 text-gray-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-600'">
                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
                @if(request()->filled('status') || request()->filled('search'))
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </button>
        </div>
    </div>

    {{-- â”€â”€ STAT CARDS â”€â”€ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="ord-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Order</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($orderStats->total ?? 0) }}</p>
        </div>
        <div class="ord-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Terkirim</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($orderStats->terkirim ?? 0) }}</p>
        </div>
        <div class="ord-card-stat">
            <p class="text-[11px] font-medium text-amber-500 uppercase tracking-wider leading-none">Belum Terkirim</p>
            <p class="text-xl font-bold text-amber-600 mt-1.5 leading-none tabular-nums">{{ number_format($orderStats->belum_terkirim ?? 0) }}</p>
        </div>
        <div class="ord-card-stat">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider leading-none">Dibatalkan</p>
            <p class="text-xl font-bold text-red-500 mt-1.5 leading-none tabular-nums">{{ number_format($orderStats->dibatalkan ?? 0) }}</p>
        </div>
    </div>

    {{-- â”€â”€ FILTER â”€â”€ --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.operational.orders.index') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="ID order / nama customer..."
                               class="ord-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Status Kirim</label>
                    <select name="status" class="ord-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="terkirim" {{ $selectedStatus === 'terkirim' ? 'selected' : '' }}>Terkirim</option>
                        <option value="belum terkirim" {{ $selectedStatus === 'belum terkirim' ? 'selected' : '' }}>Belum Terkirim</option>
                        <option value="dibatalkan" {{ $selectedStatus === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Terapkan</button>
                    <a href="{{ route('manager.operational.orders.index') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- â”€â”€ MAIN TABLE â”€â”€ --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $orders->firstItem() ?? 0 }}â€“{{ $orders->lastItem() ?? 0 }}</span> dari {{ $orders->total() }} order
            </p>
            @if(request()->filled('status') || request()->filled('search'))
            <a href="{{ route('manager.operational.orders.index') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] ord-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Bayar</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Tanggal</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status Bayar</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status Kirim</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Item</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $idx => $order)
                    @php
                        $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40';
                    @endphp
                    <tr class="ord-row {{ $stripe }} border-b border-gray-50 last:border-b-0 hover:bg-emerald-50/20">
                        <td class="py-3 px-5 font-mono text-[11px] text-gray-400">#{{ $order->id }}</td>
                        <td class="py-3 px-4">
                            <p class="font-medium text-gray-800 leading-snug">{{ $order->customer->name ?? '-' }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-none">ID: {{ $order->customer_id }}</p>
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-gray-700 tabular-nums">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-gray-500 hidden lg:table-cell">{{ ucfirst($order->payment_type ?? '-') }}</td>
                        <td class="py-3 px-4 text-gray-500 text-[12px] tabular-nums hidden lg:table-cell">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($order->isRA == 0)
                            <span class="ord-badge bg-blue-50 text-blue-600"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Lunas</span>
                            @else
                            <span class="ord-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Belum</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $sc = match($order->order_status) {
                                    'terkirim' => ['bg-emerald-50 text-emerald-600', 'bg-emerald-400', 'Terkirim'],
                                    'dibatalkan' => ['bg-red-50 text-red-600', 'bg-red-400', 'Batal'],
                                    default => ['bg-amber-50 text-amber-600', 'bg-amber-400', 'Menunggu'],
                                };
                            @endphp
                            <span class="ord-badge {{ $sc[0] }}"><span class="w-1.5 h-1.5 rounded-full {{ $sc[1] }}"></span>{{ $sc[2] }}</span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <details class="relative inline-block">
                                <summary class="text-[12px] text-gray-500 hover:text-gray-700 cursor-pointer font-medium inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    {{ count($order->invoices) }}
                                </summary>
                                <div class="absolute right-0 mt-1 z-20 bg-white border border-gray-200 rounded-xl shadow-lg p-3 w-64 text-xs space-y-1.5">
                                    @foreach($order->invoices as $item)
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-medium text-gray-700">{{ $item->product->name ?? $item->product_name ?? '-' }}</span>
                                            <span class="text-gray-400"> x{{ $item->quantity_bought }}</span>
                                            @if($item->variant && optional($item->variant->options)->count())
                                                <p class="text-gray-400 text-[10px]">
                                                    @foreach ($item->variant->options as $opt)
                                                        {{ $opt->attribute->name }}: {{ $opt->name }}@if(!$loop->last), @endif
                                                    @endforeach
                                                </p>
                                            @elseif($item->variant_name)
                                                <p class="text-gray-400 text-[10px]">{{ $item->variant_name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </details>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Tidak ada order ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($orders as $order)
            @php
                $accent = match($order->order_status) {
                    'terkirim' => 'border-l-emerald-400',
                    'dibatalkan' => 'border-l-red-400',
                    default => 'border-l-amber-400',
                };
                $sc = match($order->order_status) {
                    'terkirim' => ['bg-emerald-50 text-emerald-600', 'bg-emerald-400', 'Terkirim'],
                    'dibatalkan' => ['bg-red-50 text-red-600', 'bg-red-400', 'Batal'],
                    default => ['bg-amber-50 text-amber-600', 'bg-amber-400', 'Menunggu'],
                };
            @endphp
            <div class="p-4 border-l-[3px] {{ $accent }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 text-[13px] leading-snug">{{ $order->customer->name ?? '-' }}</p>
                        <p class="text-[11px] text-gray-400">#{{ $order->id }} &middot; {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</p>
                    </div>
                    <span class="ord-badge {{ $sc[0] }} shrink-0"><span class="w-1.5 h-1.5 rounded-full {{ $sc[1] }}"></span>{{ $sc[2] }}</span>
                </div>
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-[15px] font-bold text-gray-800 tabular-nums">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</p>
                    @if($order->isRA == 0)
                    <span class="ord-badge bg-blue-50 text-blue-600"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Lunas</span>
                    @else
                    <span class="ord-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Belum</span>
                    @endif
                </div>
                @if(count($order->invoices) > 0)
                <details class="mt-2 pt-2 border-t border-gray-100">
                    <summary class="text-[11px] text-gray-400 cursor-pointer hover:text-gray-600 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        {{ count($order->invoices) }} item
                    </summary>
                    <div class="mt-2 space-y-1 pl-2 border-l-2 border-gray-100">
                        @foreach($order->invoices as $item)
                        <div class="text-[11px]">
                            <span class="font-medium text-gray-700">{{ $item->product->name ?? $item->product_name ?? '-' }}</span>
                            <span class="text-gray-400"> x{{ $item->quantity_bought }}</span>
                            @if($item->variant_name)
                            <span class="text-gray-300 text-[10px]">({{ $item->variant_name }})</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </details>
                @endif
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Tidak ada order ditemukan.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($orders->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $orders->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($orders->getUrlRange(max(1, $orders->currentPage()-2), min($orders->lastPage(), $orders->currentPage()+2)) as $page => $url)
                    @if($page == $orders->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

    {{-- â”€â”€ PENDING ORDERS SECTION â”€â”€ --}}
    @if(count($pendingOrders) > 0)
    <div class="mt-8">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-amber-500 rounded-full"></div>
            <div>
                <h2 class="text-[15px] font-bold text-gray-800">Order Belum Terkirim</h2>
                <p class="text-[11px] text-gray-400">{{ count($pendingOrders) }} pesanan menunggu</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($pendingOrders as $order)
            <div class="ord-card bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                {{-- Card Header --}}
                <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <p class="font-bold text-gray-800 text-[13px]">#{{ $order->id }} â€” {{ $order->customer->name ?? '-' }}</p>
                        @if($order->delivery_date || $order->delivery_time)
                        <p class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') : '' }}
                            {{ $order->delivery_time ? \Carbon\Carbon::parse($order->delivery_time)->format('H:i') : '' }}
                        </p>
                        @endif
                    </div>
                    <p class="text-[14px] font-bold text-gray-800 tabular-nums">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</p>
                </div>

                {{-- Items --}}
                <div class="px-4 py-3 space-y-1.5 max-h-36 overflow-y-auto text-[12px]">
                    @foreach($order->invoices as $item)
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-medium text-gray-700">{{ $item->product->name ?? $item->product_name ?? '-' }}</span>
                            <span class="text-gray-400"> x{{ $item->quantity_bought }}</span>
                            @if($item->variant && optional($item->variant->options)->count())
                                <p class="text-gray-400 text-[10px]">
                                    @foreach ($item->variant->options as $opt)
                                        {{ $opt->attribute->name }}: {{ $opt->name }}@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            @elseif($item->variant_name)
                                <p class="text-gray-400 text-[10px]">{{ $item->variant_name }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="px-4 py-3 border-t border-gray-50 flex items-center gap-2 justify-end">
                    <form action="{{ route('manager.operational.orders.markAsShipped', $order->id) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin menandai sebagai selesai?')">
                        @csrf
                        <button type="submit" class="h-8 inline-flex items-center gap-1.5 px-3 rounded-lg bg-emerald-50 text-emerald-700 text-[12px] font-semibold hover:bg-emerald-100 transition cursor-pointer border border-emerald-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Selesai
                        </button>
                    </form>
                    <form action="{{ route('manager.operational.orders.cancel', $order->id) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        @csrf
                        <button type="submit" class="h-8 inline-flex items-center gap-1.5 px-3 rounded-lg bg-red-50 text-red-600 text-[12px] font-semibold hover:bg-red-100 transition cursor-pointer border border-red-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batalkan
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if($pendingOrders->hasPages())
        <div class="mt-4 flex justify-end">
            <div class="flex items-center gap-1">
                @if($pendingOrders->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $pendingOrders->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @if($pendingOrders->hasMorePages())
                <a href="{{ $pendingOrders->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

</div>
@endsection

@section('page-script')
<script>
    // Preserve scroll position on reload
    window.addEventListener('beforeunload', () => {
        sessionStorage.setItem('scrollPosition', window.scrollY);
    });
    window.addEventListener('load', () => {
        const scrollY = sessionStorage.getItem('scrollPosition');
        if (scrollY !== null) {
            window.scrollTo(0, parseInt(scrollY));
            sessionStorage.removeItem('scrollPosition');
        }
    });
</script>
@endsection
