@extends('handai-manager.layouts.master')

@section('title', 'Laporan Laba Rugi — Handai Finance')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Laba Rugi</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name ?? 'Semua Store' }}</p>
        </div>
        <a href="{{ route('manager.finance.accounting.dashboard') }}" class="mt-3 sm:mt-0 text-sm text-green-600 hover:underline">&larr; Dashboard</a>
    </div>

    {{-- Period Filter --}}
    <form method="GET" action="{{ route('manager.finance.accounting.income') }}" class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Periode</label>
                <select name="period" onchange="this.form.submit()" class="input input-bordered input-sm text-sm bg-white">
                    <option value="daily"   {{ $period === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="monthly"  {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly"   {{ $period === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    <option value="custom"   {{ $period === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
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

    {{-- Income Statement --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">

        {{-- REVENUE --}}
        <div class="border-b">
            <div class="px-6 py-3 bg-green-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-green-700 uppercase tracking-wide">
                    <i class="ti ti-chart-bar mr-1"></i> Pendapatan (Revenue)
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
                    <tr class="bg-green-50">
                        <td class="px-6 py-3 font-semibold text-green-700">Total Pendapatan</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-green-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- COGS --}}
        <div class="border-b">
            <div class="px-6 py-3 bg-amber-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-amber-700 uppercase tracking-wide">
                    <i class="ti ti-package mr-1"></i> Harga Pokok Penjualan (COGS)
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
                    <tr class="bg-amber-50">
                        <td class="px-6 py-3 font-semibold text-amber-700">Total HPP</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-amber-700">Rp{{ number_format($totalCogs, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- GROSS PROFIT --}}
        <div class="px-6 py-4 bg-blue-50 flex items-center justify-between border-b">
            <p class="font-semibold text-blue-800">
                <i class="ti ti-arrow-right mr-1"></i> Laba Kotor (Gross Profit)
            </p>
            <p class="font-mono font-bold text-lg {{ $grossProfit >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                Rp{{ number_format($grossProfit, 0, ',', '.') }}
            </p>
        </div>

        {{-- EXPENSES --}}
        <div class="border-b">
            <div class="px-6 py-3 bg-rose-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-rose-700 uppercase tracking-wide">
                    <i class="ti ti-receipt mr-1"></i> Beban Operasional (Expenses)
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
                    <tr class="bg-rose-50">
                        <td class="px-6 py-3 font-semibold text-rose-700">Total Beban</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-rose-700">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- NET PROFIT --}}
        <div class="px-6 py-5 flex items-center justify-between {{ $netProfit >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
            <p class="text-lg font-bold {{ $netProfit >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                <i class="ti ti-trophy mr-1"></i> Laba Bersih (Net Profit)
            </p>
            <p class="font-mono font-bold text-2xl {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp{{ number_format($netProfit, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Summary Bar --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border p-5">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-green-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
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
