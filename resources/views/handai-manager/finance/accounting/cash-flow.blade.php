@extends('handai-manager.layouts.master')

@section('title', 'Arus Kas (Cash Flow) — Handai Finance')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Arus Kas</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name ?? 'Semua Store' }}</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="mt-3 sm:mt-0 text-sm text-green-600 hover:underline">&larr; Dashboard</a>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.cashflow') }}" class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="input input-bordered input-sm text-sm bg-white">
            </div>
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="input input-bordered input-sm text-sm bg-white">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </p>
    </form>

    {{-- Opening Cash --}}
    <div class="bg-white rounded-xl shadow-sm border px-6 py-4 mb-4 flex items-center justify-between">
        <p class="text-sm font-medium text-gray-600"><i class="ti ti-wallet mr-1"></i> Saldo Awal Kas</p>
        <p class="font-mono font-bold text-lg text-gray-800">Rp{{ number_format($openingCash, 0, ',', '.') }}</p>
    </div>

    {{-- OPERATING ACTIVITIES --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-4">
        <div class="px-6 py-4 bg-green-50 border-b flex items-center justify-between">
            <h3 class="text-sm font-semibold text-green-700 uppercase tracking-wide">
                <i class="ti ti-activity mr-1"></i> Aktivitas Operasi
            </h3>
            <span class="font-mono font-bold text-green-700 text-lg">Rp{{ number_format($netOperating, 0, ',', '.') }}</span>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-2 divide-x border-b text-center">
            <div class="py-3">
                <p class="text-lg font-bold text-green-600">Rp{{ number_format($operatingIn, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">Kas Masuk</p>
            </div>
            <div class="py-3">
                <p class="text-lg font-bold text-red-500">Rp{{ number_format($operatingOut, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">Kas Keluar</p>
            </div>
        </div>

        {{-- Detail --}}
        @if (count($operatingDetails) > 0)
            <div x-data="{ showOp: false }">
                <button @click="showOp = !showOp" class="w-full px-6 py-2 text-xs text-green-600 hover:bg-green-50 text-left cursor-pointer">
                    <i class="ti ti-chevron-down transition-transform" :class="showOp ? 'rotate-180' : ''"></i>
                    Lihat Detail ({{ count($operatingDetails) }} transaksi)
                </button>
                <div x-show="showOp" x-transition x-cloak>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-5 py-2 text-left">Tanggal</th>
                                <th class="px-5 py-2 text-left">Deskripsi</th>
                                <th class="px-5 py-2 text-right">Masuk</th>
                                <th class="px-5 py-2 text-right">Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($operatingDetails as $d)
                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                    <td class="px-5 py-2 text-gray-500 text-xs">{{ $d['date'] }}</td>
                                    <td class="px-5 py-2 text-gray-700 truncate max-w-xs">{{ $d['description'] }}</td>
                                    <td class="px-5 py-2 text-right font-mono {{ $d['in'] > 0 ? 'text-green-600' : 'text-gray-300' }}">
                                        {{ $d['in'] > 0 ? 'Rp' . number_format($d['in'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-2 text-right font-mono {{ $d['out'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                                        {{ $d['out'] > 0 ? 'Rp' . number_format($d['out'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- INVESTING ACTIVITIES --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-4">
        <div class="px-6 py-4 bg-blue-50 border-b flex items-center justify-between">
            <h3 class="text-sm font-semibold text-blue-700 uppercase tracking-wide">
                <i class="ti ti-building-store mr-1"></i> Aktivitas Investasi
            </h3>
            <span class="font-mono font-bold text-blue-700 text-lg">Rp{{ number_format($netInvesting, 0, ',', '.') }}</span>
        </div>
        <div class="grid grid-cols-2 divide-x text-center py-3">
            <div>
                <p class="text-lg font-bold text-green-600">Rp{{ number_format($investingIn, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">Kas Masuk</p>
            </div>
            <div>
                <p class="text-lg font-bold text-red-500">Rp{{ number_format($investingOut, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">Kas Keluar</p>
            </div>
        </div>
        @if ($investingIn == 0 && $investingOut == 0)
            <div class="px-6 py-3 text-center text-gray-400 text-xs border-t">Tidak ada aktivitas investasi di periode ini.</div>
        @endif
    </div>

    {{-- FINANCING ACTIVITIES --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden mb-4">
        <div class="px-6 py-4 bg-purple-50 border-b flex items-center justify-between">
            <h3 class="text-sm font-semibold text-purple-700 uppercase tracking-wide">
                <i class="ti ti-cash mr-1"></i> Aktivitas Pendanaan
            </h3>
            <span class="font-mono font-bold text-purple-700 text-lg">Rp{{ number_format($netFinancing, 0, ',', '.') }}</span>
        </div>
        <div class="grid grid-cols-2 divide-x text-center py-3">
            <div>
                <p class="text-lg font-bold text-green-600">Rp{{ number_format($financingIn, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">Kas Masuk</p>
            </div>
            <div>
                <p class="text-lg font-bold text-red-500">Rp{{ number_format($financingOut, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">Kas Keluar</p>
            </div>
        </div>

        @if (count($financingDetails) > 0)
            <div x-data="{ showFin: false }">
                <button @click="showFin = !showFin" class="w-full px-6 py-2 text-xs text-purple-600 hover:bg-purple-50 text-left cursor-pointer">
                    <i class="ti ti-chevron-down transition-transform" :class="showFin ? 'rotate-180' : ''"></i>
                    Lihat Detail ({{ count($financingDetails) }} transaksi)
                </button>
                <div x-show="showFin" x-transition x-cloak>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-5 py-2 text-left">Tanggal</th>
                                <th class="px-5 py-2 text-left">Deskripsi</th>
                                <th class="px-5 py-2 text-right">Masuk</th>
                                <th class="px-5 py-2 text-right">Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($financingDetails as $d)
                                <tr class="border-b border-gray-50 hover:bg-gray-50">
                                    <td class="px-5 py-2 text-gray-500 text-xs">{{ $d['date'] }}</td>
                                    <td class="px-5 py-2 text-gray-700 truncate max-w-xs">{{ $d['description'] }}</td>
                                    <td class="px-5 py-2 text-right font-mono {{ $d['in'] > 0 ? 'text-green-600' : 'text-gray-300' }}">
                                        {{ $d['in'] > 0 ? 'Rp' . number_format($d['in'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-2 text-right font-mono {{ $d['out'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                                        {{ $d['out'] > 0 ? 'Rp' . number_format($d['out'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($financingIn == 0 && $financingOut == 0)
            <div class="px-6 py-3 text-center text-gray-400 text-xs border-t">Tidak ada aktivitas pendanaan di periode ini.</div>
        @endif
    </div>

    {{-- NET CASH CHANGE + CLOSING --}}
    <div class="space-y-3">
        <div class="bg-white rounded-xl shadow-sm border px-6 py-4 flex items-center justify-between">
            <p class="text-sm font-medium text-gray-600"><i class="ti ti-arrows-exchange mr-1"></i> Perubahan Kas Bersih</p>
            <p class="font-mono font-bold text-lg {{ $netCashChange >= 0 ? 'text-green-700' : 'text-red-600' }}">
                Rp{{ number_format($netCashChange, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-emerald-50 rounded-xl border border-emerald-200 px-6 py-5 flex items-center justify-between">
            <p class="font-semibold text-emerald-800 text-lg"><i class="ti ti-wallet mr-1"></i> Saldo Akhir Kas</p>
            <p class="font-mono font-bold text-2xl text-emerald-700">Rp{{ number_format($closingCash, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
            <p class="text-xs text-gray-400 uppercase mb-1">Operasi</p>
            <p class="text-xl font-bold {{ $netOperating >= 0 ? 'text-green-700' : 'text-red-600' }}">
                Rp{{ number_format($netOperating, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
            <p class="text-xs text-gray-400 uppercase mb-1">Investasi</p>
            <p class="text-xl font-bold {{ $netInvesting >= 0 ? 'text-green-700' : 'text-red-600' }}">
                Rp{{ number_format($netInvesting, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
            <p class="text-xs text-gray-400 uppercase mb-1">Pendanaan</p>
            <p class="text-xl font-bold {{ $netFinancing >= 0 ? 'text-green-700' : 'text-red-600' }}">
                Rp{{ number_format($netFinancing, 0, ',', '.') }}
            </p>
        </div>
    </div>
</div>
@endsection
