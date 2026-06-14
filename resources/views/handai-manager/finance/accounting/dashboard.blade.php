@extends('layouts.master')

@section('title', 'Finance Dashboard â€” Handai')

@section('page-style')
<style>
    :root {
        --card-bg: #ffffff;
        --card-border: #e2e8f0;
        --card-radius: 16px;
        --card-shadow: 0 1px 3px 0 rgba(0,0,0,.04), 0 1px 2px -1px rgba(0,0,0,.04);
    }
    .fc { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: var(--card-radius); box-shadow: var(--card-shadow); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ loaded: false }" x-init="$nextTick(() => loaded = true)">

    {{-- Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Finance Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store?->name ?? 'Semua Store' }} &mdash; {{ now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-400">
            <i class="ti ti-clock text-sm"></i>
            <span>Cache 5 menit â€” data diperbarui otomatis saat ada transaksi baru</span>
        </div>
    </div>

    {{-- KPI Cards with comparison --}}
    @php
        $kpis = [
            ['label' => 'Revenue',      'value' => $revenue,   'prev' => $prevRevenue,   'icon' => 'ti-chart-bar',   'iconBg' => 'bg-green-100',   'iconColor' => 'text-green-600',   'good' => 'up'],
            ['label' => 'HPP (COGS)',   'value' => $cogs,      'prev' => $prevCogs,      'icon' => 'ti-package',     'iconBg' => 'bg-orange-100',  'iconColor' => 'text-orange-600',  'good' => 'down'],
            ['label' => 'Biaya',        'value' => $expenses,  'prev' => $prevExpenses,  'icon' => 'ti-receipt',     'iconBg' => 'bg-red-100',     'iconColor' => 'text-red-600',     'good' => 'down'],
            ['label' => 'Laba Bersih',  'value' => $netProfit, 'prev' => $prevNetProfit, 'icon' => 'ti-trending-up', 'iconBg' => $netProfit >= 0 ? 'bg-emerald-100' : 'bg-red-100', 'iconColor' => $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600', 'good' => 'up'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8"
         x-show="loaded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        @foreach ($kpis as $kpi)
            @php
                $change = $kpi['prev'] != 0 ? round((($kpi['value'] - $kpi['prev']) / abs($kpi['prev'])) * 100, 1) : ($kpi['value'] > 0 ? 100 : 0);
                $isPositiveChange = $change > 0;
                // For costs, going UP is bad; for revenue/profit, going UP is good
                $isGood = $kpi['good'] === 'up' ? $isPositiveChange : !$isPositiveChange;
                $changeColor = $change == 0 ? 'text-gray-400' : ($isGood ? 'text-green-600' : 'text-red-500');
                $changeIcon  = $change > 0 ? 'ti-arrow-up-right' : ($change < 0 ? 'ti-arrow-down-right' : 'ti-minus');
            @endphp
            <div class="fc p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl {{ $kpi['iconBg'] }} flex items-center justify-center">
                        <i class="ti {{ $kpi['icon'] }} {{ $kpi['iconColor'] }} text-xl"></i>
                    </div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $kpi['label'] }}</p>
                </div>
                <p class="text-2xl font-bold {{ $kpi['label'] === 'Laba Bersih' ? ($netProfit >= 0 ? 'text-green-700' : 'text-red-600') : 'text-gray-800' }}">
                    Rp{{ number_format($kpi['value'], 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1 mt-2">
                    <i class="ti {{ $changeIcon }} text-sm {{ $changeColor }}"></i>
                    <span class="text-xs font-medium {{ $changeColor }}">{{ $change > 0 ? '+' : '' }}{{ $change }}%</span>
                    <span class="text-xs text-gray-400 ml-1">vs bulan lalu</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Position Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8"
         x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        @php
            $positions = [
                ['label' => 'Kas',              'value' => $cashPosition,  'icon' => 'ti-wallet',    'color' => 'text-gray-800'],
                ['label' => 'Bank',             'value' => $bankPosition,  'icon' => 'ti-building-bank', 'color' => 'text-gray-800'],
                ['label' => 'Inventory Bahan',  'value' => $inventoryRaw,  'icon' => 'ti-box',       'color' => 'text-gray-800'],
                ['label' => 'Inventory Produk', 'value' => $inventoryFg,   'icon' => 'ti-package',   'color' => 'text-gray-800'],
                ['label' => 'Hutang',           'value' => $hutang,        'icon' => 'ti-alert-triangle', 'color' => 'text-red-600'],
            ];
        @endphp
        @foreach ($positions as $pos)
        <div class="fc p-4">
            <div class="flex items-center gap-2 mb-1">
                <i class="ti {{ $pos['icon'] }} text-sm text-gray-400"></i>
                <p class="text-xs text-gray-400 uppercase">{{ $pos['label'] }}</p>
            </div>
            <p class="text-lg font-bold {{ $pos['color'] }}">Rp{{ number_format($pos['value'], 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>

    {{-- 6-Month Trend Chart --}}
    <div class="fc p-6 mb-8">
        <h3 class="text-base font-semibold text-gray-700 mb-4">Tren Keuangan â€” 6 Bulan Terakhir</h3>
        <div style="height:320px">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- Recent Journals --}}
    <div class="fc p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-700">Jurnal Terbaru</h3>
            <a href="{{ route('manager.finance.accounting.journals') }}" class="text-sm text-green-600 hover:underline">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">No. Jurnal</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Sumber</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentJournals as $j)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ $j->journal_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $j->journal_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    @switch($j->source)
                                        @case('POS') bg-green-100 text-green-700 @break
                                        @case('KASIR') bg-blue-100 text-blue-700 @break
                                        @case('PURCHASE') bg-amber-100 text-amber-700 @break
                                        @case('PRODUCTION') bg-purple-100 text-purple-700 @break
                                        @case('CANCEL') bg-red-100 text-red-700 @break
                                        @case('EXPIRED') bg-rose-100 text-rose-700 @break
                                        @case('ADJUSTMENT') bg-gray-200 text-gray-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch">
                                    {{ $j->source }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $j->description }}</td>
                            <td class="px-4 py-3 text-right font-mono">Rp{{ number_format($j->total_debit, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono">Rp{{ number_format($j->total_credit, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">Belum ada jurnal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-6">
        <a href="{{ route('manager.finance.accounting.coa') }}" class="fc p-4 text-center hover:border-green-400 transition">
            <i class="ti ti-list-tree text-2xl text-green-600"></i>
            <p class="text-xs font-medium text-gray-600 mt-2">Chart of Accounts</p>
        </a>
        <a href="{{ route('manager.finance.accounting.journals') }}" class="fc p-4 text-center hover:border-green-400 transition">
            <i class="ti ti-notebook text-2xl text-blue-600"></i>
            <p class="text-xs font-medium text-gray-600 mt-2">Jurnal</p>
        </a>
        <a href="{{ route('manager.finance.accounting.income') }}" class="fc p-4 text-center hover:border-green-400 transition">
            <i class="ti ti-report-money text-2xl text-amber-600"></i>
            <p class="text-xs font-medium text-gray-600 mt-2">Laba Rugi</p>
        </a>
        <a href="{{ route('manager.finance.accounting.balance') }}" class="fc p-4 text-center hover:border-green-400 transition">
            <i class="ti ti-scale text-2xl text-purple-600"></i>
            <p class="text-xs font-medium text-gray-600 mt-2">Neraca</p>
        </a>
        <a href="{{ route('manager.finance.accounting.cashflow') }}" class="fc p-4 text-center hover:border-green-400 transition">
            <i class="ti ti-cash text-2xl text-emerald-600"></i>
            <p class="text-xs font-medium text-gray-600 mt-2">Arus Kas</p>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trend = @json($monthlyTrend);
    const ctx = document.getElementById('trendChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: trend.map(m => m.label),
            datasets: [
                {
                    label: 'Revenue',
                    data: trend.map(m => m.revenue),
                    backgroundColor: 'rgba(16,185,129,.7)',
                    borderRadius: 6,
                    barPercentage: 0.7,
                },
                {
                    label: 'COGS',
                    data: trend.map(m => m.cogs),
                    backgroundColor: 'rgba(245,158,11,.6)',
                    borderRadius: 6,
                    barPercentage: 0.7,
                },
                {
                    label: 'Expenses',
                    data: trend.map(m => m.expense),
                    backgroundColor: 'rgba(239,68,68,.5)',
                    borderRadius: 6,
                    barPercentage: 0.7,
                },
                {
                    label: 'Net Profit',
                    type: 'line',
                    data: trend.map(m => m.profit),
                    borderColor: '#0C9044',
                    backgroundColor: 'rgba(12,144,68,.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0C9044',
                    borderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, padding: 16, font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': Rp' + new Intl.NumberFormat('id-ID').format(ctx.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'Rp' + new Intl.NumberFormat('id-ID').format(v),
                        font: { size: 11 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
});
</script>
@endpush

