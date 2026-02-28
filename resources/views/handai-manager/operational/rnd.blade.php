@extends('handai-manager.layouts.master')

@section('title', 'Riset & Pengembangan')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .rnd-table th { position: sticky; top: 0; z-index: 5; }
    .rnd-row { transition: background-color 0.15s ease; }
    .rnd-row:hover .rnd-actions { opacity: 1; }
    .rnd-actions { opacity: 0; transition: opacity 0.15s ease; }
    @media (max-width: 767px) { .rnd-actions { opacity: 1; } }
    .rnd-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .rnd-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .rnd-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .rnd-card-stat { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto"
     x-data="{
        showFilter: {{ request()->hasAny(['search','status','from','to']) ? 'true' : 'false' }},
        deleteId: null, showDeleteModal: false
     }">

    {{-- ── FLASH MESSAGES ── --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px]">
        @foreach($errors->all() as $err) <p>{{ $err }}</p> @endforeach
    </div>
    @endif

    {{-- ── HEADER ── --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Riset & Pengembangan</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">Riwayat dan monitoring proyek R&D</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="showFilter = !showFilter"
                    class="h-9 inline-flex items-center gap-1.5 px-3.5 text-[13px] font-medium border rounded-lg transition cursor-pointer"
                    :class="showFilter ? 'bg-gray-100 border-gray-300 text-gray-700' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-300 hover:text-gray-600'">
                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
                @if(request()->hasAny(['search','status','from','to']))
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                @endif
            </button>
            <a href="{{ route('manager.operational.rnd.create') }}"
               class="h-9 inline-flex items-center gap-1.5 px-4 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Buat R&D Baru
            </a>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="rnd-card-stat">
            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider leading-none">Total Proyek</p>
            <p class="text-xl font-bold text-gray-800 mt-1.5 leading-none tabular-nums">{{ number_format($rndStats->total ?? 0) }}</p>
        </div>
        <div class="rnd-card-stat">
            <p class="text-[11px] font-medium text-amber-500 uppercase tracking-wider leading-none">Pending</p>
            <p class="text-xl font-bold text-amber-600 mt-1.5 leading-none tabular-nums">{{ number_format($rndStats->pending ?? 0) }}</p>
        </div>
        <div class="rnd-card-stat">
            <p class="text-[11px] font-medium text-emerald-500 uppercase tracking-wider leading-none">Approved</p>
            <p class="text-xl font-bold text-emerald-600 mt-1.5 leading-none tabular-nums">{{ number_format($rndStats->approved ?? 0) }}</p>
        </div>
        <div class="rnd-card-stat">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider leading-none">Rejected</p>
            <p class="text-xl font-bold text-red-500 mt-1.5 leading-none tabular-nums">{{ number_format($rndStats->rejected ?? 0) }}</p>
        </div>
    </div>

    {{-- ── FILTER ── --}}
    <div x-show="showFilter" x-collapse x-cloak class="mb-5">
        <form method="GET" action="{{ route('manager.operational.rnd') }}"
              class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari project, deskripsi..."
                               class="rnd-input !pl-9" />
                    </div>
                </div>
                <div class="min-w-[130px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="rnd-input appearance-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option>
                        <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
                        <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>Rejected</option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="rnd-input" />
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="rnd-input" />
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Terapkan</button>
                    <a href="{{ route('manager.operational.rnd') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── MAIN TABLE ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info bar --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[12px] text-gray-400">
                <span class="font-medium text-gray-500">{{ $rndHistories->firstItem() ?? 0 }}–{{ $rndHistories->lastItem() ?? 0 }}</span> dari {{ $rndHistories->total() }} proyek
            </p>
            @if(request()->hasAny(['search','status','from','to']))
            <a href="{{ route('manager.operational.rnd') }}" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Hapus filter
            </a>
            @endif
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-[13px] rnd-table">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="text-left py-2.5 px-5 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Nama Project</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">PIC</th>
                        <th class="text-left py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell">Bahan</th>
                        <th class="text-right py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Total Biaya</th>
                        <th class="text-center py-2.5 px-4 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-center py-2.5 px-3 text-[10.5px] font-semibold text-gray-400 uppercase tracking-wider w-[60px]"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rndHistories as $idx => $project)
                    @php
                        $stripe = $idx % 2 === 0 ? '' : 'bg-gray-50/40';
                    @endphp
                    <tr class="rnd-row {{ $stripe }} border-b border-gray-50 last:border-b-0">
                        {{-- Date --}}
                        <td class="py-3 px-5 tabular-nums text-gray-500">{{ \Carbon\Carbon::parse($project->rnd_date)->format('d M Y') }}</td>
                        {{-- Name --}}
                        <td class="py-3 px-4">
                            <p class="font-medium text-gray-800 leading-snug">{{ $project->rnd_name }}</p>
                            @if($project->Deskripsi)
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-snug line-clamp-1">{{ $project->Deskripsi }}</p>
                            @endif
                        </td>
                        {{-- PIC --}}
                        <td class="py-3 px-4 text-gray-500 hidden lg:table-cell">{{ $project->pic->name ?? '—' }}</td>
                        {{-- Materials --}}
                        <td class="py-3 px-4 hidden xl:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($project->stockUsages->take(3) as $usage)
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px]">
                                    {{ $usage->stock->name ?? $usage->stock_name ?? $usage->manual_name ?? '-' }}
                                </span>
                                @endforeach
                                @if($project->stockUsages->count() > 3)
                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-400 rounded text-[10px]">+{{ $project->stockUsages->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        {{-- Total Cost --}}
                        <td class="py-3 px-4 text-right tabular-nums font-semibold text-gray-700">Rp{{ number_format($project->stockUsages->sum('cost'), 0, ',', '.') }}</td>
                        {{-- Status --}}
                        <td class="py-3 px-4 text-center">
                            @if($project->status === 'approved')
                            <span class="rnd-badge bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Approved</span>
                            @elseif($project->status === 'rejected')
                            <span class="rnd-badge bg-red-50 text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Rejected</span>
                            @else
                            <span class="rnd-badge bg-amber-50 text-amber-600"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Pending</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="py-3 px-3">
                            <div class="rnd-actions flex items-center justify-center">
                                <button @click="deleteId = {{ $project->id }}; showDeleteModal = true"
                                        class="w-7 h-7 rounded-md inline-flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer" title="Hapus">
                                    <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <p class="text-sm text-gray-400 font-medium">Belum ada proyek R&D</p>
                            <p class="text-xs text-gray-300 mt-0.5">Buat proyek R&D pertama Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-50">
            @forelse($rndHistories as $project)
            @php
                $statusColor = $project->status === 'approved' ? 'border-l-emerald-400' : ($project->status === 'rejected' ? 'border-l-red-400' : 'border-l-amber-400');
            @endphp
            <div class="p-4 border-l-[3px] {{ $statusColor }}">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-800 text-[13px] leading-snug truncate">{{ $project->rnd_name }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $project->pic->name ?? '—' }} &bull; {{ \Carbon\Carbon::parse($project->rnd_date)->format('d M Y') }}</p>
                    </div>
                    @if($project->status === 'approved')
                    <span class="rnd-badge bg-emerald-50 text-emerald-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Approved</span>
                    @elseif($project->status === 'rejected')
                    <span class="rnd-badge bg-red-50 text-red-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Rejected</span>
                    @else
                    <span class="rnd-badge bg-amber-50 text-amber-600 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Pending</span>
                    @endif
                </div>
                @if($project->Deskripsi)
                <p class="text-[12px] text-gray-400 line-clamp-2 mb-2">{{ $project->Deskripsi }}</p>
                @endif
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-[12px]">
                    <div><span class="text-gray-400">Bahan:</span> <span class="text-gray-600">{{ $project->stockUsages->count() }} item</span></div>
                    <div><span class="text-gray-400">Biaya:</span> <span class="font-semibold text-gray-700">Rp{{ number_format($project->stockUsages->sum('cost'), 0, ',', '.') }}</span></div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-3 pt-2.5 border-t border-gray-100">
                    <button @click="deleteId = {{ $project->id }}; showDeleteModal = true"
                            class="text-[11px] text-red-400 hover:text-red-600 font-medium inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </div>
            </div>
            @empty
            <div class="py-16 text-center">
                <p class="text-sm text-gray-400">Belum ada proyek R&D.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($rndHistories->hasPages())
        <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
            <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $rndHistories->currentPage() }} / {{ $rndHistories->lastPage() }}</p>
            <div class="flex items-center gap-1 mx-auto sm:mx-0">
                @if($rndHistories->onFirstPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </span>
                @else
                <a href="{{ $rndHistories->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @endif
                @foreach($rndHistories->getUrlRange(max(1, $rndHistories->currentPage()-2), min($rndHistories->lastPage(), $rndHistories->currentPage()+2)) as $page => $url)
                    @if($page == $rndHistories->currentPage())
                    <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                    @endif
                @endforeach
                @if($rndHistories->hasMorePages())
                <a href="{{ $rndHistories->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
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

    {{-- ── READY PROJECTS SECTION ── --}}
    @if($readyProjects->isNotEmpty())
    <div class="mt-8">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
            <h2 class="text-[15px] font-bold text-gray-800">Proyek R&D Siap Diproses</h2>
            <span class="rnd-badge bg-emerald-50 text-emerald-600">{{ $readyProjects->count() }}</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($readyProjects as $project)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-[14px] font-bold text-gray-800">{{ $project->rnd_name }}</h3>
                        @if($project->deskripsi)
                        <p class="text-[12px] text-gray-400 mt-0.5">{{ $project->deskripsi }}</p>
                        @endif
                    </div>
                    <span class="rnd-badge bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Ready</span>
                </div>
                <div class="overflow-x-auto mb-4 -mx-1">
                    <table class="w-full text-[12px]">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Bahan</th>
                                <th class="text-right py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Jumlah</th>
                                <th class="text-left py-1.5 px-1 text-[10px] font-semibold text-gray-400 uppercase">Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($project->stockUsages as $usage)
                            <tr class="border-b border-gray-50">
                                <td class="py-1.5 px-1 text-gray-700">{{ $usage->stock->name ?? $usage->manual_name }}</td>
                                <td class="py-1.5 px-1 text-right tabular-nums text-gray-600">{{ $usage->quantity_used }}</td>
                                <td class="py-1.5 px-1 text-gray-500">{{ $usage->unit->symbol ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <form method="POST" action="{{ route('manager.rnd.finish', $project->id) }}">
                    @csrf
                    <button type="submit"
                            class="h-8 inline-flex items-center gap-1.5 px-4 text-[12px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Tandai Selesai
                    </button>
                </form>
            </div>
            @endforeach
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
            <h3 class="text-[15px] font-bold text-gray-800 mb-1">Hapus Proyek R&D?</h3>
            <p class="text-[13px] text-gray-400 mb-5 leading-relaxed">Data proyek dan semua bahan<br>akan dihapus permanen.</p>
            <div class="flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 h-10 rounded-lg border border-gray-200 text-[13px] font-medium text-gray-500 hover:bg-gray-50 transition cursor-pointer">Batal</button>
                <form :action="'/manager/operational/rnd/' + deleteId" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full h-10 rounded-lg bg-red-500 hover:bg-red-600 text-white text-[13px] font-semibold transition cursor-pointer">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection