@extends('handai-manager.layouts.master')
@section('title', 'Inventory Control')

@section('content')
<style>
[x-cloak]{display:none!important}
:root{--inv-bg:#f8f9fb;--inv-card:#fff;--inv-border:#eceef2;--inv-muted:#8b919e;--inv-text:#1e2330;--inv-accent:#3b82f6;--inv-success:#10b981;--inv-warn:#f59e0b;--inv-danger:#ef4444;--inv-purple:#8b5cf6}
.inv-card{background:var(--inv-card);border:1px solid var(--inv-border);border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
.inv-input{height:36px;padding:0 12px;font-size:13px;border:1px solid var(--inv-border);border-radius:8px;background:#fff;outline:none;transition:border .15s,box-shadow .15s;width:100%}
.inv-input:focus{border-color:var(--inv-accent);box-shadow:0 0 0 3px rgba(59,130,246,.08)}
.inv-btn{display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 14px;font-size:13px;font-weight:500;border-radius:8px;border:none;cursor:pointer;transition:all .15s;white-space:nowrap}
.inv-btn-sm{height:30px;padding:0 10px;font-size:11px;gap:4px}
.inv-btn-primary{background:var(--inv-accent);color:#fff}.inv-btn-primary:hover{background:#2563eb}
.inv-btn-outline{background:#fff;border:1px solid var(--inv-border);color:#4b5563}.inv-btn-outline:hover{border-color:#d1d5db;background:#f9fafb}
.inv-btn-success{background:var(--inv-success);color:#fff}.inv-btn-success:hover{background:#059669}
.inv-btn-danger{background:var(--inv-danger);color:#fff}.inv-btn-danger:hover{background:#dc2626}
.inv-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;line-height:1.4;white-space:nowrap}
.inv-badge .dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.inv-table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
.inv-table thead th{position:sticky;top:0;z-index:5;background:#f8f9fb;padding:10px 14px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--inv-muted);border-bottom:1px solid var(--inv-border);white-space:nowrap}
.inv-table tbody td{padding:12px 14px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.inv-table tbody tr{transition:background .12s}
.inv-table tbody tr:hover{background:#f1f5ff}
.inv-table tbody tr:last-child td{border-bottom:none}
.inv-table tbody tr.row-danger{background:#fef2f2}.inv-table tbody tr.row-danger:hover{background:#fee2e2}
.inv-table tbody tr.row-warn{background:#fffbeb}.inv-table tbody tr.row-warn:hover{background:#fef3c7}
.inv-table .act-cell{opacity:0;transition:opacity .12s}
.inv-table tbody tr:hover .act-cell{opacity:1}
@media(max-width:767px){.inv-table .act-cell{opacity:1}}
.sort-link{cursor:pointer;user-select:none;transition:color .12s}.sort-link:hover{color:var(--inv-accent)}
.stat{padding:16px 18px;position:relative;overflow:hidden}
.stat .stat-bar{position:absolute;top:0;left:0;right:0;height:3px;border-radius:12px 12px 0 0}
.tip{position:relative}.tip:hover .tip-text{opacity:1;transform:translateY(0);pointer-events:auto}
.tip-text{position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%) translateY(4px);padding:5px 10px;border-radius:6px;background:#1e293b;color:#fff;font-size:11px;font-weight:500;white-space:nowrap;opacity:0;pointer-events:none;transition:all .18s;z-index:30}
.tip-text::after{content:'';position:absolute;top:100%;left:50%;margin-left:-4px;border:4px solid transparent;border-top-color:#1e293b}
.mob-card{padding:16px;border-bottom:1px solid #f1f5f9;transition:background .12s}.mob-card:last-child{border-bottom:none}.mob-card:hover{background:#f8fafc}
.col-toggle{position:absolute;right:0;top:calc(100% + 4px);background:#fff;border:1px solid var(--inv-border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.08);padding:12px;z-index:40;min-width:200px}
.val-card{display:flex;align-items:center;gap:14px;padding:18px 20px}
.val-card .val-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.action-item{display:flex;align-items:flex-start;gap:10px;padding:10px 14px;border-radius:10px;transition:background .12s}
.action-item:hover{background:#f8fafc}
.filter-chip{display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:500;cursor:pointer;transition:all .15s;border:1px solid var(--inv-border);background:#fff;color:#6b7280;text-decoration:none}
.filter-chip:hover{border-color:#d1d5db;background:#f9fafb}
.filter-chip.active{background:var(--inv-accent);color:#fff;border-color:var(--inv-accent)}
.filter-chip-warn.active{background:var(--inv-warn);border-color:var(--inv-warn)}
.filter-chip-danger.active{background:var(--inv-danger);border-color:var(--inv-danger)}
.filter-chip-success.active{background:var(--inv-success);border-color:var(--inv-success)}
.section-title{display:flex;align-items:center;gap:8px;margin-bottom:16px}
.section-title .bar{width:4px;height:20px;border-radius:4px}
.section-title h2{font-size:15px;font-weight:700;color:var(--inv-text)}
</style>

@php
    $currentSort = request('sort','name');
    $currentDir  = request('dir','asc');
    $sortUrl = function($field) {
        $dir = (request('sort','name')===$field && request('dir','asc')==='asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort'=>$field,'dir'=>$dir,'page'=>null]);
    };
    $sortIcon = function($field) {
        if(request('sort','name')!==$field) return '<span class="text-gray-300 ml-0.5">⇅</span>';
        return request('dir','asc')==='asc' ? '<span class="text-blue-500 ml-0.5">↑</span>' : '<span class="text-blue-500 ml-0.5">↓</span>';
    };
    $typeFilter = $type ?? 'all';
    $fmtRp = fn($v) => 'Rp' . number_format((float)($v ?? 0), 0, ',', '.');
@endphp

<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto" style="background:var(--inv-bg);min-height:100vh"
     x-data="{
        showColToggle: false,
        cols: JSON.parse(localStorage.getItem('inv_cols') || '{&quot;type&quot;:true,&quot;category&quot;:true,&quot;hpp&quot;:true,&quot;sell&quot;:true,&quot;min&quot;:true,&quot;expired&quot;:true,&quot;value&quot;:true,&quot;usage&quot;:true,&quot;updated&quot;:false}'),
        toggleCol(key){ this.cols[key]=!this.cols[key]; localStorage.setItem('inv_cols',JSON.stringify(this.cols)); },
        deleteId:null, deleteName:'', deleteType:'', showDeleteModal:false,
        showExpiredModal:false, expiredBatches:[],
        expiredData: @js($expiredBatchesMap),
        openExpired(id){this.expiredBatches=this.expiredData[id]||[];this.showExpiredModal=true},
        showFinance: false,
        showActions: true
     }">

    {{-- ═══════════════════════ FLASH MESSAGES ═══════════════════════ --}}
    @if(session('success'))
    <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2.5" x-data x-init="setTimeout(()=>$el.remove(),5000)">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ═══════════════════════ A. HEADER ═══════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[22px] font-bold text-[var(--inv-text)] tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-[var(--inv-accent)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Inventory Control
            </h1>
            <p class="text-[13px] text-[var(--inv-muted)] mt-0.5">{{ $selected_store->name ?? 'Store' }} &middot; <span class="tabular-nums">{{ now()->translatedFormat('d M Y') }}</span></p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('manager.inventory.stock.create') }}" class="inv-btn inv-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Pembelian
            </a>
        </div>
    </div>

    {{-- ═══════════════════════ B. WAREHOUSE VALUATION PANEL ═══════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- B1. Bahan Baku --}}
        <div class="inv-card val-card border-l-[3px] border-l-blue-400">
            <div class="val-icon bg-blue-50">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Bahan Baku</p>
                <p class="text-xl font-bold text-[var(--inv-text)] tabular-nums mt-0.5">{{ number_format($stats->total_bahan) }} <span class="text-[13px] font-normal text-[var(--inv-muted)]">item</span></p>
                <div class="flex items-center gap-3 mt-1">
                    <div class="flex items-center gap-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span><span class="text-[var(--inv-muted)]">Ready</span><span class="font-semibold text-[var(--inv-text)]">{{ $stats->raw_ready }}</span></div>
                    <div class="flex items-center gap-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span><span class="text-[var(--inv-muted)]">Low</span><span class="font-semibold text-amber-600">{{ $stats->raw_low }}</span></div>
                    <div class="flex items-center gap-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span><span class="text-[var(--inv-muted)]">Habis</span><span class="font-semibold text-red-600">{{ $stats->raw_out }}</span></div>
                </div>
                <p class="text-[13px] font-bold text-blue-600 mt-1.5 tabular-nums">{{ $fmtRp($stats->raw_value) }}</p>
            </div>
        </div>
        {{-- B2. Produk Jadi --}}
        <div class="inv-card val-card border-l-[3px] border-l-violet-400">
            <div class="val-icon bg-violet-50">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-violet-500 uppercase tracking-wider">Produk Jadi</p>
                <p class="text-xl font-bold text-[var(--inv-text)] tabular-nums mt-0.5">{{ number_format($stats->total_produk) }} <span class="text-[13px] font-normal text-[var(--inv-muted)]">item</span></p>
                <div class="flex items-center gap-3 mt-1">
                    <div class="flex items-center gap-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span><span class="text-[var(--inv-muted)]">Ready</span><span class="font-semibold text-[var(--inv-text)]">{{ $stats->fg_ready }}</span></div>
                    <div class="flex items-center gap-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span><span class="text-[var(--inv-muted)]">Low</span><span class="font-semibold text-amber-600">{{ $stats->fg_low }}</span></div>
                    <div class="flex items-center gap-1 text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span><span class="text-[var(--inv-muted)]">Habis</span><span class="font-semibold text-red-600">{{ $stats->fg_out }}</span></div>
                </div>
                <div class="flex items-center gap-3 mt-1.5">
                    <div>
                        <span class="text-[10px] text-[var(--inv-muted)]">HPP</span>
                        <span class="text-[13px] font-bold text-violet-600 tabular-nums ml-1">{{ $fmtRp($stats->fg_value) }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-[var(--inv-muted)]">Jual</span>
                        <span class="text-[13px] font-bold text-emerald-600 tabular-nums ml-1">{{ $fmtRp($stats->fg_selling_value ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- B3. Total Gudang --}}
        <div class="inv-card val-card border-l-[3px] border-l-emerald-400">
            <div class="val-icon bg-emerald-50">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Total Estimasi Gudang</p>
                <p class="text-xl font-bold text-[var(--inv-text)] tabular-nums mt-0.5">{{ number_format($stats->total_items) }} <span class="text-[13px] font-normal text-[var(--inv-muted)]">item</span></p>
                <p class="text-[18px] font-bold text-emerald-600 mt-1 tabular-nums">{{ $fmtRp($stats->total_value) }}</p>
                <p class="text-[10px] text-[var(--inv-muted)] mt-0.5">Bahan {{ $fmtRp($stats->raw_value) }} + FG {{ $fmtRp($stats->fg_value) }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ C. STATUS OVERVIEW STRIP ═══════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="inv-card stat">
            <div class="stat-bar bg-emerald-400"></div>
            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Ready</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1 tabular-nums">{{ number_format($stats->ready) }}</p>
        </div>
        <div class="inv-card stat">
            <div class="stat-bar bg-amber-400"></div>
            <p class="text-[10px] font-bold text-amber-500 uppercase tracking-wider">Hampir Habis</p>
            <p class="text-2xl font-bold text-amber-600 mt-1 tabular-nums">{{ number_format($stats->low_stock) }}</p>
        </div>
        <div class="inv-card stat">
            <div class="stat-bar bg-red-400"></div>
            <p class="text-[10px] font-bold text-red-400 uppercase tracking-wider">Habis</p>
            <p class="text-2xl font-bold text-red-500 mt-1 tabular-nums">{{ number_format($stats->out_of_stock) }}</p>
        </div>
        <div class="inv-card stat">
            <div class="stat-bar bg-blue-400"></div>
            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Perlu Reorder</p>
            <p class="text-2xl font-bold text-blue-600 mt-1 tabular-nums">{{ number_format($stats->reorder) }}</p>
        </div>
        <div class="inv-card stat">
            <div class="stat-bar bg-orange-400"></div>
            <p class="text-[10px] font-bold text-orange-500 uppercase tracking-wider">Hampir Expired</p>
            <p class="text-2xl font-bold text-orange-500 mt-1 tabular-nums">{{ number_format($stats->almost_expired) }}</p>
        </div>
        <div class="inv-card stat">
            <div class="stat-bar bg-gray-400"></div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Slow Movers</p>
            <p class="text-2xl font-bold text-gray-500 mt-1 tabular-nums">{{ number_format($stats->slow_movers) }}</p>
            <p class="text-[10px] text-[var(--inv-muted)]">turnover &lt;0.3x</p>
        </div>
        <div class="inv-card stat">
            <div class="stat-bar bg-rose-400"></div>
            <p class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Waste/Expired 30d</p>
            <p class="text-lg font-bold text-rose-500 mt-1 tabular-nums leading-tight">{{ $fmtRp($stats->waste_expired_loss_30d) }}</p>
        </div>
    </div>

    {{-- ═══════════════════════ D. ACTION ITEMS (Operational Insights) ═══════════════════════ --}}
    @if($actionItems->count() > 0)
    <div class="inv-card mb-6 overflow-hidden" x-data="{open:true}">
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

    {{-- ═══════════════════════ E. FINANCE INTEGRATION ═══════════════════════ --}}
    @if($financeData->available ?? false)
    <div class="inv-card mb-6 overflow-hidden">
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

    {{-- ═══════════════════════ F. FILTER BAR ═══════════════════════ --}}
    <form method="GET" action="{{ route('manager.inventory.stock') }}" id="filterForm" class="inv-card p-4 mb-5">
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
                    <option value="ready" {{ request('status')==='ready'?'selected':'' }}>✅ Ready</option>
                    <option value="low_stock" {{ request('status')==='low_stock'?'selected':'' }}>⚠️ Hampir Habis</option>
                    <option value="out_of_stock" {{ request('status')==='out_of_stock'?'selected':'' }}>🚫 Habis</option>
                    <option value="reorder" {{ request('status')==='reorder'?'selected':'' }}>🔄 Perlu Reorder</option>
                    <option value="almost_expired" {{ request('status')==='almost_expired'?'selected':'' }}>⏰ Hampir Expired</option>
                    <option value="expired" {{ request('status')==='expired'?'selected':'' }}>☠️ Expired</option>
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
            <span class="inv-badge {{ request('type')==='bahan' ? 'bg-blue-50 text-blue-600' : 'bg-violet-50 text-violet-600' }}">
                {{ request('type')==='bahan' ? 'Bahan Baku' : 'Produk Jadi' }}
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

    {{-- ═══════════════════════ G. DATA TABLE ═══════════════════════ --}}
    <div class="inv-card overflow-hidden">
        {{-- Toolbar --}}
        <div class="px-5 py-3 border-b border-[var(--inv-border)] flex items-center justify-between gap-4">
            <p class="text-[12px] text-[var(--inv-muted)]">
                <span class="font-semibold text-[var(--inv-text)]">{{ $inventoryItems->firstItem() ?? 0 }}–{{ $inventoryItems->lastItem() ?? 0 }}</span>
                dari {{ number_format($inventoryItems->total()) }} item
                @if(($stats->inactive ?? 0) > 0)
                <span class="mx-1 text-gray-300">&middot;</span>{{ $stats->inactive }} nonaktif
                @endif
            </p>
            <div class="flex items-center gap-2">
                {{-- Column toggle --}}
                <div class="relative">
                    <button @click="showColToggle=!showColToggle" class="inv-btn inv-btn-outline !px-2.5 !h-8 text-[11px]" title="Pilih kolom">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    </button>
                    <div x-show="showColToggle" @click.outside="showColToggle=false" x-cloak x-transition class="col-toggle">
                        <p class="text-[10px] font-bold text-[var(--inv-muted)] uppercase tracking-wider mb-2">Tampilkan Kolom</p>
                        <template x-for="[key,label] in [['type','Tipe'],['category','Kategori'],['hpp','HPP'],['sell','Harga Jual'],['min','Min Stok'],['expired','Expired'],['value','Nilai'],['usage','Pakai 30hr'],['updated','Last Update']]">
                            <label class="flex items-center gap-2 py-1.5 cursor-pointer text-[12px] text-gray-600 hover:text-gray-800">
                                <input type="checkbox" :checked="cols[key]" @change="toggleCol(key)" class="rounded border-gray-300 text-blue-500 w-3.5 h-3.5 cursor-pointer"/>
                                <span x-text="label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Desktop Table ── --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th class="text-left pl-5 w-[260px]"><a href="{{ $sortUrl('name') }}" class="sort-link">Nama {!! $sortIcon('name') !!}</a></th>
                        <th class="text-left" x-show="cols.type"><a href="{{ $sortUrl('type') }}" class="sort-link">Tipe {!! $sortIcon('type') !!}</a></th>
                        <th class="text-left" x-show="cols.category">Kategori</th>
                        <th class="text-right" x-show="cols.hpp"><a href="{{ $sortUrl('hpp') }}" class="sort-link">HPP {!! $sortIcon('hpp') !!}</a></th>
                        <th class="text-right" x-show="cols.sell">Harga Jual</th>
                        <th class="text-right"><a href="{{ $sortUrl('quantity') }}" class="sort-link">Stok {!! $sortIcon('quantity') !!}</a></th>
                        <th class="text-right" x-show="cols.min">Min Stok</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" x-show="cols.expired">Expired</th>
                        <th class="text-right" x-show="cols.value"><a href="{{ $sortUrl('value') }}" class="sort-link">Nilai {!! $sortIcon('value') !!}</a></th>
                        <th class="text-right" x-show="cols.usage">Pakai 30hr</th>
                        <th class="text-right" x-show="cols.updated"><a href="{{ $sortUrl('updated_at') }}" class="sort-link">Update {!! $sortIcon('updated_at') !!}</a></th>
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
                    <tr class="{{ $rowClass }}">
                        {{-- Name --}}
                        <td class="pl-5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-[10px] font-bold {{ $item->model_type==='stock' ? 'bg-blue-50 text-blue-500' : 'bg-violet-50 text-violet-500' }}">
                                    {{ $item->model_type==='stock' ? 'BB' : 'PJ' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-[var(--inv-text)] leading-snug truncate text-[13px]">{{ $item->name }}</p>
                                    @if($item->subtitle && $item->subtitle !== 'Tanpa Varian')
                                    <p class="text-[11px] text-[var(--inv-muted)] mt-0.5 truncate leading-none">{{ $item->subtitle }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- Type --}}
                        <td x-show="cols.type">
                            <span class="inv-badge {{ $item->model_type==='stock' ? 'bg-blue-50 text-blue-600' : 'bg-violet-50 text-violet-600' }}">
                                {{ $item->type_label }}
                            </span>
                        </td>
                        {{-- Category --}}
                        <td x-show="cols.category" class="text-[12px] text-gray-500">{{ $item->category_name }}</td>
                        {{-- HPP --}}
                        <td x-show="cols.hpp" class="text-right tabular-nums font-medium text-[var(--inv-text)]">
                            <span class="tip">
                                Rp{{ number_format($item->hpp,0,',','.') }}
                                <span class="tip-text">Harga Pokok Produksi</span>
                            </span>
                            @if($item->unit_symbol)
                            <span class="text-[10px] text-[var(--inv-muted)] font-normal">/{{ $item->unit_symbol }}</span>
                            @endif
                        </td>
                        {{-- Selling price --}}
                        <td x-show="cols.sell" class="text-right tabular-nums text-[12px]">
                            @if($item->selling_price)
                            <span class="font-medium text-[var(--inv-text)]">Rp{{ number_format($item->selling_price,0,',','.') }}</span>
                            @if($item->margin_percent !== null)
                            <span class="block text-[10px] {{ ($item->margin_percent ?? 0) >= 30 ? 'text-emerald-500' : (($item->margin_percent ?? 0) < 15 ? 'text-red-400' : 'text-[var(--inv-muted)]') }}">
                                margin {{ $item->margin_percent }}%
                            </span>
                            @endif
                            @else
                            <span class="text-gray-200">&mdash;</span>
                            @endif
                        </td>
                        {{-- Quantity --}}
                        <td class="text-right">
                            <span class="font-bold tabular-nums text-[14px] {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-[var(--inv-text)]') }}">
                                {{ $item->quantity_fmt }}
                            </span>
                            <span class="text-[10px] text-[var(--inv-muted)] ml-0.5">{{ $item->unit_symbol }}</span>
                        </td>
                        {{-- Min Stock --}}
                        <td x-show="cols.min" class="text-right tabular-nums text-[12px]">
                            @if($item->min_stock > 0)
                            <span class="{{ $item->quantity <= $item->min_stock ? 'text-red-500 font-semibold' : 'text-gray-500' }}">{{ number_format($item->min_stock,0) }}</span>
                            @if($item->reorder_point > 0)
                            <span class="text-[10px] text-[var(--inv-muted)]"> / {{ number_format($item->reorder_point,0) }}</span>
                            @endif
                            @else
                            <span class="text-gray-200">&mdash;</span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="text-center">
                            @if($isOut)
                            <span class="inv-badge bg-red-50 text-red-600"><span class="dot bg-red-400"></span>Habis</span>
                            @elseif($isLow)
                            <span class="inv-badge bg-amber-50 text-amber-600"><span class="dot bg-amber-400"></span>Low</span>
                            @elseif($item->needs_reorder)
                            <span class="inv-badge bg-blue-50 text-blue-600"><span class="dot bg-blue-400"></span>Reorder</span>
                            @else
                            <span class="inv-badge bg-emerald-50 text-emerald-600"><span class="dot bg-emerald-400"></span>Ready</span>
                            @endif
                        </td>
                        {{-- Expired / Freshness --}}
                        <td x-show="cols.expired" class="text-center">
                            @if($item->model_type === 'product_variant')
                                @if(($item->freshness ?? '-') === 'Fresh')
                                <span class="inv-badge bg-emerald-50 text-emerald-600"><span class="dot bg-emerald-400"></span>Fresh</span>
                                @if($item->days_left !== null)<span class="block text-[10px] text-emerald-500 mt-0.5">{{ $item->days_left }}hr</span>@endif
                                @elseif(($item->freshness ?? '') === 'Hampir Expired')
                                <span class="inv-badge bg-orange-50 text-orange-600"><span class="dot bg-orange-400 animate-pulse"></span>{{ $item->days_left ?? '?' }}hr</span>
                                @elseif(($item->freshness ?? '') === 'Expired')
                                <span class="inv-badge bg-red-50 text-red-600"><span class="dot bg-red-400"></span>Expired</span>
                                @else
                                <span class="text-gray-200">&mdash;</span>
                                @endif
                            @else
                                @if(($item->almost_expired ?? 0) > 0)
                                <button @click="openExpired({{ $item->id }})" class="text-amber-600 font-semibold hover:text-amber-700 cursor-pointer text-[12px] tabular-nums">
                                    {{ number_format($item->almost_expired, $item->almost_expired == intval($item->almost_expired) ? 0 : 1) }}
                                    <span class="block text-[10px] text-amber-400 font-normal">&le;{{ $item->days_left }}hr</span>
                                </button>
                                @else
                                <span class="text-gray-200">&mdash;</span>
                                @endif
                            @endif
                        </td>
                        {{-- Value --}}
                        <td x-show="cols.value" class="text-right tabular-nums text-[12px] text-gray-600">
                            Rp{{ number_format($item->inventory_value,0,',','.') }}
                        </td>
                        {{-- Usage 30d --}}
                        <td x-show="cols.usage" class="text-right tabular-nums text-[12px]">
                            @if($item->usage_30d > 0)
                            <span class="text-[var(--inv-text)] font-medium">{{ number_format($item->usage_30d,0) }}</span>
                            <span class="text-[10px] text-[var(--inv-muted)]">{{ $item->unit_symbol }}</span>
                            @if($item->turnover_rate > 0)
                            <span class="block text-[10px] {{ $item->turnover_rate < 0.5 ? 'text-red-400' : ($item->turnover_rate > 2 ? 'text-emerald-500' : 'text-[var(--inv-muted)]') }}">
                                {{ $item->turnover_rate }}x
                            </span>
                            @endif
                            @else
                            <span class="text-gray-200">&mdash;</span>
                            @endif
                        </td>
                        {{-- Updated --}}
                        <td x-show="cols.updated" class="text-right text-[11px] text-[var(--inv-muted)]">
                            @if($item->updated_at)
                            {{ $item->updated_at->diffForHumans(null, true) }}
                            @else &mdash; @endif
                        </td>
                        {{-- Actions --}}
                        <td>
                            <div class="act-cell flex items-center justify-center gap-0.5">
                                @if($item->edit_url)
                                <a href="{{ $item->edit_url }}" class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-blue-500 hover:bg-blue-50 transition" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </a>
                                @endif
                                @if($item->batch_url)
                                <a href="{{ $item->batch_url }}" class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 transition" title="Tambah Batch">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </a>
                                @endif
                                @if($item->can_delete)
                                <button @click="deleteId={{ $item->id }}; deleteName='{{ addslashes($item->name) }}'; deleteType='{{ $item->model_type }}'; showDeleteModal=true"
                                        class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="py-20 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="text-[15px] text-gray-500 font-semibold">Belum ada data inventory</p>
                            <p class="text-[13px] text-[var(--inv-muted)] mt-1 mb-4">Mulai dengan menambahkan pembelian bahan baku.</p>
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
                        <td colspan="5" class="py-2.5 pl-5 text-[11px] font-semibold text-[var(--inv-muted)] uppercase tracking-wider">
                            Total halaman ini ({{ $pageTotals->items }} item)
                        </td>
                        <td class="py-2.5 text-right"></td>
                        <td class="py-2.5"></td>
                        <td class="py-2.5"></td>
                        <td class="py-2.5"></td>
                        <td x-show="cols.value" class="py-2.5 text-right tabular-nums text-[12px] font-bold text-[var(--inv-text)] pr-3.5">
                            Rp{{ number_format($pageTotals->value ?? 0, 0, ',', '.') }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- ── Mobile Cards ── --}}
        <div class="md:hidden">
            @forelse($inventoryItems as $item)
            @php
                $isOut = in_array($item->status, ['Out of Stock','Habis']);
                $isLow = $item->status === 'Low Stock';
                $accent = $isOut ? 'border-l-red-400' : ($isLow ? 'border-l-amber-400' : ($item->needs_reorder ? 'border-l-blue-400' : 'border-l-emerald-400'));
            @endphp
            <div class="mob-card border-l-[3px] {{ $accent }} {{ $isOut ? 'bg-red-50/30' : '' }}">
                <div class="flex items-start justify-between gap-2 mb-2.5">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="inv-badge {{ $item->model_type==='stock' ? 'bg-blue-50 text-blue-600' : 'bg-violet-50 text-violet-600' }} !text-[9px] !py-0 !px-1.5">{{ $item->model_type === 'stock' ? 'BB' : 'PJ' }}</span>
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

        {{-- ── Pagination ── --}}
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

    {{-- ═══════════════════════ H. R&D APPROVED ═══════════════════════ --}}
    @if($approvedProjects->isNotEmpty())
    <div class="mt-8">
        <div class="section-title"><div class="bar bg-blue-500"></div><h2>R&D Disetujui</h2></div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($approvedProjects as $project)
            <div class="inv-card p-5">
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

    {{-- ═══════════════════════ I. EXPIRED STORED ═══════════════════════ --}}
    @if($storedExpiredStocks->count() > 0)
    <div class="mt-8">
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

    {{-- ═══════════════════════ DELETE MODAL ═══════════════════════ --}}
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
                <button @click="showDeleteModal=false" class="flex-1 h-10 rounded-lg border border-[var(--inv-border)] text-[13px] font-medium text-gray-500 hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <form :action="'/manager/inventory/stock/' + deleteId" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full h-10 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[13px] font-semibold transition cursor-pointer">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ EXPIRED BATCH MODAL ═══════════════════════ --}}
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

</div>
@endsection
