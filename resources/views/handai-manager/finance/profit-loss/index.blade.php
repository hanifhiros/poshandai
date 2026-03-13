@extends('handai-manager.layouts.master')

@section('title', 'Profit & Loss — Handai Finance')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Profit & Loss Report</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $store->name }} — {{ $periodLabel }}</p>
    </div>

    {{-- Filter --}}
    <div class="fc p-4 mb-6">
        <form method="GET" action="{{ route('manager.finance.profit-loss.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 font-medium">Periode</label>
                <select name="period" id="plPeriod" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            <div id="dateField" class="{{ $period !== 'daily' ? 'hidden' : '' }}">
                <label class="text-xs text-gray-500 font-medium">Tanggal</label>
                <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div id="monthField" class="{{ $period !== 'monthly' ? 'hidden' : '' }}">
                <label class="text-xs text-gray-500 font-medium">Bulan</label>
                <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div id="yearField" class="{{ $period !== 'yearly' ? 'hidden' : '' }}">
                <label class="text-xs text-gray-500 font-medium">Tahun</label>
                <input type="number" name="year" value="{{ request('year', now()->format('Y')) }}" min="2020" max="2099"
                       class="mt-1 block w-24 text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition">Tampilkan</button>
        </form>
    </div>

    {{-- KPI Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Revenue</p>
            <p class="text-xl font-bold text-green-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">COGS</p>
            <p class="text-xl font-bold text-orange-600">Rp{{ number_format($totalCogs, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Gross Profit</p>
            <p class="text-xl font-bold {{ $grossProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp{{ number_format($grossProfit, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Op. Expenses</p>
            <p class="text-xl font-bold text-red-600">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</p>
        </div>
        <div class="fc p-4 {{ $netProfit >= 0 ? 'ring-2 ring-green-200' : 'ring-2 ring-red-200' }}">
            <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold mb-1">Net Profit</p>
            <p class="text-xl font-bold {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">Rp{{ number_format($netProfit, 0, ',', '.') }}</p>
            <p class="text-xs {{ $marginPct >= 0 ? 'text-green-600' : 'text-red-500' }} mt-0.5">Margin: {{ $marginPct }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- P&L Statement --}}
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Laporan Laba Rugi</h3>

            {{-- Revenue --}}
            <div class="mb-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Pendapatan</p>
                @foreach ($revenueBreakdown as $item)
                    <div class="flex justify-between py-1">
                        <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
                        <span class="text-sm font-medium text-gray-800">Rp{{ number_format($item['balance'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between py-1 border-t border-gray-200 mt-1 font-semibold">
                    <span class="text-sm text-gray-700">Total Pendapatan</span>
                    <span class="text-sm text-green-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- COGS --}}
            <div class="mb-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Harga Pokok Penjualan</p>
                @foreach ($cogsBreakdown as $item)
                    <div class="flex justify-between py-1">
                        <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
                        <span class="text-sm font-medium text-gray-800">Rp{{ number_format($item['balance'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between py-1 border-t border-gray-200 mt-1 font-semibold">
                    <span class="text-sm text-gray-700">Laba Kotor</span>
                    <span class="text-sm {{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">Rp{{ number_format($grossProfit, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Operating Expenses --}}
            <div class="mb-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Biaya Operasional</p>
                @foreach ($expenseBreakdown as $item)
                    <div class="flex justify-between py-1">
                        <span class="text-sm text-gray-600">{{ $item['name'] }}</span>
                        <span class="text-sm font-medium text-gray-800">Rp{{ number_format($item['balance'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between py-2 border-t-2 border-gray-300 mt-2 font-bold">
                    <span class="text-sm text-gray-800">LABA BERSIH</span>
                    <span class="text-base {{ $netProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">Rp{{ number_format($netProfit, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Profit Trend --}}
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Profit Trend</h3>
            <div style="height:350px"><canvas id="profitTrendChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Period toggle
    const periodSel = document.getElementById('plPeriod');
    if (periodSel) {
        periodSel.addEventListener('change', function() {
            document.getElementById('dateField').classList.toggle('hidden', this.value !== 'daily');
            document.getElementById('monthField').classList.toggle('hidden', this.value !== 'monthly');
            document.getElementById('yearField').classList.toggle('hidden', this.value !== 'yearly');
        });
    }

    // Profit trend chart
    const trend = @json($trendData);
    new Chart(document.getElementById('profitTrendChart'), {
        type: 'line',
        data: {
            labels: trend.map(t => t.label),
            datasets: [{
                label: 'Profit',
                data: trend.map(t => t.profit),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true, tension: 0.3, pointRadius: 5, pointHoverRadius: 7, borderWidth: 2,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { callback: v => 'Rp' + (v/1000000).toFixed(1) + 'jt', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
});
</script>
@endsection
