@extends('layouts.master')
@section('title', 'Inventory Control')

@section('page-style')
@vite('resources/css/handai-manager-inventory-stock.css')
@endsection

@section('content')

@php
    $currentSort = request('sort','name');
    $currentDir  = request('dir','asc');
    $sortUrl = function($field) {
        $dir = (request('sort','name')===$field && request('dir','asc')==='asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort'=>$field,'dir'=>$dir,'page'=>null]);
    };
    $sortIcon = function($field) {
        if(request('sort','name')!==$field) return '<span class="text-gray-300 ml-0.5">â‡…</span>';
        return request('dir','asc')==='asc' ? '<span class="text-blue-500 ml-0.5">â†‘</span>' : '<span class="text-blue-500 ml-0.5">â†“</span>';
    };
    $typeFilter = $type ?? 'all';
    $fmtRp = fn($v) => 'Rp' . number_format((float)($v ?? 0), 0, ',', '.');
@endphp

<div class="py-6 px-4 md:px-6 lg:px-8 max-w-[1600px] mx-auto" style="background:var(--inv-bg);min-height:100vh;font-family:'Poppins','Public Sans',sans-serif"
     x-data="{
        deleteId:null, deleteName:'', deleteType:'', showDeleteModal:false,
        showExpiredModal:false, expiredBatches:[],
        expiredData: @js($expiredBatchesMap),
        openExpired(id){this.expiredBatches=this.expiredData[id]||[];this.showExpiredModal=true},
        showFinance: false,
        showActions: true
     }">

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• FLASH MESSAGES â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if(session('success'))
    <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] flex items-center gap-2.5" style="border-radius:var(--inv-radius)" x-data x-init="setTimeout(()=>$el.remove(),5000)">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-[13px] flex items-center gap-2.5" style="border-radius:var(--inv-radius)">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• A. HEADER â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 fade-in-up">
        <div>
            <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2.5" style="color:var(--inv-text);letter-spacing:-0.02em">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:var(--inv-accent-light)">
                    <svg class="w-5 h-5" style="color:var(--inv-accent)" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                Inventory Control
            </h1>
            <p class="text-sm mt-1" style="color:var(--inv-muted)">{{ $selected_store->name ?? 'Store' }} &middot; <span class="tabular-nums">{{ now()->translatedFormat('l, d F Y') }}</span></p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @include('handai-manager.partials.import-export-modal', ['type' => 'stock', 'label' => 'Bahan Baku'])
            <a href="{{ route('manager.inventory.stock.create') }}" class="inv-btn inv-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Pembelian
            </a>
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• B. KPI CARDS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6 fade-in-up fade-delay-1">
        {{-- B1. Bahan Baku --}}
        <div class="inv-card-hover p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:var(--inv-info)">Bahan Baku</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold tabular-nums" style="color:var(--inv-text);letter-spacing:-0.02em">{{ number_format($stats->total_bahan) }} <span class="text-sm font-normal" style="color:var(--inv-muted)">item</span></p>
            <div class="flex items-center gap-3 mt-2">
                <span class="text-xs" style="color:var(--inv-muted)"><span class="font-semibold text-emerald-600">{{ $stats->raw_ready }}</span> Ready</span>
                <span class="text-xs" style="color:var(--inv-muted)"><span class="font-semibold text-amber-600">{{ $stats->raw_low }}</span> Low</span>
                <span class="text-xs" style="color:var(--inv-muted)"><span class="font-semibold text-red-500">{{ $stats->raw_out }}</span> Habis</span>
            </div>
            <p class="text-sm font-bold text-blue-600 mt-2 tabular-nums">{{ $fmtRp($stats->raw_value) }}</p>
        </div>
        {{-- B2. Produk Jadi --}}
        <div class="inv-card-hover p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-violet-500">Produk Jadi</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-violet-50">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold tabular-nums" style="color:var(--inv-text);letter-spacing:-0.02em">{{ number_format($stats->total_produk) }} <span class="text-sm font-normal" style="color:var(--inv-muted)">item</span></p>
            <div class="flex items-center gap-3 mt-2">
                <span class="text-xs" style="color:var(--inv-muted)"><span class="font-semibold text-emerald-600">{{ $stats->fg_ready }}</span> Ready</span>
                <span class="text-xs" style="color:var(--inv-muted)"><span class="font-semibold text-amber-600">{{ $stats->fg_low }}</span> Low</span>
                <span class="text-xs" style="color:var(--inv-muted)"><span class="font-semibold text-red-500">{{ $stats->fg_out }}</span> Habis</span>
            </div>
            <div class="flex items-center gap-4 mt-2">
                <div>
                    <span class="text-[11px]" style="color:var(--inv-muted)">
                        HPP <span class="text-[9px]" title="Jumlah HPP semua varian produk jadi dalam inventori (jika HPP tidak diisi ditarik dari harga jual).">(total)</span>
                    </span>
                    <span class="text-sm font-bold text-violet-600 tabular-nums">{{ $fmtRp($stats->fg_value) }}</span>
                </div>
                <div><span class="text-[11px]" style="color:var(--inv-muted)">Jual</span> <span class="text-sm font-bold text-emerald-600 tabular-nums">{{ $fmtRp($stats->fg_selling_value ?? 0) }}</span></div>
            </div>
        </div>
        {{-- B3. Total Gudang --}}
        <div class="inv-card-hover p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider" style="color:var(--inv-accent)">Total Estimasi Gudang</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:var(--inv-accent-light)">
                    <svg class="w-5 h-5" style="color:var(--inv-accent)" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold tabular-nums" style="color:var(--inv-text);letter-spacing:-0.02em">{{ number_format($stats->total_items) }} <span class="text-sm font-normal" style="color:var(--inv-muted)">item</span></p>
            <p class="text-xl font-bold tabular-nums mt-1" style="color:var(--inv-accent)">{{ $fmtRp($stats->total_value) }}</p>
            <p class="text-xs mt-1" style="color:var(--inv-muted)">Bahan {{ $fmtRp($stats->raw_value) }} + Produk Jadi {{ $fmtRp($stats->fg_value) }}</p>
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• C. STATUS OVERVIEW â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    {{-- two-row status grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 fade-in-up fade-delay-2">
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-emerald-500 uppercase tracking-wider">Ready</p>
            <p class="text-2xl font-bold text-emerald-600 mt-2 tabular-nums">{{ number_format($stats->ready) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-amber-500 uppercase tracking-wider">Hampir Habis</p>
            <p class="text-2xl font-bold text-amber-600 mt-2 tabular-nums">{{ number_format($stats->low_stock) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-red-400 uppercase tracking-wider">Habis</p>
            <p class="text-2xl font-bold text-red-500 mt-2 tabular-nums">{{ number_format($stats->out_of_stock) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-blue-500 uppercase tracking-wider">Perlu Reorder</p>
            <p class="text-2xl font-bold text-blue-600 mt-2 tabular-nums">{{ number_format($stats->reorder) }}</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 fade-in-up fade-delay-3">
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Slow Movers</p>
            <p class="text-2xl font-bold text-gray-500 mt-2 tabular-nums">{{ number_format($stats->slow_movers) }}</p>
            <p class="text-[10px]" style="color:var(--inv-muted)">turnover &lt;0.3x</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-orange-500 uppercase tracking-wider">Hampir Basi</p>
            <p class="text-2xl font-bold text-orange-500 mt-2 tabular-nums">{{ number_format($stats->almost_expired) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-semibold text-red-500 uppercase tracking-wider">Basi</p>
            <p class="text-2xl font-bold text-red-500 mt-2 tabular-nums">{{ number_format($stats->expired ?? 0) }}</p>
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• D. ACTION ITEMS (Operational Insights) â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($actionItems->count() > 0)
    <div class="inv-card mb-6 overflow-hidden fade-in-up fade-delay-3" x-data="{open:true}">
        <button @click="open=!open" class="w-full flex items-center justify-between px-5 py-3.5 border-b border-[var(--inv-border)] cursor-pointer hover:bg-gray-50/50 transition">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <span class="text-[14px] font-bold text-[var(--inv-text)]">Perlu Tindakan</span>
                <span class="inv-badge bg-amber-100 text-amber-700">{{ $actionItems->count() }}</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open&&'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-collapse>
            <div class="divide-y divide-gray-50">
                @foreach($actionItems as $ai)
                <div class="action-item">
                    @if($ai->priority === 'critical')
                    <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    @elseif($ai->priority === 'high')
                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    @elseif($ai->priority === 'warning')
                    <div class="w-7 h-7 rounded-lg bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    @else
                    <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] text-gray-700 leading-relaxed">{{ $ai->message }}</p>
                    </div>
                    @if($ai->action_url && $ai->action_label)
                    <a href="{{ $ai->action_url }}" class="inv-btn inv-btn-sm inv-btn-outline shrink-0">{{ $ai->action_label }}</a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• D2. SMART RECOMMENDATIONS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($recommendations->count() > 0)
    <div class="inv-card mb-6 overflow-hidden fade-in-up fade-delay-4" x-data="{openRec:false}">
        <button @click="openRec=!openRec" class="w-full flex items-center justify-between px-5 py-3.5 border-b border-[var(--inv-border)] cursor-pointer hover:bg-gray-50/50 transition">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                </div>
                <span class="text-[14px] font-bold text-[var(--inv-text)]">Smart Rekomendasi</span>
                <span class="inv-badge bg-blue-100 text-blue-700">{{ $recommendations->count() }}</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openRec&&'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="openRec" x-collapse x-cloak>
            <div class="divide-y divide-gray-50">
                @foreach($recommendations as $rec)
                <div class="action-item">
                    @if($rec->type === 'critical')
                    <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    @elseif($rec->type === 'warning')
                    <div class="w-7 h-7 rounded-lg bg-orange-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    @else
                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-[var(--inv-text)] leading-snug">{{ $rec->title }}</p>
                        <p class="text-[11px] text-[var(--inv-muted)] mt-0.5 leading-relaxed">{{ $rec->message }}</p>
                    </div>
                    @if($rec->action_url && $rec->action_label)
                    <a href="{{ $rec->action_url }}" class="inv-btn inv-btn-sm inv-btn-primary shrink-0">{{ $rec->action_label }}</a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• E. FINANCE INTEGRATION â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($financeData->available ?? false)
    <div class="inv-card mb-6 overflow-hidden fade-in-up fade-delay-5">
        <button @click="showFinance=!showFinance" class="w-full flex items-center justify-between px-5 py-3.5 border-b border-[var(--inv-border)] cursor-pointer hover:bg-gray-50/50 transition">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[14px] font-bold text-[var(--inv-text)]">Integrasi Finance (Neraca)</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="showFinance&&'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="showFinance" x-collapse x-cloak>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-2">Bahan Baku (1-2001)</p>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between"><span class="text-[var(--inv-muted)]">Fisik Gudang</span><span class="font-semibold tabular-nums">{{ $fmtRp($stats->raw_value) }}</span></div>
                            <div class="flex justify-between"><span class="text-[var(--inv-muted)]">Jurnal (Neraca)</span><span class="font-semibold tabular-nums">{{ $fmtRp($financeData->inv_raw_journal) }}</span></div>
                            <div class="flex justify-between pt-1.5 border-t border-blue-200">
                                <span class="font-medium {{ $financeData->raw_variance == 0 ? 'text-emerald-600' : 'text-red-500' }}">Selisih</span>
                                <span class="font-bold tabular-nums {{ $financeData->raw_variance == 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $fmtRp($financeData->raw_variance) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-violet-50/50 rounded-xl p-4 border border-violet-100">
                        <p class="text-[10px] font-bold text-violet-500 uppercase tracking-wider mb-2">Produk Jadi (1-2002)</p>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between"><span class="text-[var(--inv-muted)]">Fisik Gudang (HPP)</span><span class="font-semibold tabular-nums">{{ $fmtRp($stats->fg_value) }}</span></div>
                            <div class="flex justify-between"><span class="text-[var(--inv-muted)]">Jurnal (Neraca)</span><span class="font-semibold tabular-nums">{{ $fmtRp($financeData->inv_fg_journal) }}</span></div>
                            <div class="flex justify-between pt-1.5 border-t border-violet-200">
                                <span class="font-medium {{ $financeData->fg_variance == 0 ? 'text-emerald-600' : 'text-red-500' }}">Selisih</span>
                                <span class="font-bold tabular-nums {{ $financeData->fg_variance == 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $fmtRp($financeData->fg_variance) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-emerald-50/50 rounded-xl p-4 border border-emerald-100">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-2">Total Inventori</p>
                        <div class="space-y-1.5 text-[12px]">
                            <div class="flex justify-between"><span class="text-[var(--inv-muted)]">Fisik Gudang</span><span class="font-semibold tabular-nums">{{ $fmtRp($stats->total_value) }}</span></div>
                            <div class="flex justify-between"><span class="text-[var(--inv-muted)]">Jurnal (Neraca)</span><span class="font-semibold tabular-nums">{{ $fmtRp($financeData->inv_total_journal) }}</span></div>
                            <div class="flex justify-between pt-1.5 border-t border-emerald-200">
                                <span class="font-medium {{ $financeData->total_variance == 0 ? 'text-emerald-600' : 'text-red-500' }}">Selisih</span>
                                <span class="font-bold tabular-nums {{ $financeData->total_variance == 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $fmtRp($financeData->total_variance) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @if($financeData->total_variance != 0)
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-[12px] text-amber-700 flex items-start gap-2">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <span>Terdapat selisih antara nilai fisik gudang dan catatan jurnal sebesar <strong>{{ $fmtRp($financeData->total_variance) }}</strong>. Lakukan stock opname untuk mencocokkan data.</span>
                </div>
                @else
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-[12px] text-emerald-700 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Nilai gudang dan jurnal neraca sudah cocok.</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• F. FILTER BAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <form method="GET" action="{{ route('manager.inventory.stock') }}" id="filterForm" class="inv-card p-5 mb-5 fade-in-up">
        <div class="flex flex-wrap items-end gap-3">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama item, kategori..." class="inv-input !pr-9" />
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            {{-- Type chips --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold text-[var(--inv-muted)] uppercase tracking-wider mr-1">Tipe</span>
                <a href="{{ route('manager.inventory.stock', array_merge(request()->except(['type','page']), ['type'=>'all'])) }}" class="filter-chip {{ $typeFilter==='all' ? 'active' : '' }}">Semua</a>
                <a href="{{ route('manager.inventory.stock', array_merge(request()->except(['type','page']), ['type'=>'bahan'])) }}" class="filter-chip {{ $typeFilter==='bahan' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>Bahan
                </a>
                <a href="{{ route('manager.inventory.stock', array_merge(request()->except(['type','page']), ['type'=>'produk'])) }}" class="filter-chip {{ $typeFilter==='produk' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>Produk
                </a>
                <a href="{{ route('manager.inventory.stock', array_merge(request()->except(['type','page']), ['type'=>'setengah'])) }}" class="filter-chip {{ $typeFilter==='setengah' ? 'active' : '' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>Setengah Jadi
                </a>
            </div>

            {{-- Category --}}
            <div class="min-w-[140px]">
                <select name="category" class="inv-input appearance-none cursor-pointer" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Kategori</option>
                    @if($stockCategories->count())
                    <optgroup label="Bahan Baku">
                        @foreach ($stockCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                        @endforeach
                    </optgroup>
                    @endif
                    @if($productCategories->count())
                    <optgroup label="Produk Jadi">
                        @foreach ($productCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category')==$cat->id?'selected':'' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
            </div>

            {{-- Status --}}
            <div class="min-w-[140px]">
                <select name="status" class="inv-input appearance-none cursor-pointer" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Semua Status</option>
                    <option value="ready" {{ request('status')==='ready'?'selected':'' }}>âœ… Ready</option>
                    <option value="low_stock" {{ request('status')==='low_stock'?'selected':'' }}>âš ï¸ Hampir Habis</option>
                    <option value="out_of_stock" {{ request('status')==='out_of_stock'?'selected':'' }}>ðŸš« Habis</option>
                    <option value="reorder" {{ request('status')==='reorder'?'selected':'' }}>ðŸ”„ Perlu Reorder</option>
                    <option value="almost_expired" {{ request('status')==='almost_expired'?'selected':'' }}>â° Hampir Expired</option>
                    <option value="expired" {{ request('status')==='expired'?'selected':'' }}>â˜ ï¸ Expired</option>
                </select>
            </div>

            {{-- Hidden carry-overs --}}
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}"/>@endif
            @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}"/>@endif
            @if(request('type') && request('type') !== 'all')<input type="hidden" name="type" value="{{ request('type') }}"/>@endif

            <button type="submit" class="inv-btn inv-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Cari
            </button>
            @if(request()->hasAny(['search','category','status','type']))
            <a href="{{ route('manager.inventory.stock') }}" class="inv-btn inv-btn-outline text-[var(--inv-muted)]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Reset
            </a>
            @endif
        </div>

        {{-- Active filter pills --}}
        @if(request()->hasAny(['search','category','status','type']))
        <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100">
            <span class="text-[10px] font-bold text-[var(--inv-muted)] uppercase tracking-wider">Filter aktif:</span>
            @if(request('search'))
            <span class="inv-badge bg-blue-50 text-blue-600">
                "{{ request('search') }}"
                <a href="{{ route('manager.inventory.stock', request()->except(['search','page'])) }}" class="ml-1 hover:text-blue-800">&times;</a>
            </span>
            @endif
            @if(request('type') && request('type') !== 'all')
            <span class="inv-badge {{ request('type')==='bahan' ? 'bg-blue-50 text-blue-600' : (request('type')==='setengah' ? 'bg-purple-50 text-purple-600' : 'bg-violet-50 text-violet-600') }}">
                {{ request('type')==='bahan' ? 'Bahan Baku' : (request('type')==='setengah' ? 'Produk Setengah Jadi' : 'Produk Jadi') }}
                <a href="{{ route('manager.inventory.stock', request()->except(['type','page'])) }}" class="ml-1 hover:opacity-70">&times;</a>
            </span>
            @endif
            @if(request('category'))
            <span class="inv-badge bg-gray-100 text-gray-600">
                Kategori
                <a href="{{ route('manager.inventory.stock', request()->except(['category','page'])) }}" class="ml-1 hover:text-gray-800">&times;</a>
            </span>
            @endif
            @if(request('status'))
            <span class="inv-badge bg-amber-50 text-amber-600">
                {{ ['ready'=>'Ready','low_stock'=>'Low Stock','out_of_stock'=>'Habis','reorder'=>'Reorder','almost_expired'=>'Hampir Expired','expired'=>'Expired'][request('status')] ?? request('status') }}
                <a href="{{ route('manager.inventory.stock', request()->except(['status','page'])) }}" class="ml-1 hover:text-amber-800">&times;</a>
            </span>
            @endif
        </div>
        @endif
    </form>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• G. DATA TABLE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="inv-card overflow-hidden fade-in-up">
        {{-- Toolbar --}}
        <div class="px-5 py-3 border-b border-[var(--inv-border)] flex items-center justify-between gap-4">
            <p class="text-[12px] text-[var(--inv-muted)]">
                <span class="font-semibold text-[var(--inv-text)]">{{ $inventoryItems->firstItem() ?? 0 }}â€“{{ $inventoryItems->lastItem() ?? 0 }}</span>
                dari {{ number_format($inventoryItems->total()) }} item
                @if(($stats->inactive ?? 0) > 0)
                <span class="mx-1 text-gray-300">&middot;</span>{{ $stats->inactive }} nonaktif
                @endif
            </p>
        </div>

        {{-- â”€â”€ Desktop Table â”€â”€ --}}
        <div class="hidden md:block w-full">
            <table class="inv-table table-fixed">
                <thead>
                    <tr>
                        <th class="text-left pl-5 w-1/3">
                            <a href="{{ $sortUrl('name') }}" class="sort-link text-[12px]">Produk & Kategori {!! $sortIcon('name') !!}</a>
                        </th>
                        <th class="text-right w-1/5">
                            <div class="flex flex-col items-end gap-1">
                                <a href="{{ $sortUrl('hpp') }}" class="sort-link text-[11px]">HPP {!! $sortIcon('hpp') !!}</a>
                                <span class="text-[10px] text-gray-400">Harga Jual &middot; <a href="{{ $sortUrl('value') }}" class="sort-link hover:text-[var(--inv-accent)]">Tot Nilai {!! $sortIcon('value') !!}</a></span>
                            </div>
                        </th>
                        <th class="text-right w-1/4">
                            <div class="flex flex-col items-end gap-1">
                                <a href="{{ $sortUrl('quantity') }}" class="sort-link text-[11px]">Stok Tersedia {!! $sortIcon('quantity') !!}</a>
                                <span class="text-[10px] text-gray-400">Min Stok &middot; Pakai/30hr</span>
                            </div>
                        </th>
                        <th class="text-center w-1/6">Status & Kondisi</th>
                        <th class="w-[80px]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryItems as $item)
                    @php
                        $isOut = in_array($item->status, ['Out of Stock','Habis']);
                        $isLow = $item->status === 'Low Stock';
                        $isExpired = ($item->freshness ?? '') === 'Expired' || ($item->expired_qty ?? 0) > 0;
                        $rowClass = $isOut ? 'row-danger' : ($isExpired ? 'row-danger' : ($isLow ? 'row-warn' : ''));
                    @endphp
                    <tr class="{{ $rowClass }} cursor-pointer" @click="$dispatch('open-detail', {type:'{{ $item->model_type }}', id:{{ $item->id }}})">
                        {{-- Produk & Kategori --}}
                        <td class="pl-5">
                            <div class="flex items-start gap-3 min-w-0 py-1">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 text-[11px] font-bold mt-0.5 {{ $item->model_type==='stock' ? 'bg-blue-50 text-blue-500' : ($item->model_type==='semi_finished' ? 'bg-purple-50 text-purple-500' : 'bg-violet-50 text-violet-500') }}">
                                    {{ $item->model_type==='stock' ? 'BB' : ($item->model_type==='semi_finished' ? 'SJ' : 'PJ') }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-[var(--inv-text)] leading-snug truncate text-[14px]">{{ $item->name }}</p>
                                    @if($item->subtitle && $item->subtitle !== 'Tanpa Varian')
                                    <p class="text-[11px] text-[var(--inv-muted)] mt-0.5 truncate leading-none">{{ $item->subtitle }}</p>
                                    @endif
                                    <p class="text-[11px] text-gray-500 mt-1.5 flex items-center gap-1.5">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full {{ $item->model_type==='stock' ? 'bg-blue-400' : ($item->model_type==='semi_finished' ? 'bg-purple-400' : 'bg-violet-400') }}"></span>
                                        {{ $item->category_name ?: 'Tanpa Kategori' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Finansial (HPP, Jual, Nilai) --}}
                        <td class="text-right">
                            <div class="flex flex-col items-end justify-center py-1">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-[10px] text-[var(--inv-muted)]">HPP:</span>
                                    <span class="font-semibold tabular-nums text-[13px] text-[var(--inv-text)]">Rp{{ number_format($item->hpp,0,',','.') }}<span class="text-[10px] text-[var(--inv-muted)] font-normal">/{{ $item->unit_symbol }}</span></span>
                                </div>
                                @if($item->selling_price)
                                <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                                    <span class="text-gray-400">Jual:</span>
                                    <span class="font-medium text-gray-700">Rp{{ number_format($item->selling_price,0,',','.') }}</span>
                                </div>
                                @endif
                                <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                                    <span class="text-gray-400">Tot Nilai:</span>
                                    <span class="font-semibold text-emerald-600 tabular-nums border-b border-emerald-100 border-dashed pb-0.5" title="Total Nilai Inventori">Rp{{ number_format($item->inventory_value,0,',','.') }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Stok & Pergerakan --}}
                        <td class="text-right pr-4">
                            <div class="flex flex-col items-end py-1">
                                <span class="font-bold tabular-nums text-[15px] {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-[var(--inv-text)]') }}">
                                    {{ $item->quantity_fmt }} <span class="text-[11px] text-[var(--inv-muted)] font-medium">{{ $item->unit_symbol }}</span>
                                </span>
                                <div class="mt-1 flex flex-col items-end gap-0.5 text-[11px] text-gray-500">
                                    @if($item->min_stock > 0)
                                    <span>Min: <b class="{{ $item->quantity <= $item->min_stock ? 'text-red-500' : 'text-gray-600' }}">{{ number_format($item->min_stock,0) }}</b></span>
                                    @endif
                                    @if($item->usage_30d > 0)
                                    <span>Pakai 30hr: <b class="text-gray-600">{{ number_format($item->usage_30d,0) }}</b> {{ $item->unit_symbol }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Status & Kondisi --}}
                        <td class="text-center">
                            <div class="flex flex-col items-center justify-center gap-1.5 py-1">
                                @if($isOut)
                                <span class="inv-badge bg-red-50 text-red-600"><span class="dot bg-red-400"></span>Habis</span>
                                @elseif($isLow)
                                <span class="inv-badge bg-amber-50 text-amber-600"><span class="dot bg-amber-400"></span>Low Stock</span>
                                @elseif($item->needs_reorder)
                                <span class="inv-badge bg-blue-50 text-blue-600"><span class="dot bg-blue-400"></span>Reorder</span>
                                @else
                                <span class="inv-badge bg-emerald-50 text-emerald-600"><span class="dot bg-emerald-400"></span>Ready</span>
                                @endif

                                @if($item->model_type === 'product_variant')
                                    @if(($item->freshness ?? '-') === 'Fresh')
                                    <span class="text-[10px] text-emerald-500 mt-0.5 font-medium">{{ $item->days_left }}hr till exp</span>
                                    @elseif(($item->freshness ?? '') === 'Hampir Expired')
                                    <span class="text-[10px] font-bold text-orange-600 mt-0.5 animate-pulse">{{ $item->days_left ?? '?' }}hr till exp</span>
                                    @elseif(($item->freshness ?? '') === 'Expired')
                                    <span class="text-[10px] font-bold text-red-600 mt-0.5">Expired</span>
                                    @endif
                                @else
                                    @if(($item->almost_expired ?? 0) > 0)
                                    <button @click.stop="openExpired({{ $item->id }})" class="text-amber-600 font-semibold hover:text-amber-700 cursor-pointer text-[11px] mt-0.5 tabular-nums underline decoration-amber-200 decoration-dashed underline-offset-2">
                                        {{ number_format($item->almost_expired, $item->almost_expired == intval($item->almost_expired) ? 0 : 1) }} expiring &le;{{ $item->days_left }}hr
                                    </button>
                                    @endif
                                @endif
                                
                                @if($item->updated_at)
                                <span class="text-[9px] text-gray-400 mt-1">Upd: {{ $item->updated_at->diffForHumans(null, true) }}</span>
                                @endif
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="act-cell flex flex-col items-center justify-center gap-1">
                                @if($item->edit_url)
                                <a href="{{ $item->edit_url }}" @click.stop class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </a>
                                @endif
                                @if($item->batch_url)
                                <a href="{{ $item->batch_url }}" @click.stop class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition" title="Tambah Batch">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </a>
                                @endif
                                @if($item->can_delete)
                                <button @click.stop="deleteId={{ $item->id }}; deleteName='{{ addslashes($item->name) }}'; deleteType='{{ $item->model_type }}'; showDeleteModal=true"
                                        class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="text-[15px] text-[var(--inv-text)] font-semibold">Belum ada data inventory</p>
                            <p class="text-[13px] text-[var(--inv-muted)] mt-1 mb-4">Mulai dengan menambahkan pembelian bahan baku atau resep.</p>
                            <a href="{{ route('manager.inventory.stock.create') }}" class="inv-btn inv-btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Tambah Pembelian
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($inventoryItems->count() > 0)
                <tfoot>
                    <tr class="bg-[#f8f9fb] border-t border-[var(--inv-border)]">
                        <td colspan="2" class="py-3 pl-5 text-[11px] font-semibold text-[var(--inv-muted)] uppercase tracking-wider">
                            Total halaman ini: {{ $pageTotals->items }} item
                        </td>
                        <td class="py-3 text-right"></td>
                        <td class="py-3 items-center justify-center text-center">
                            <span class="text-[12px] font-bold text-[var(--inv-text)]" title="Total Nilai HPP Barang">Tot Nilai: Rp{{ number_format($pageTotals->value ?? 0, 0, ',', '.') }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- â”€â”€ Mobile Cards â”€â”€ --}}
        <div class="md:hidden">
            @forelse($inventoryItems as $item)
            @php
                $isOut = in_array($item->status, ['Out of Stock','Habis']);
                $isLow = $item->status === 'Low Stock';
                $accent = $isOut ? 'border-l-red-400' : ($isLow ? 'border-l-amber-400' : ($item->needs_reorder ? 'border-l-blue-400' : 'border-l-emerald-400'));
            @endphp
            <div class="mob-card border-l-[3px] {{ $accent }} {{ $isOut ? 'bg-red-50/30' : '' }} cursor-pointer" @click="$dispatch('open-detail', {type:'{{ $item->model_type }}', id:{{ $item->id }}})">
                <div class="flex items-start justify-between gap-2 mb-2.5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="inv-badge {{ $item->model_type==='stock' ? 'bg-blue-50 text-blue-600' : ($item->model_type==='semi_finished' ? 'bg-purple-50 text-purple-600' : 'bg-violet-50 text-violet-600') }} !text-[9px] !py-0 !px-1.5">{{ $item->model_type === 'stock' ? 'BB' : ($item->model_type==='semi_finished' ? 'SJ' : 'PJ') }}</span>
                            <span class="text-[11px] text-[var(--inv-muted)]">{{ $item->category_name }}</span>
                        </div>
                        <p class="font-semibold text-[var(--inv-text)] text-[13px] leading-snug truncate">{{ $item->name }}</p>
                        @if($item->subtitle && $item->subtitle !== 'Tanpa Varian')
                        <p class="text-[11px] text-[var(--inv-muted)] truncate">{{ $item->subtitle }}</p>
                        @endif
                    </div>
                    @if($isOut)
                    <span class="inv-badge bg-red-50 text-red-600 shrink-0"><span class="dot bg-red-400"></span>Habis</span>
                    @elseif($isLow)
                    <span class="inv-badge bg-amber-50 text-amber-600 shrink-0"><span class="dot bg-amber-400"></span>Low</span>
                    @elseif($item->needs_reorder)
                    <span class="inv-badge bg-blue-50 text-blue-600 shrink-0"><span class="dot bg-blue-400"></span>Reorder</span>
                    @else
                    <span class="inv-badge bg-emerald-50 text-emerald-600 shrink-0"><span class="dot bg-emerald-400"></span>Ready</span>
                    @endif
                </div>
                <div class="grid grid-cols-3 gap-x-3 gap-y-2 text-[12px] mb-3">
                    <div>
                        <span class="block text-[10px] text-[var(--inv-muted)] uppercase tracking-wider">Stok</span>
                        <span class="font-bold {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-[var(--inv-text)]') }}">{{ $item->quantity_fmt }} {{ $item->unit_symbol }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-[var(--inv-muted)] uppercase tracking-wider">HPP</span>
                        <span class="font-medium text-[var(--inv-text)]">Rp{{ number_format($item->hpp,0,',','.') }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-[var(--inv-muted)] uppercase tracking-wider">Nilai</span>
                        <span class="text-gray-600">Rp{{ number_format($item->inventory_value,0,',','.') }}</span>
                    </div>
                    @if($item->selling_price)
                    <div>
                        <span class="block text-[10px] text-[var(--inv-muted)] uppercase tracking-wider">Harga Jual</span>
                        <span class="font-medium">Rp{{ number_format($item->selling_price,0,',','.') }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-[var(--inv-muted)] uppercase tracking-wider">Margin</span>
                        <span class="{{ ($item->margin_percent ?? 0) >= 30 ? 'text-emerald-600' : '' }}">{{ $item->margin_percent ?? 0 }}%</span>
                    </div>
                    @endif
                    @if(($item->freshness ?? '-') !== '-' && $item->freshness !== null)
                    <div>
                        <span class="block text-[10px] text-[var(--inv-muted)] uppercase tracking-wider">Freshness</span>
                        @if($item->freshness === 'Fresh')
                        <span class="text-emerald-500 font-medium">Fresh{{ $item->days_left !== null ? " ({$item->days_left}hr)" : '' }}</span>
                        @elseif($item->freshness === 'Hampir Expired')
                        <span class="text-orange-500 font-medium">{{ $item->days_left ?? '?' }}hr lagi</span>
                        @elseif($item->freshness === 'Expired')
                        <span class="text-red-500 font-medium">Expired</span>
                        @endif
                    </div>
                    @endif
                </div>
                @if($item->edit_url || $item->batch_url || $item->can_delete)
                <div class="flex items-center justify-end gap-3 pt-2.5 border-t border-gray-100">
                    @if($item->edit_url)
                    <a href="{{ $item->edit_url }}" class="text-[11px] text-blue-500 font-medium inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>Edit
                    </a>
                    @endif
                    @if($item->batch_url)
                    <a href="{{ $item->batch_url }}" class="text-[11px] text-emerald-500 font-medium inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Batch
                    </a>
                    @endif
                    @if($item->can_delete)
                    <button @click="deleteId={{ $item->id }}; deleteName='{{ addslashes($item->name) }}'; deleteType='{{ $item->model_type }}'; showDeleteModal=true"
                            class="text-[11px] text-red-400 font-medium inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Hapus
                    </button>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <div class="py-20 text-center px-6">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-[14px] text-gray-500 font-semibold">Belum ada data inventory</p>
                <a href="{{ route('manager.inventory.stock.create') }}" class="inv-btn inv-btn-primary mt-4">Tambah Pembelian</a>
            </div>
            @endforelse
        </div>

        {{-- â”€â”€ Pagination â”€â”€ --}}
        @if($inventoryItems->hasPages())
        <div class="px-5 py-3 border-t border-[var(--inv-border)] flex items-center justify-between">
            <p class="text-[11px] text-[var(--inv-muted)] hidden sm:block">Halaman {{ $inventoryItems->currentPage() }} dari {{ $inventoryItems->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($inventoryItems->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $inventoryItems->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($inventoryItems->getUrlRange(max(1,$inventoryItems->currentPage()-2), min($inventoryItems->lastPage(),$inventoryItems->currentPage()+2)) as $page => $url)
                    @if($page == $inventoryItems->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-[var(--inv-accent)] text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($inventoryItems->hasMorePages())
                <a href="{{ $inventoryItems->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• G2. INSIGHT VISUALS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6 fade-in-up">
        {{-- Movement Chart --}}
        <div class="lg:col-span-2 inv-card p-6">
            <div class="section-title mb-4"><div class="bar" style="background:var(--inv-accent)"></div><h2>Pergerakan Stok 30 Hari</h2></div>
            <div style="height:260px;"><canvas id="movementChart"></canvas></div>
        </div>

        {{-- Fast & Slow Movers --}}
        <div class="inv-card p-6 flex flex-col">
            <div class="section-title mb-3"><div class="bar" style="background:var(--inv-accent)"></div><h2>Fast & Slow Movers</h2></div>

            {{-- Fast Movers --}}
            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-2">ðŸš€ Fast Movers</p>
            @forelse($fastMovers as $fm)
            <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="w-5 h-5 rounded flex items-center justify-center text-[9px] font-bold {{ $fm->model_type==='stock' ? 'bg-blue-50 text-blue-500' : ($fm->model_type==='semi_finished' ? 'bg-purple-50 text-purple-500' : 'bg-violet-50 text-violet-500') }}">{{ $fm->model_type==='stock' ? 'BB' : ($fm->model_type==='semi_finished' ? 'SJ' : 'PJ') }}</span>
                    <span class="text-[12px] font-medium text-[var(--inv-text)] truncate">{{ $fm->name }}</span>
                </div>
                <div class="text-right shrink-0 ml-2">
                    <span class="text-[12px] font-bold text-emerald-600 tabular-nums">{{ $fm->turnover_rate }}x</span>
                    <span class="block text-[10px] text-[var(--inv-muted)]">{{ number_format($fm->usage_30d,0) }} {{ $fm->unit_symbol }}/30hr</span>
                </div>
            </div>
            @empty
            <p class="text-[12px] text-[var(--inv-muted)] py-2">Belum ada data.</p>
            @endforelse

            <div class="my-3 border-t border-gray-100"></div>

            {{-- Slow Movers --}}
            <p class="text-[10px] font-bold text-red-400 uppercase tracking-wider mb-2">ðŸ¢ Slow Movers</p>
            @forelse($topSlowMovers as $sm)
            <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="w-5 h-5 rounded flex items-center justify-center text-[9px] font-bold {{ $sm->model_type==='stock' ? 'bg-blue-50 text-blue-500' : ($sm->model_type==='semi_finished' ? 'bg-purple-50 text-purple-500' : 'bg-violet-50 text-violet-500') }}">{{ $sm->model_type==='stock' ? 'BB' : ($sm->model_type==='semi_finished' ? 'SJ' : 'PJ') }}</span>
                    <span class="text-[12px] font-medium text-[var(--inv-text)] truncate">{{ $sm->name }}</span>
                </div>
                <div class="text-right shrink-0 ml-2">
                    <span class="text-[12px] font-bold text-red-500 tabular-nums">{{ $sm->turnover_rate }}x</span>
                    <span class="block text-[10px] text-[var(--inv-muted)]">{{ number_format($sm->usage_30d,0) }} {{ $sm->unit_symbol }}/30hr</span>
                </div>
            </div>
            @empty
            <p class="text-[12px] text-[var(--inv-muted)] py-2">Tidak ada slow movers.</p>
            @endforelse
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• H. R&D APPROVED â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($approvedProjects->isNotEmpty())
    <div class="mt-6 fade-in-up">
        <div class="section-title"><div class="bar" style="background:var(--inv-accent)"></div><h2>R&D Disetujui</h2></div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($approvedProjects as $project)
            <div class="inv-card p-6">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-[14px] font-bold text-[var(--inv-text)]">{{ $project->id }}</h3>
                        <p class="text-[12px] text-[var(--inv-muted)] mt-0.5">{{ $project->deskripsi }}</p>
                    </div>
                    <span class="inv-badge bg-blue-50 text-blue-600"><span class="dot bg-blue-400"></span>Approved</span>
                </div>
                <div class="overflow-x-auto mb-4 -mx-1">
                    <table class="w-full text-[12px]">
                        <thead><tr class="border-b border-gray-100">
                            <th class="text-left py-1.5 px-1 text-[10px] font-semibold text-[var(--inv-muted)] uppercase">Bahan</th>
                            <th class="text-right py-1.5 px-1 text-[10px] font-semibold text-[var(--inv-muted)] uppercase">Jumlah</th>
                            <th class="text-left py-1.5 px-1 text-[10px] font-semibold text-[var(--inv-muted)] uppercase">Unit</th>
                            <th class="text-right py-1.5 px-1 text-[10px] font-semibold text-[var(--inv-muted)] uppercase">Biaya</th>
                        </tr></thead>
                        <tbody>
                            @foreach($project->stockUsages as $usage)
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
                <a href="{{ route('manager.inventory.stock.batch.createFromRnd', $project->id) }}" class="inv-btn inv-btn-primary inv-btn-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Isi Stok dari R&D
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• I. EXPIRED STORED â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if($storedExpiredStocks->count() > 0)
    <div class="mt-6 fade-in-up">
        <div class="section-title"><div class="bar bg-red-500"></div><h2 class="!text-red-700">Stok Expired Masih Disimpan</h2></div>
        <div class="inv-card overflow-hidden border-red-200">
            <table class="w-full text-[13px]">
                <thead><tr class="bg-red-50/60 border-b border-red-100">
                    <th class="text-left py-2.5 px-5 text-[10px] font-semibold text-red-400 uppercase tracking-wider">Nama</th>
                    <th class="text-right py-2.5 px-4 text-[10px] font-semibold text-red-400 uppercase tracking-wider">Expired</th>
                    <th class="text-right py-2.5 px-4 text-[10px] font-semibold text-red-400 uppercase tracking-wider">Disimpan</th>
                    <th class="text-center py-2.5 px-4 text-[10px] font-semibold text-red-400 uppercase tracking-wider">Aksi</th>
                </tr></thead>
                <tbody>
                    @foreach($storedExpiredStocks as $es)
                    <tr class="border-b border-red-50 bg-red-50/20">
                        <td class="py-3 px-5 font-medium text-[var(--inv-text)]">{{ $es->name }}</td>
                        <td class="py-3 px-4 text-right tabular-nums text-red-600 font-semibold">{{ number_format($es->expired ?? 0, 0) }}</td>
                        <td class="py-3 px-4 text-right tabular-nums text-red-600 font-semibold">{{ number_format($es->stored_expired ?? 0, 0) }} {{ $es->raw_model->unit->symbol ?? '' }}</td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" action="{{ route('manager.inventory.stock.reduceExpiredStored', $es->id) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Kurangi stok expired yang masih disimpan?')"
                                        class="inv-btn inv-btn-sm inv-btn-danger">
                                    Kurangi {{ number_format($es->stored_expired ?? 0, 0) }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• DELETE MODAL â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div x-show="showDeleteModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-[2px]"
         @click.self="showDeleteModal=false" @keydown.escape.window="showDeleteModal=false">
        <div x-show="showDeleteModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-xl w-full max-w-[360px] mx-4 p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-[15px] font-bold text-[var(--inv-text)] mb-1">Hapus Item?</h3>
            <p class="text-[13px] text-[var(--inv-muted)] mb-5 leading-relaxed">
                <span class="font-semibold text-gray-600" x-text="deleteName"></span> akan dihapus permanen.
            </p>
            <div class="flex gap-3">
                <button @click="showDeleteModal=false" class="flex-1 h-10 rounded-lg border border-[var(--inv-border)] text-[13px] font-medium text-gray-500 hover:bg-gray-50 hover:bg-gray-100 transition cursor-pointer">â† Batal</button>
                <form :action="'/manager/inventory/stock/' + deleteId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full h-10 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[13px] font-semibold transition cursor-pointer">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• EXPIRED BATCH MODAL â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div x-show="showExpiredModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-[2px]"
         @click.self="showExpiredModal=false" @keydown.escape.window="showExpiredModal=false">
        <div x-show="showExpiredModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-2xl shadow-xl w-full max-w-[400px] mx-4 p-6 relative">
            <button @click="showExpiredModal=false" class="absolute top-3 right-3 w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-[var(--inv-text)]">Batch Hampir Expired</h3>
            </div>
            <template x-if="expiredBatches.length===0">
                <p class="text-[13px] text-[var(--inv-muted)] py-4 text-center">Tidak ada batch hampir expired.</p>
            </template>
            <div class="space-y-2 max-h-[300px] overflow-y-auto">
                <template x-for="(batch,idx) in expiredBatches" :key="idx">
                    <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-lg">
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-[var(--inv-muted)]">Batch ID</span>
                            <span class="font-semibold text-[var(--inv-text)]" x-text="'#'+batch.id"></span>
                        </div>
                        <div class="flex items-center justify-between text-[12px] mt-1">
                            <span class="text-[var(--inv-muted)]">Kuantitas</span>
                            <span class="font-semibold text-amber-600" x-text="batch.qty+' '+(batch.unit||'')"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• J. DETAIL DRAWER â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div x-data="detailDrawer()" @open-detail.window="openDetail($event.detail.type, $event.detail.id)">
        {{-- Overlay --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/15 backdrop-blur-[1px]" @click="open=false"></div>
        {{-- Drawer --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="fixed right-0 top-0 bottom-0 z-50 w-full max-w-[480px] bg-white shadow-2xl flex flex-col overflow-hidden">
            {{-- Drawer header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--inv-border)]" style="background:var(--inv-bg)">
                <div class="min-w-0 flex-1">
                    <p class="text-[15px] font-bold text-[var(--inv-text)] truncate" x-text="detail?.name || 'Detail'"></p>
                    <p class="text-[11px] text-[var(--inv-muted)] mt-0.5" x-text="detail?.category || ''"></p>
                </div>
                <button @click="open=false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition cursor-pointer ml-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Loading state --}}
            <div x-show="loading" class="flex-1 flex items-center justify-center">
                <svg class="w-8 h-8 animate-spin" style="color:var(--inv-accent)" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
            </div>

            {{-- Detail content --}}
            <div x-show="!loading && detail" class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
                {{-- KPI row --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-blue-50/50 rounded-xl p-3 text-center border border-blue-100">
                        <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Stok</p>
                        <p class="text-[18px] font-bold mt-1 tabular-nums" :class="(detail?.quantity??0)<=0?'text-red-600':((detail?.status??'')==='Low Stock'?'text-amber-600':'text-[var(--inv-text)]')" x-text="formatNumber(detail?.quantity)+' '+(detail?.unit||'')"></p>
                    </div>
                    <div class="bg-violet-50/50 rounded-xl p-3 text-center border border-violet-100">
                        <p class="text-[10px] font-bold text-violet-500 uppercase tracking-wider">HPP</p>
                        <p class="text-[18px] font-bold text-violet-600 mt-1 tabular-nums" x-text="formatRp(detail?.hpp)"></p>
                    </div>
                    <div class="bg-emerald-50/50 rounded-xl p-3 text-center border border-emerald-100">
                        <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Nilai</p>
                        <p class="text-[18px] font-bold text-emerald-600 mt-1 tabular-nums" x-text="formatRp(detail?.inventory_value)"></p>
                    </div>
                </div>

                {{-- Extra info --}}
                <div class="space-y-2">
                    <div class="flex justify-between text-[12px]"><span class="text-[var(--inv-muted)]">Status</span>
                        <span class="font-semibold" :class="{
                            'text-red-600': detail?.status==='Out of Stock'||detail?.status==='Habis',
                            'text-amber-600': detail?.status==='Low Stock',
                            'text-emerald-600': detail?.status==='Ready'
                        }" x-text="detail?.status"></span>
                    </div>
                    <div class="flex justify-between text-[12px]"><span class="text-[var(--inv-muted)]">Supplier</span><span class="font-medium text-[var(--inv-text)]" x-text="detail?.supplier||'-'"></span></div>
                    <div class="flex justify-between text-[12px]"><span class="text-[var(--inv-muted)]">Min Stok</span><span class="font-medium text-[var(--inv-text)] tabular-nums" x-text="formatNumber(detail?.min_stock)"></span></div>
                    <template x-if="detail?.selling_price">
                        <div class="flex justify-between text-[12px]"><span class="text-[var(--inv-muted)]">Harga Jual</span><span class="font-medium text-[var(--inv-text)] tabular-nums" x-text="formatRp(detail?.selling_price)"></span></div>
                    </template>
                    <template x-if="detail?.margin !== undefined && detail?.margin !== null">
                        <div class="flex justify-between text-[12px]"><span class="text-[var(--inv-muted)]">Margin</span><span class="font-semibold tabular-nums" :class="detail.margin>=30?'text-emerald-600':(detail.margin<15?'text-red-500':'text-[var(--inv-text)]')" x-text="detail.margin+'%'"></span></div>
                    </template>
                    <div class="flex justify-between text-[12px]"><span class="text-[var(--inv-muted)]">Penjualan 30hr</span><span class="font-medium text-[var(--inv-text)] tabular-nums" x-text="(detail?.sales_30d??0)+' transaksi'"></span></div>
                </div>

                {{-- Batches (raw materials only) --}}
                <template x-if="detail?.batches && detail.batches.length > 0">
                    <div>
                        <p class="text-[10px] font-bold text-[var(--inv-muted)] uppercase tracking-wider mb-2">Batch Terakhir</p>
                        <div class="space-y-1.5">
                            <template x-for="(batch,idx) in detail.batches" :key="idx">
                                <div class="bg-gray-50 rounded-lg p-2.5 flex items-center justify-between text-[12px]">
                                    <div>
                                        <span class="text-[var(--inv-muted)]" x-text="batch.buy_date"></span>
                                        <span class="ml-2 font-medium text-[var(--inv-text)]" x-text="batch.supplier"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold tabular-nums" x-text="batch.qty+' '+batch.unit"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Recent movements --}}
                <template x-if="detail?.movements && detail.movements.length > 0">
                    <div>
                        <p class="text-[10px] font-bold text-[var(--inv-muted)] uppercase tracking-wider mb-2">Riwayat Pergerakan</p>
                        <div class="space-y-1">
                            <template x-for="(mv,idx) in detail.movements.slice(0,10)" :key="idx">
                                <div class="flex items-center justify-between text-[12px] py-1.5 border-b border-gray-50">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded flex items-center justify-center text-[9px] font-bold" :class="mv.qty>0?'bg-emerald-50 text-emerald-500':'bg-red-50 text-red-500'" x-text="mv.qty>0?'IN':'OUT'"></span>
                                        <span class="text-[var(--inv-muted)]" x-text="mv.date"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-semibold tabular-nums" :class="mv.qty>0?'text-emerald-600':'text-red-500'" x-text="(mv.qty>0?'+':'')+mv.qty+' '+mv.unit"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Drawer footer --}}
            <div x-show="!loading && detail" class="px-6 py-4 border-t border-[var(--inv-border)] flex items-center gap-2" style="background:var(--inv-bg)">
                <template x-if="detail?.edit_url">
                    <a :href="detail.edit_url" class="inv-btn inv-btn-primary flex-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        Edit
                    </a>
                </template>
                <template x-if="detail?.batch_url">
                    <a :href="detail.batch_url" class="inv-btn inv-btn-success flex-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Beli / Batch
                    </a>
                </template>
                <button @click="open=false" class="inv-btn inv-btn-outline flex-1 justify-center">Tutup</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
// â”€â”€ Detail Drawer Component â”€â”€
document.addEventListener('alpine:init', () => {
    Alpine.data('detailDrawer', () => ({
        open: false,
        loading: false,
        detail: null,
        async openDetail(type, id) {
            this.open = true;
            this.loading = true;
            this.detail = null;
            try {
                const res = await fetch(`/manager/inventory/stock/detail/${type}/${id}`);
                if (!res.ok) throw new Error('Failed');
                this.detail = await res.json();
            } catch (e) {
                console.error('Detail fetch error:', e);
                this.detail = { name: 'Error', category: 'Gagal memuat data' };
            } finally {
                this.loading = false;
            }
        },
        formatRp(v) {
            if (!v && v !== 0) return 'Rp0';
            return 'Rp' + Math.round(v).toLocaleString('id-ID');
        },
        formatNumber(v) {
            if (!v && v !== 0) return '0';
            return Number(v).toLocaleString('id-ID');
        }
    }));
});

// â”€â”€ Listen for drawer open from table row clicks â”€â”€
window.openStockDetail = function(type, id) {
    const drawerEl = document.querySelector('[x-data*="detailDrawer"]');
    if (drawerEl && drawerEl.__x) {
        drawerEl.__x.$data.openDetail(type, id);
    } else if (drawerEl) {
        // Alpine v3
        Alpine.evaluate(drawerEl, `openDetail('${type}', ${id})`);
    }
};

// â”€â”€ Movement Chart â”€â”€
document.addEventListener('DOMContentLoaded', function() {
    const mvCtx = document.getElementById('movementChart');
    if (!mvCtx) return;

    const chartData = @json($movementChart);

    new Chart(mvCtx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Masuk',
                    data: chartData.in,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                },
                {
                    label: 'Keluar',
                    data: chartData.out,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.06)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 }, usePointStyle: true, pointStyle: 'circle', padding: 16 } },
                tooltip: {
                    backgroundColor: '#1e293b', titleFont: { size: 11 }, bodyFont: { size: 11 }, padding: 10, cornerRadius: 8,
                    callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('id-ID') + ' unit' }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af', maxRotation: 0 } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, color: '#9ca3af' } }
            }
        }
    });
});
</script>
@endpush
@endsection

