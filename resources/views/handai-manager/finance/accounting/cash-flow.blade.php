@extends('handai-manager.layouts.master')

@section('title', 'Arus Kas (Cash Flow) — Handai Finance')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .cf-input { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; }
    .cf-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background: #fff; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.finance.accounting.dashboard') }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-gray-800">Laporan Arus Kas</h1>
                <p class="text-[12px] text-gray-400 mt-0.5">{{ $store->name ?? 'Semua Store' }}</p>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.cashflow') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="cf-input w-40">
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="cf-input w-40">
            </div>
            <button type="submit" class="h-9 px-5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Tampilkan</button>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </p>
    </form>

    {{-- Detail Truncation Warning --}}
    @if (!empty($detailTruncated))
    <div class="bg-amber-50/60 border border-amber-200 rounded-lg px-4 py-2.5 mb-4 flex items-center gap-2 text-[12px] text-amber-700">
        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Detail transaksi ditampilkan maksimal 200 baris. Nilai ringkasan (total kas masuk/keluar) tetap memperhitungkan seluruh transaksi.
    </div>
    @endif

    {{-- Opening Cash --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-3.5 mb-4 flex items-center justify-between">
        <p class="text-[13px] font-medium text-gray-600 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 013 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 013 6v3"/></svg>
            Saldo Awal Kas
        </p>
        <p class="font-mono font-bold text-base text-gray-800">Rp{{ number_format($openingCash, 0, ',', '.') }}</p>
    </div>

    {{-- OPERATING ACTIVITIES --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-5 py-3.5 bg-emerald-50/60 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[12px] font-semibold text-emerald-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                Aktivitas Operasi
            </h3>
            <span class="font-mono font-bold text-emerald-700 text-base">Rp{{ number_format($netOperating, 0, ',', '.') }}</span>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-2 divide-x divide-gray-100 border-b border-gray-100 text-center">
            <div class="py-3">
                <p class="text-base font-bold text-emerald-600">Rp{{ number_format($operatingIn, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400">Kas Masuk</p>
            </div>
            <div class="py-3">
                <p class="text-base font-bold text-red-500">Rp{{ number_format($operatingOut, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400">Kas Keluar</p>
            </div>
        </div>

        {{-- Detail --}}
        @if (count($operatingDetails) > 0)
            <div x-data="{ showOp: false }">
                <button @click="showOp = !showOp" class="w-full px-5 py-2 text-[12px] text-emerald-600 hover:bg-emerald-50/40 text-left cursor-pointer flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="showOp ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    Lihat Detail ({{ count($operatingDetails) }} transaksi)
                </button>
                <div x-show="showOp" x-transition x-cloak>
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/80 text-[11px] text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-2 text-left font-medium">Tanggal</th>
                                <th class="px-5 py-2 text-left font-medium">Deskripsi</th>
                                <th class="px-5 py-2 text-right font-medium">Masuk</th>
                                <th class="px-5 py-2 text-right font-medium">Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operatingDetails as $d)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                    <td class="px-5 py-2 text-gray-500 text-[12px] whitespace-nowrap">{{ $d['date'] }}</td>
                                    <td class="px-5 py-2 text-gray-700 text-[13px] truncate max-w-xs">{{ $d['description'] }}</td>
                                    <td class="px-5 py-2 text-right font-mono text-[13px] whitespace-nowrap {{ $d['in'] > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                                        {{ $d['in'] > 0 ? 'Rp' . number_format($d['in'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-2 text-right font-mono text-[13px] whitespace-nowrap {{ $d['out'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                                        {{ $d['out'] > 0 ? 'Rp' . number_format($d['out'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- INVESTING ACTIVITIES --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-5 py-3.5 bg-blue-50/60 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[12px] font-semibold text-blue-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.15c0 .415.336.75.75.75z"/></svg>
                Aktivitas Investasi
            </h3>
            <span class="font-mono font-bold text-blue-700 text-base">Rp{{ number_format($netInvesting, 0, ',', '.') }}</span>
        </div>
        <div class="grid grid-cols-2 divide-x divide-gray-100 text-center py-3">
            <div>
                <p class="text-base font-bold text-emerald-600">Rp{{ number_format($investingIn, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400">Kas Masuk</p>
            </div>
            <div>
                <p class="text-base font-bold text-red-500">Rp{{ number_format($investingOut, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400">Kas Keluar</p>
            </div>
        </div>
        @if ($investingIn == 0 && $investingOut == 0)
            <div class="px-5 py-3 text-center text-gray-400 text-[12px] border-t border-gray-100">Tidak ada aktivitas investasi di periode ini.</div>
        @endif
    </div>

    {{-- FINANCING ACTIVITIES --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-5 py-3.5 bg-purple-50/60 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-[12px] font-semibold text-purple-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                Aktivitas Pendanaan
            </h3>
            <span class="font-mono font-bold text-purple-700 text-base">Rp{{ number_format($netFinancing, 0, ',', '.') }}</span>
        </div>
        <div class="grid grid-cols-2 divide-x divide-gray-100 text-center py-3">
            <div>
                <p class="text-base font-bold text-emerald-600">Rp{{ number_format($financingIn, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400">Kas Masuk</p>
            </div>
            <div>
                <p class="text-base font-bold text-red-500">Rp{{ number_format($financingOut, 0, ',', '.') }}</p>
                <p class="text-[11px] text-gray-400">Kas Keluar</p>
            </div>
        </div>

        @if (count($financingDetails) > 0)
            <div x-data="{ showFin: false }">
                <button @click="showFin = !showFin" class="w-full px-5 py-2 text-[12px] text-purple-600 hover:bg-purple-50/40 text-left cursor-pointer flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="showFin ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    Lihat Detail ({{ count($financingDetails) }} transaksi)
                </button>
                <div x-show="showFin" x-transition x-cloak>
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/80 text-[11px] text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-2 text-left font-medium">Tanggal</th>
                                <th class="px-5 py-2 text-left font-medium">Deskripsi</th>
                                <th class="px-5 py-2 text-right font-medium">Masuk</th>
                                <th class="px-5 py-2 text-right font-medium">Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($financingDetails as $d)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                    <td class="px-5 py-2 text-gray-500 text-[12px] whitespace-nowrap">{{ $d['date'] }}</td>
                                    <td class="px-5 py-2 text-gray-700 text-[13px] truncate max-w-xs">{{ $d['description'] }}</td>
                                    <td class="px-5 py-2 text-right font-mono text-[13px] whitespace-nowrap {{ $d['in'] > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                                        {{ $d['in'] > 0 ? 'Rp' . number_format($d['in'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-2 text-right font-mono text-[13px] whitespace-nowrap {{ $d['out'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                                        {{ $d['out'] > 0 ? 'Rp' . number_format($d['out'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @elseif ($financingIn == 0 && $financingOut == 0)
            <div class="px-5 py-3 text-center text-gray-400 text-[12px] border-t border-gray-100">Tidak ada aktivitas pendanaan di periode ini.</div>
        @endif
    </div>

    {{-- NET CASH CHANGE + CLOSING --}}
    <div class="space-y-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-3.5 flex items-center justify-between">
            <p class="text-[13px] font-medium text-gray-600 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                Perubahan Kas Bersih
            </p>
            <p class="font-mono font-bold text-base {{ $netCashChange >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp{{ number_format($netCashChange, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-emerald-50/60 rounded-xl border border-emerald-200 px-5 py-4 flex items-center justify-between">
            <p class="font-semibold text-emerald-800 text-base flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 013 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 013 6v3"/></svg>
                Saldo Akhir Kas
            </p>
            <p class="font-mono font-bold text-2xl text-emerald-700">Rp{{ number_format($closingCash, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider mb-1">Operasi</p>
            <p class="text-lg font-bold {{ $netOperating >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp{{ number_format($netOperating, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider mb-1">Investasi</p>
            <p class="text-lg font-bold {{ $netInvesting >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp{{ number_format($netInvesting, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider mb-1">Pendanaan</p>
            <p class="text-lg font-bold {{ $netFinancing >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp{{ number_format($netFinancing, 0, ',', '.') }}
            </p>
        </div>
    </div>
</div>
@endsection
