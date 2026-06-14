@extends('layouts.master')

@section('title', 'Jurnal Entries â€” Handai Finance')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .je-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .je-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Jurnal Entries</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">{{ $store->name ?? 'Semua Store' }}</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="text-[13px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Dashboard
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.journals') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col min-w-[130px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sumber</label>
                <select name="source" class="je-input appearance-none cursor-pointer">
                    <option value="">Semua</option>
                    @foreach ($sources as $src)
                        <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="je-input">
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="je-input">
            </div>
            <div class="flex flex-col min-w-[180px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Deskripsi..." class="je-input !pl-9">
                </div>
            </div>
            <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Terapkan</button>
            <a href="{{ route('manager.finance.accounting.journals') }}" class="h-9 px-3 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Reset</a>
        </div>
    </form>

    {{-- Journals --}}
    <div class="space-y-3" x-data="{ openJournal: null }">
        @forelse ($journals as $journal)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <button type="button"
                    @click="openJournal = openJournal === {{ $journal->id }} ? null : {{ $journal->id }}"
                    class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50/60 transition text-left cursor-pointer">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <span class="font-mono text-[11px] text-gray-400 shrink-0">{{ $journal->journal_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0
                            @switch($journal->source)
                                @case('POS') bg-green-50 text-green-700 @break
                                @case('KASIR') bg-blue-50 text-blue-700 @break
                                @case('PURCHASE') bg-amber-50 text-amber-700 @break
                                @case('PRODUCTION') bg-purple-50 text-purple-700 @break
                                @case('CANCEL') bg-red-50 text-red-700 @break
                                @case('EXPIRED') bg-rose-50 text-rose-700 @break
                                @case('ADJUSTMENT') bg-gray-100 text-gray-600 @break
                                @default bg-gray-50 text-gray-500
                            @endswitch">
                            {{ $journal->source }}
                        </span>
                        <span class="text-[13px] text-gray-700 truncate">{{ $journal->description }}</span>
                    </div>
                    <div class="flex items-center gap-4 shrink-0 ml-4">
                        <span class="text-[11px] text-gray-400 tabular-nums hidden sm:block">{{ $journal->journal_date->format('d/m/Y') }}</span>
                        <span class="font-mono text-[13px] font-semibold text-gray-700 tabular-nums">Rp{{ number_format($journal->total_debit, 0, ',', '.') }}</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openJournal === {{ $journal->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                <div x-show="openJournal === {{ $journal->id }}" x-transition x-cloak class="border-t border-gray-100 bg-gray-50/50">
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-[13px]">
                        <thead class="text-[10.5px] text-gray-400 uppercase tracking-wider bg-gray-50">
                            <tr>
                                <th class="px-5 py-2 text-left">Akun</th>
                                <th class="px-5 py-2 text-left">Kode</th>
                                <th class="px-5 py-2 text-left hidden sm:table-cell">Memo</th>
                                <th class="px-5 py-2 text-right">Debit</th>
                                <th class="px-5 py-2 text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($journal->entries as $entry)
                                <tr class="border-b border-gray-100/80">
                                    <td class="px-5 py-2 text-gray-700">{{ $entry->account->name ?? '-' }}</td>
                                    <td class="px-5 py-2 font-mono text-[11px] text-gray-400">{{ $entry->account->code ?? '-' }}</td>
                                    <td class="px-5 py-2 text-gray-400 text-[11px] hidden sm:table-cell">{{ $entry->memo ?? '-' }}</td>
                                    <td class="px-5 py-2 text-right font-mono {{ $entry->debit > 0 ? 'text-emerald-700 font-semibold' : 'text-gray-300' }}">
                                        {{ $entry->debit > 0 ? 'Rp' . number_format($entry->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-2 text-right font-mono {{ $entry->credit > 0 ? 'text-red-600 font-semibold' : 'text-gray-300' }}">
                                        {{ $entry->credit > 0 ? 'Rp' . number_format($entry->credit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold text-[11px]">
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-gray-500 hidden sm:table-cell">TOTAL</td>
                                <td colspan="1" class="px-5 py-2 text-right text-gray-500 sm:hidden">TOTAL</td>
                                <td class="px-5 py-2 text-right font-mono text-emerald-700">Rp{{ number_format($journal->total_debit, 0, ',', '.') }}</td>
                                <td class="px-5 py-2 text-right font-mono text-red-600">Rp{{ number_format($journal->total_credit, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-[13px] text-gray-400 font-medium">Belum ada jurnal yang dicatat.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($journals->hasPages())
    <div class="mt-5 flex items-center justify-between">
        <p class="text-[11px] text-gray-400 hidden sm:block">Hal. {{ $journals->currentPage() }} / {{ $journals->lastPage() }}</p>
        <div class="flex items-center gap-1 mx-auto sm:mx-0">
            @if($journals->onFirstPage())
            <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
            @else
            <a href="{{ $journals->appends(request()->query())->previousPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
            @endif
            @foreach($journals->appends(request()->query())->getUrlRange(max(1, $journals->currentPage()-2), min($journals->lastPage(), $journals->currentPage()+2)) as $page => $url)
                @if($page == $journals->currentPage())
                <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-semibold bg-emerald-600 text-white">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-[12px] font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
                @endif
            @endforeach
            @if($journals->hasMorePages())
            <a href="{{ $journals->appends(request()->query())->nextPageUrl() }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
            @else
            <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

