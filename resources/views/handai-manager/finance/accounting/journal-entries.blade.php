@extends('handai-manager.layouts.master')

@section('title', 'Jurnal Entries — Handai Finance')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jurnal Entries</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name ?? 'Semua Store' }}</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="mt-3 sm:mt-0 text-sm text-green-600 hover:underline">&larr; Dashboard</a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.journals') }}" class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Sumber</label>
                <select name="source" class="input input-bordered input-sm text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($sources as $src)
                        <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="input input-bordered input-sm text-sm bg-white">
            </div>
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="input input-bordered input-sm text-sm bg-white">
            </div>
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Deskripsi..." class="input input-bordered input-sm text-sm bg-white w-48">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('manager.finance.accounting.journals') }}" class="btn btn-sm btn-ghost text-gray-500">Reset</a>
        </div>
    </form>

    {{-- Journals --}}
    <div class="space-y-4" x-data="{ openJournal: null }">
        @forelse ($journals as $journal)
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                {{-- Header Row (clickable) --}}
                <button type="button"
                    @click="openJournal = openJournal === {{ $journal->id }} ? null : {{ $journal->id }}"
                    class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition text-left cursor-pointer">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <span class="font-mono text-xs text-gray-500 shrink-0">{{ $journal->journal_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0
                            @switch($journal->source)
                                @case('POS') bg-green-100 text-green-700 @break
                                @case('KASIR') bg-blue-100 text-blue-700 @break
                                @case('PURCHASE') bg-amber-100 text-amber-700 @break
                                @case('PRODUCTION') bg-purple-100 text-purple-700 @break
                                @case('CANCEL') bg-red-100 text-red-700 @break
                                @case('EXPIRED') bg-rose-100 text-rose-700 @break
                                @case('ADJUSTMENT') bg-gray-200 text-gray-700 @break
                                @default bg-gray-100 text-gray-600
                            @endswitch">
                            {{ $journal->source }}
                        </span>
                        <span class="text-sm text-gray-700 truncate">{{ $journal->description }}</span>
                    </div>
                    <div class="flex items-center gap-4 shrink-0 ml-4">
                        <span class="text-xs text-gray-500">{{ $journal->journal_date->format('d/m/Y') }}</span>
                        <span class="font-mono text-sm font-semibold text-gray-700">Rp{{ number_format($journal->total_debit, 0, ',', '.') }}</span>
                        <i class="ti ti-chevron-down text-gray-400 transition-transform" :class="openJournal === {{ $journal->id }} ? 'rotate-180' : ''"></i>
                    </div>
                </button>

                {{-- Entries Detail (collapsible) --}}
                <div x-show="openJournal === {{ $journal->id }}" x-transition x-cloak class="border-t bg-gray-50">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-100">
                            <tr>
                                <th class="px-5 py-2 text-left">Akun</th>
                                <th class="px-5 py-2 text-left">Kode</th>
                                <th class="px-5 py-2 text-left">Memo</th>
                                <th class="px-5 py-2 text-right">Debit</th>
                                <th class="px-5 py-2 text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($journal->entries as $entry)
                                <tr class="border-b border-gray-100">
                                    <td class="px-5 py-2 text-gray-700">{{ $entry->account->name ?? '-' }}</td>
                                    <td class="px-5 py-2 font-mono text-xs text-gray-500">{{ $entry->account->code ?? '-' }}</td>
                                    <td class="px-5 py-2 text-gray-500 text-xs">{{ $entry->memo ?? '-' }}</td>
                                    <td class="px-5 py-2 text-right font-mono {{ $entry->debit > 0 ? 'text-green-700 font-semibold' : 'text-gray-300' }}">
                                        {{ $entry->debit > 0 ? 'Rp' . number_format($entry->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-2 text-right font-mono {{ $entry->credit > 0 ? 'text-red-600 font-semibold' : 'text-gray-300' }}">
                                        {{ $entry->credit > 0 ? 'Rp' . number_format($entry->credit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-semibold text-xs">
                            <tr>
                                <td colspan="3" class="px-5 py-2 text-right text-gray-600">TOTAL</td>
                                <td class="px-5 py-2 text-right font-mono text-green-700">Rp{{ number_format($journal->total_debit, 0, ',', '.') }}</td>
                                <td class="px-5 py-2 text-right font-mono text-red-600">Rp{{ number_format($journal->total_credit, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border p-10 text-center">
                <i class="ti ti-notebook-off text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Belum ada jurnal yang dicatat.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($journals->hasPages())
        <div class="mt-6 flex justify-end">
            {{ $journals->appends(request()->query())->links('vendor.pagination.custom-tailwind') }}
        </div>
    @endif
</div>
@endsection
