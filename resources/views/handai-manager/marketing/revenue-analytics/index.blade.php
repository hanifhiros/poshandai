@extends('layouts.master')
@section('title', 'Revenue Analytics')

@section('vendor-style')
<style>
    .ra-card {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem;
        padding: 1.25rem;
        transition: box-shadow .2s;
    }
    .ra-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .ra-val { font-size: 1.5rem; font-weight: 700; color: var(--text-primary, #0f172a); line-height: 1.2; }
    .ra-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted, #94a3b8); }
    .ra-chart-box {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem; padding: 1.25rem;
    }
    .ra-chart-box h3 { font-size: .85rem; font-weight: 600; color: var(--text-primary, #0f172a); margin-bottom: .75rem; }
    .ra-filter-btn {
        padding: 6px 16px; border-radius: 8px; font-size: .78rem; font-weight: 500;
        border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; transition: all .15s;
    }
    .ra-filter-btn:hover { border-color: #0C9044; color: #0C9044; }
    .ra-filter-btn.active { background: #0C9044; color: #fff; border-color: #0C9044; }
    .ra-date-input {
        height: 34px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0 10px;
        font-size: .78rem; color: #334155; background: #fff;
    }
    .ra-badge {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .65rem; font-weight: 600; padding: 2px 10px; border-radius: 999px;
    }
    .ra-badge-up { background: #ecfdf5; color: #059669; }
    .ra-badge-down { background: #fef2f2; color: #dc2626; }
</style>
@endsection

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">

    {{-- HEADER + PERIOD FILTER --}}
    <form method="GET" action="" class="ra-card flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-slate-600 mr-1">
            <i class="ti ti-calendar-stats text-base align-middle mr-1"></i>Periode
        </span>

        @php
            $periods = [
                'today'      => 'Hari Ini',
                'this_week'  => 'Minggu Ini',
                'this_month' => 'Bulan Ini',
                'custom'     => 'Custom',
            ];
        @endphp

        @foreach($periods as $key => $label)
            <button type="submit" name="period" value="{{ $key }}"
                class="ra-filter-btn {{ ($period ?? 'this_month') === $key ? 'active' : '' }}"
                @if($key !== 'custom') onclick="this.form.querySelectorAll('.ra-date-input').forEach(i => i.disabled = true);" @endif
            >{{ $label }}</button>
        @endforeach

        <div class="flex items-center gap-2 ml-auto">
            <input type="date" name="start_date" value="{{ isset($startDate) ? $startDate->format('Y-m-d') : '' }}"
                   class="ra-date-input" placeholder="Mulai">
            <span class="text-xs text-slate-400">â€”</span>
            <input type="date" name="end_date" value="{{ isset($endDate) ? $endDate->format('Y-m-d') : '' }}"
                   class="ra-date-input" placeholder="Akhir">
            <button type="submit" name="period" value="custom"
                    class="ra-filter-btn active" style="background:#0C9044;color:#fff;border-color:#0C9044;">
                <i class="ti ti-filter text-xs mr-1"></i>Terapkan
            </button>
        </div>
    </form>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        {{-- Total Revenue --}}
        <div class="ra-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ra-label">Total Revenue</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5;">
                    <i class="ti ti-cash text-base" style="color:#0C9044;"></i>
                </div>
            </div>
            <p class="ra-val">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
            <div class="flex items-center gap-2 mt-2">
                <span class="ra-badge {{ ($revenueGrowth ?? 0) >= 0 ? 'ra-badge-up' : 'ra-badge-down' }}">
                    <i class="ti {{ ($revenueGrowth ?? 0) >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($revenueGrowth ?? 0), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- Revenue Growth --}}
        <div class="ra-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ra-label">Pertumbuhan</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                     style="background:{{ ($revenueGrowth ?? 0) >= 0 ? '#ecfdf5' : '#fef2f2' }};">
                    <i class="ti {{ ($revenueGrowth ?? 0) >= 0 ? 'ti-trending-up' : 'ti-trending-down' }} text-base"
                       style="color:{{ ($revenueGrowth ?? 0) >= 0 ? '#0C9044' : '#dc2626' }};"></i>
                </div>
            </div>
            <p class="ra-val" style="color:{{ ($revenueGrowth ?? 0) >= 0 ? '#059669' : '#dc2626' }};">
                {{ ($revenueGrowth ?? 0) >= 0 ? '+' : '' }}{{ number_format($revenueGrowth ?? 0, 1) }}%
            </p>
            <p class="mt-2" style="font-size:.68rem;color:#94a3b8;">
                Sebelumnya: Rp {{ number_format($previousRevenue ?? 0, 0, ',', '.') }}
            </p>
        </div>

        {{-- Revenue per Customer --}}
        <div class="ra-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ra-label">Revenue / Customer</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff;">
                    <i class="ti ti-users text-base" style="color:#3b82f6;"></i>
                </div>
            </div>
            <p class="ra-val">Rp {{ number_format($revenuePerCustomer ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- AOV --}}
        <div class="ra-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ra-label">Rata-rata Order (AOV)</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fefce8;">
                    <i class="ti ti-shopping-cart text-base" style="color:#ca8a04;"></i>
                </div>
            </div>
            <p class="ra-val">Rp {{ number_format($aov ?? 0, 0, ',', '.') }}</p>
            <p class="mt-2" style="font-size:.68rem;color:#94a3b8;">
                {{ number_format($totalOrders ?? 0, 0, ',', '.') }} total order
            </p>
        </div>

    </div>

    {{-- REVENUE TREND CHART --}}
    <div class="ra-chart-box">
        <h3><i class="ti ti-chart-line text-base align-middle mr-1" style="color:#0C9044;"></i>Tren Revenue</h3>
        <div style="height:300px;"><canvas id="chartRevenueTrend"></canvas></div>
    </div>

    {{-- AOV TREND CHART --}}
    <div class="ra-chart-box">
        <h3><i class="ti ti-chart-bar text-base align-middle mr-1" style="color:#3b82f6;"></i>Tren Rata-rata Order (AOV)</h3>
        <div style="height:260px;"><canvas id="chartAovTrend"></canvas></div>
    </div>

    {{-- BREAKDOWN: CATEGORY & PAYMENT METHOD --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Revenue by Category --}}
        <div class="ra-chart-box">
            <h3><i class="ti ti-category text-base align-middle mr-1" style="color:#8b5cf6;"></i>Revenue per Kategori</h3>
            <div style="height:300px;"><canvas id="chartByCategory"></canvas></div>
            <div id="legendCategory" class="flex flex-wrap gap-3 mt-3"></div>
        </div>

        {{-- Revenue by Payment Method --}}
        <div class="ra-chart-box">
            <h3><i class="ti ti-credit-card text-base align-middle mr-1" style="color:#ec4899;"></i>Revenue per Metode Pembayaran</h3>
            <div style="height:300px;"><canvas id="chartByPayment"></canvas></div>
            <div id="legendPayment" class="flex flex-wrap gap-3 mt-3"></div>
        </div>

    </div>

</div>
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7"></script>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const chartColors = ['#0C9044','#3b82f6','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#ef4444','#84cc16','#14b8a6','#f97316'];

    const tooltipDefaults = {
        backgroundColor: 'rgba(15,23,42,0.92)',
        titleFont: { family: 'Poppins', size: 12 },
        bodyFont: { family: 'Poppins', size: 11 },
        padding: 10,
        cornerRadius: 8,
    };

    function rupiah(v) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
    }

    function buildLegend(chart, containerId) {
        const el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = '';
        chart.data.labels.forEach(function (label, i) {
            const color = chart.data.datasets[0].backgroundColor[i];
            const value = chart.data.datasets[0].data[i];
            const item = document.createElement('div');
            item.style.cssText = 'display:inline-flex;align-items:center;gap:5px;font-size:.72rem;color:#475569;';
            item.innerHTML = '<span style="width:10px;height:10px;border-radius:3px;background:' + color + ';display:inline-block;"></span>' +
                             '<span>' + label + '</span>' +
                             '<span style="font-weight:600;">' + rupiah(value) + '</span>';
            el.appendChild(item);
        });
    }

    // â”€â”€ Revenue Trend (Line) â”€â”€
    const revLabels = @json(($revenueTrend ?? collect())->pluck('date'));
    const revData   = @json(($revenueTrend ?? collect())->pluck('revenue'));

    if (revLabels.length > 0) {
        const ctxRev = document.getElementById('chartRevenueTrend').getContext('2d');
        const gradRev = ctxRev.createLinearGradient(0, 0, 0, 300);
        gradRev.addColorStop(0, 'rgba(12,144,68,0.18)');
        gradRev.addColorStop(1, 'rgba(12,144,68,0.01)');

        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: revLabels,
                datasets: [{
                    label: 'Revenue',
                    data: revData,
                    borderColor: '#0C9044',
                    backgroundColor: gradRev,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#0C9044',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: { label: ctx => rupiah(ctx.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => rupiah(v) } }
                }
            }
        });
    }

    // â”€â”€ AOV Trend (Line) â”€â”€
    const aovLabels = @json(($aovTrend ?? collect())->pluck('date'));
    const aovData   = @json(($aovTrend ?? collect())->pluck('aov'));

    if (aovLabels.length > 0) {
        const ctxAov = document.getElementById('chartAovTrend').getContext('2d');
        const gradAov = ctxAov.createLinearGradient(0, 0, 0, 260);
        gradAov.addColorStop(0, 'rgba(59,130,246,0.16)');
        gradAov.addColorStop(1, 'rgba(59,130,246,0.01)');

        new Chart(ctxAov, {
            type: 'line',
            data: {
                labels: aovLabels,
                datasets: [{
                    label: 'AOV',
                    data: aovData,
                    borderColor: '#3b82f6',
                    backgroundColor: gradAov,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: { label: ctx => rupiah(ctx.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: v => rupiah(v) } }
                }
            }
        });
    }

    // â”€â”€ Revenue by Category (Doughnut) â”€â”€
    const catLabels = @json(($revenueByCategory ?? collect())->pluck('category'));
    const catData   = @json(($revenueByCategory ?? collect())->pluck('revenue'));

    if (catLabels.length > 0) {
        const catChart = new Chart(document.getElementById('chartByCategory'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: chartColors.slice(0, catLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + rupiah(ctx.parsed) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
        buildLegend(catChart, 'legendCategory');
    }

    // â”€â”€ Revenue by Payment Method (Doughnut) â”€â”€
    const payLabels = @json(($revenueByPaymentMethod ?? collect())->pluck('payment_type'));
    const payData   = @json(($revenueByPaymentMethod ?? collect())->pluck('revenue'));

    if (payLabels.length > 0) {
        const payChart = new Chart(document.getElementById('chartByPayment'), {
            type: 'doughnut',
            data: {
                labels: payLabels,
                datasets: [{
                    data: payData,
                    backgroundColor: chartColors.slice(0, payLabels.length).reverse(),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + rupiah(ctx.parsed) + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
        buildLegend(payChart, 'legendPayment');
    }

});
</script>
@endsection

