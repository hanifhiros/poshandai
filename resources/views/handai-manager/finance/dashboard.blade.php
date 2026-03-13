@extends('handai-manager.layouts.master')

@section('title', 'Finance Dashboard — Handai')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    .kpi-up { color: #16a34a; }
    .kpi-down { color: #dc2626; }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ loaded: false }" x-init="$nextTick(() => loaded = true)">

    {{-- Header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Finance Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name ?? 'Store' }} — {{ now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('manager.finance.revenue.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <i class="ti ti-chart-bar text-sm"></i> Revenue
            </a>
            <a href="{{ route('manager.finance.expenses.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <i class="ti ti-receipt text-sm"></i> Expenses
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    @php
        $kpis = [
            ['label' => 'Total Revenue', 'value' => $revenue, 'prev' => $prevRevenue, 'icon' => 'ti-chart-bar', 'bg' => 'bg-green-100', 'color' => 'text-green-600', 'good' => 'up'],
            ['label' => 'Total Expenses', 'value' => $cogs + $expenses, 'prev' => null, 'icon' => 'ti-receipt-2', 'bg' => 'bg-red-100', 'color' => 'text-red-600', 'good' => 'down'],
            ['label' => 'Net Profit', 'value' => $netProfit, 'prev' => $prevNetProfit, 'icon' => 'ti-trending-up', 'bg' => $netProfit >= 0 ? 'bg-emerald-100' : 'bg-red-100', 'color' => $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600', 'good' => 'up'],
            ['label' => 'Cash Balance', 'value' => $cashBalance, 'prev' => null, 'icon' => 'ti-wallet', 'bg' => 'bg-blue-100', 'color' => 'text-blue-600', 'good' => 'up'],
            ['label' => 'Piutang (AR)', 'value' => $outstandingAR, 'prev' => null, 'icon' => 'ti-file-invoice', 'bg' => 'bg-amber-100', 'color' => 'text-amber-600', 'good' => 'down'],
            ['label' => 'Hutang (AP)', 'value' => $outstandingAP, 'prev' => null, 'icon' => 'ti-alert-triangle', 'bg' => 'bg-orange-100', 'color' => 'text-orange-600', 'good' => 'down'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8"
         x-show="loaded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        @foreach ($kpis as $kpi)
            @php
                $change = null;
                if ($kpi['prev'] !== null && $kpi['prev'] != 0) {
                    $change = round((($kpi['value'] - $kpi['prev']) / abs($kpi['prev'])) * 100, 1);
                }
            @endphp
            <div class="fc p-4">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg {{ $kpi['bg'] }} flex items-center justify-center">
                        <i class="ti {{ $kpi['icon'] }} {{ $kpi['color'] }} text-base"></i>
                    </div>
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400">{{ $kpi['label'] }}</p>
                </div>
                <p class="text-xl font-bold text-gray-800">Rp{{ number_format($kpi['value'], 0, ',', '.') }}</p>
                @if ($change !== null)
                    <div class="flex items-center gap-1 mt-1.5">
                        <i class="ti {{ $change >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs {{ $change >= 0 ? ($kpi['good'] === 'up' ? 'kpi-up' : 'kpi-down') : ($kpi['good'] === 'up' ? 'kpi-down' : 'kpi-up') }}"></i>
                        <span class="text-[11px] font-medium {{ $change >= 0 ? ($kpi['good'] === 'up' ? 'kpi-up' : 'kpi-down') : ($kpi['good'] === 'up' ? 'kpi-down' : 'kpi-up') }}">{{ $change > 0 ? '+' : '' }}{{ $change }}%</span>
                        <span class="text-[10px] text-gray-400 ml-0.5">vs bulan lalu</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Cashflow Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
        <div class="fc p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <i class="ti ti-arrow-down-left text-xl text-green-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold">Cash In (bulan ini)</p>
                <p class="text-xl font-bold text-gray-800">Rp{{ number_format($cashIn, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="fc p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                <i class="ti ti-arrow-up-right text-xl text-red-600"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold">Cash Out (bulan ini)</p>
                <p class="text-xl font-bold text-gray-800">Rp{{ number_format($cashOut, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="fc p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ ($cashIn - $cashOut) >= 0 ? 'bg-blue-100' : 'bg-red-100' }} flex items-center justify-center">
                <i class="ti ti-arrows-exchange text-xl {{ ($cashIn - $cashOut) >= 0 ? 'text-blue-600' : 'text-red-600' }}"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-semibold">Net Cashflow</p>
                <p class="text-xl font-bold {{ ($cashIn - $cashOut) >= 0 ? 'text-green-700' : 'text-red-600' }}">Rp{{ number_format($cashIn - $cashOut, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Revenue & Expense Trend</h3>
            <div style="height:280px"><canvas id="trendChart"></canvas></div>
        </div>
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Profit Trend</h3>
            <div style="height:280px"><canvas id="profitChart"></canvas></div>
        </div>
    </div>

    {{-- Bottom Section: Recent Expenses + Overdue --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Expenses --}}
        <div class="fc p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Pengeluaran Terakhir</h3>
                <a href="{{ route('manager.finance.expenses.index') }}" class="text-xs text-green-600 hover:underline">Lihat Semua →</a>
            </div>
            @forelse ($recentExpenses as $exp)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="text-sm text-gray-700">{{ Str::limit($exp->description, 30) }}</p>
                        <p class="text-xs text-gray-400">{{ $exp->category->name ?? '-' }} · {{ $exp->expense_date->format('d/m/Y') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-red-600">-Rp{{ number_format($exp->amount, 0, ',', '.') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada pengeluaran</p>
            @endforelse
        </div>

        {{-- Overdue AP --}}
        <div class="fc p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Hutang Jatuh Tempo</h3>
                <a href="{{ route('manager.finance.ap.index') }}" class="text-xs text-green-600 hover:underline">Lihat Semua →</a>
            </div>
            @forelse ($overdueAP as $ap)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="text-sm text-gray-700">{{ $ap->supplier->name ?? '-' }}</p>
                        <p class="text-xs text-red-500">Jatuh tempo: {{ $ap->due_date->format('d/m/Y') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-orange-600">Rp{{ number_format($ap->total_amount - $ap->paid_amount, 0, ',', '.') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada hutang jatuh tempo</p>
            @endforelse
        </div>

        {{-- Overdue AR --}}
        <div class="fc p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Piutang Jatuh Tempo</h3>
                <a href="{{ route('manager.finance.ar.index') }}" class="text-xs text-green-600 hover:underline">Lihat Semua →</a>
            </div>
            @forelse ($overdueAR as $ar)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="text-sm text-gray-700">{{ $ar->customer->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-red-500">Jatuh tempo: {{ $ar->due_date->format('d/m/Y') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-amber-600">Rp{{ number_format($ar->total_amount - $ar->paid_amount, 0, ',', '.') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-4">Tidak ada piutang jatuh tempo</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trend = @json($monthlyTrend);

    // Revenue & Expense Trend
    new Chart(document.getElementById('trendChart'), {
        type: 'bar',
        data: {
            labels: trend.map(t => t.label),
            datasets: [
                { label: 'Revenue', data: trend.map(t => t.revenue), backgroundColor: 'rgba(34,197,94,0.7)', borderRadius: 6 },
                { label: 'Expenses', data: trend.map(t => t.expense), backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 6 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rp' + (v/1000000).toFixed(1) + 'jt', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // Profit Trend
    new Chart(document.getElementById('profitChart'), {
        type: 'line',
        data: {
            labels: trend.map(t => t.label),
            datasets: [{
                label: 'Net Profit',
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
