@extends('layouts.master')

@section('title', 'Revenue â€” Handai Finance')

@section('page-style')
<style>
    .fc { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Revenue Management</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $store->name }} â€” Analisis pemasukan bisnis</p>
    </div>

    {{-- Total --}}
    <div class="fc p-5 mb-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
            <i class="ti ti-chart-bar text-xl text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400 uppercase font-semibold">Total Revenue Periode Ini</p>
            <p class="text-2xl font-bold text-green-700">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="fc p-4 mb-6">
        <form method="GET" action="{{ route('manager.finance.revenue.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-gray-500 font-medium">Periode</label>
                <select name="period" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                        onchange="this.form.submit()">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Channel</label>
                <select name="channel" class="mt-1 block w-full text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                    <option value="ALL" {{ $channel === 'ALL' ? 'selected' : '' }}>Semua</option>
                    <option value="POS" {{ $channel === 'POS' ? 'selected' : '' }}>POS</option>
                    <option value="KASIR" {{ $channel === 'KASIR' ? 'selected' : '' }}>Kasir</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-900 transition">Filter</button>
        </form>
    </div>

    {{-- Revenue Trend Chart --}}
    <div class="fc p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-700 mb-4">Revenue Trend</h3>
        <div style="height:300px"><canvas id="revenueChart"></canvas></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Revenue by Channel --}}
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Revenue by Channel</h3>
            @if ($revenueByChannel->isNotEmpty())
                <div style="height:250px"><canvas id="channelChart"></canvas></div>
            @else
                <p class="text-sm text-gray-400 text-center py-8">Tidak ada data</p>
            @endif
        </div>

        {{-- Revenue by Product --}}
        <div class="fc p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Top Produk by Revenue</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse ($revenueByProduct as $i => $prod)
                    <div class="flex items-center justify-between py-1.5 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 w-5">#{{ $i + 1 }}</span>
                            <span class="text-sm text-gray-700">{{ Str::limit($prod->name, 25) }}</span>
                            <span class="text-xs text-gray-400">({{ number_format($prod->total_qty) }} pcs)</span>
                        </div>
                        <span class="text-sm font-semibold text-green-600">Rp{{ number_format($prod->total_revenue, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Revenue Detail Table --}}
    <div class="fc overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Revenue per Periode</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Periode</th>
                        <th class="text-right px-5 py-3 font-medium">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($revenueByPeriod as $row)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3 text-gray-700">{{ $row->period_key }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-green-600">Rp{{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-5 py-8 text-center text-gray-400">Belum ada data revenue</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($revenueByPeriod);

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: data.map(d => d.period_key),
            datasets: [{
                label: 'Revenue',
                data: data.map(d => d.total),
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => 'Rp' + (v/1000000).toFixed(1) + 'jt', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });

    @if ($revenueByChannel->isNotEmpty())
    const channelData = @json($revenueByChannel);
    new Chart(document.getElementById('channelChart'), {
        type: 'doughnut',
        data: {
            labels: channelData.map(c => c.source),
            datasets: [{
                data: channelData.map(c => c.total),
                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444'],
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 11 } } } }
        }
    });
    @endif
});
</script>
@endsection

