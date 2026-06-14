@extends('layouts.master')

@section('title', 'Laporan Laba Rugi â€” Handai Finance')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .is-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .is-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Laporan Laba Rugi</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">{{ $store->name ?? 'Semua Store' }}</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="text-[13px] text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Dashboard
        </a>
    </div>

    {{-- Period Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.income') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col min-w-[120px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Periode</label>
                <select name="period" onchange="this.form.submit()" class="is-input appearance-none cursor-pointer">
                    <option value="daily"   {{ $period === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="monthly"  {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly"   {{ $period === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    <option value="custom"   {{ $period === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="is-input">
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="is-input">
            </div>
            <button type="submit" class="h-9 px-4 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition cursor-pointer">Tampilkan</button>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">
            Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} â€” {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
        </p>
    </form>

    {{-- Income Statement --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">

        {{-- REVENUE --}}
        <div class="border-b border-gray-100">
            <div class="px-6 py-3 bg-emerald-50/60 flex items-center justify-between">
                <h3 class="text-[12px] font-semibold text-emerald-700 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Pendapatan (Revenue)
                </h3>
            </div>
            <table class="min-w-full text-sm">
                <tbody>
                    @forelse ($revenueBreakdown as $item)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-gray-700">
                                <span class="font-mono text-xs text-gray-400 mr-2">{{ $item['code'] }}</span>
                                {{ $item['name'] }}
                            </td>
                            <td class="px-6 py-2.5 text-right font-mono text-gray-700">Rp{{ number_format($item['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-6 py-3 text-center text-gray-400 text-xs">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50/60">
                        <td class="px-6 py-3 font-semibold text-emerald-700">Total Pendapatan</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-emerald-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- COGS --}}
        <div class="border-b border-gray-100">
            <div class="px-6 py-3 bg-amber-50/60 flex items-center justify-between">
                <h3 class="text-[12px] font-semibold text-amber-700 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Harga Pokok Penjualan (COGS)
                </h3>
            </div>
            <table class="min-w-full text-sm">
                <tbody>
                    @forelse ($cogsBreakdown as $item)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-gray-700">
                                <span class="font-mono text-xs text-gray-400 mr-2">{{ $item['code'] }}</span>
                                {{ $item['name'] }}
                            </td>
                            <td class="px-6 py-2.5 text-right font-mono text-gray-700">Rp{{ number_format($item['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-6 py-3 text-center text-gray-400 text-xs">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-amber-50/60">
                        <td class="px-6 py-3 font-semibold text-amber-700">Total HPP</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-amber-700">Rp{{ number_format($totalCogs, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- GROSS PROFIT --}}
        <div class="px-6 py-4 bg-blue-50/60 flex items-center justify-between border-b border-gray-100">
            <p class="font-semibold text-blue-800 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                Laba Kotor (Gross Profit)
            </p>
            <p class="font-mono font-bold text-lg {{ $grossProfit >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                Rp{{ number_format($grossProfit, 0, ',', '.') }}
            </p>
        </div>

        {{-- EXPENSES --}}
        <div class="border-b border-gray-100">
            <div class="px-6 py-3 bg-rose-50/60 flex items-center justify-between">
                <h3 class="text-[12px] font-semibold text-rose-700 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    Beban Operasional (Expenses)
                </h3>
            </div>
            <table class="min-w-full text-sm">
                <tbody>
                    @forelse ($expenseBreakdown as $item)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-6 py-2.5 text-gray-700">
                                <span class="font-mono text-xs text-gray-400 mr-2">{{ $item['code'] }}</span>
                                {{ $item['name'] }}
                            </td>
                            <td class="px-6 py-2.5 text-right font-mono text-gray-700">Rp{{ number_format($item['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-6 py-3 text-center text-gray-400 text-xs">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-rose-50/60">
                        <td class="px-6 py-3 font-semibold text-rose-700">Total Beban</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-rose-700">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- NET PROFIT --}}
        <div class="px-6 py-5 flex items-center justify-between {{ $netProfit >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
            <p class="text-lg font-bold {{ $netProfit >= 0 ? 'text-emerald-800' : 'text-red-800' }} flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Laba Bersih (Net Profit)
            </p>
            <p class="font-mono font-bold text-2xl {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp{{ number_format($netProfit, 0, ',', '.') }}
            </p>
        </div>
        </div> {{-- /overflow-x-auto --}}
    </div>

    {{-- Summary Bar --}}
    <div class="mt-5 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-emerald-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">Revenue</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-700">Rp{{ number_format($totalCogs, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">HPP</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-rose-600">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 uppercase mt-1">Expenses</p>
            </div>
            <div>
                <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    Rp{{ number_format($netProfit, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 uppercase mt-1">Net Profit</p>
            </div>
        </div>
        @if ($totalRevenue > 0)
            <div class="mt-4 w-full bg-gray-200 rounded-full h-3 overflow-hidden flex">
                @php
                    $cogsPct = round(($totalCogs / $totalRevenue) * 100, 1);
                    $expPct  = round(($totalExpenses / $totalRevenue) * 100, 1);
                    $profPct = max(0, 100 - $cogsPct - $expPct);
                @endphp
                <div class="bg-amber-400 h-3" style="width: {{ $cogsPct }}%" title="HPP {{ $cogsPct }}%"></div>
                <div class="bg-rose-400 h-3" style="width: {{ $expPct }}%" title="Expenses {{ $expPct }}%"></div>
                <div class="bg-emerald-400 h-3" style="width: {{ $profPct }}%" title="Profit {{ $profPct }}%"></div>
            </div>
            <div class="flex gap-4 text-xs text-gray-500 mt-2">
                <span><span class="inline-block w-3 h-3 bg-amber-400 rounded mr-1"></span>HPP {{ $cogsPct }}%</span>
                <span><span class="inline-block w-3 h-3 bg-rose-400 rounded mr-1"></span>Expenses {{ $expPct }}%</span>
                <span><span class="inline-block w-3 h-3 bg-emerald-400 rounded mr-1"></span>Profit {{ $profPct }}%</span>
            </div>
        @endif
    </div>
</div>
@endsection

