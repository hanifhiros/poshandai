@extends('handai-manager.layouts.master')
@section('title', 'Marketing Dashboard')

@section('vendor-style')
<style>
    .mk-card {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem;
        padding: 1.25rem;
        transition: box-shadow .2s;
    }
    .mk-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .mk-val { font-size: 1.5rem; font-weight: 700; color: var(--text-primary, #0f172a); line-height: 1.2; }
    .mk-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted, #94a3b8); }
    .mk-badge {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: .65rem; font-weight: 600; padding: 2px 8px; border-radius: 999px;
    }
    .mk-badge-up { background: #ecfdf5; color: #059669; }
    .mk-badge-down { background: #fef2f2; color: #dc2626; }
    .mk-badge-neutral { background: #f8fafc; color: #64748b; }
    .mk-chart-box {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem; padding: 1.25rem;
    }
    .mk-chart-box h3 { font-size: .85rem; font-weight: 600; color: var(--text-primary, #0f172a); margin-bottom: .75rem; }
    .mk-table { width: 100%; font-size: .8rem; border-collapse: collapse; }
    .mk-table thead th {
        text-align: left; font-weight: 600; font-size: .7rem; text-transform: uppercase;
        letter-spacing: .03em; color: var(--text-muted, #94a3b8); padding: .5rem .75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .mk-table tbody td { padding: .55rem .75rem; border-bottom: 1px solid #f8fafc; color: var(--text-primary, #0f172a); }
    .mk-table tbody tr:hover { background: #f8fafc; }
    .mk-rank {
        width: 22px; height: 22px; border-radius: 6px; display: inline-flex;
        align-items: center; justify-content: center; font-size: .65rem; font-weight: 700;
    }
    .mk-filter-btn {
        padding: 6px 16px; border-radius: 8px; font-size: .78rem; font-weight: 500;
        border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; transition: all .15s;
    }
    .mk-filter-btn:hover { border-color: #0C9044; color: #0C9044; }
    .mk-filter-btn.active { background: #0C9044; color: #fff; border-color: #0C9044; }
    .mk-date-input {
        height: 34px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0 10px;
        font-size: .78rem; color: #334155; background: #fff;
    }
    .mk-alert-box { border-radius: .75rem; padding: 1rem 1.25rem; font-size: .8rem; line-height: 1.6; }
</style>
@endsection

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════
         ZONE A: Period Filter + KPI Cards
    ═══════════════════════════════════════════════ --}}

    {{-- Period Filter --}}
    <form method="GET" action="" class="mk-card flex flex-wrap items-center gap-3">
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
                class="mk-filter-btn {{ ($period ?? 'this_month') === $key ? 'active' : '' }}"
                @if($key !== 'custom') onclick="this.form.querySelectorAll('.mk-date-input').forEach(i => i.disabled = true);" @endif
            >{{ $label }}</button>
        @endforeach

        <div class="flex items-center gap-2 ml-auto">
            <input type="date" name="start_date" value="{{ isset($startDate) ? $startDate->format('Y-m-d') : '' }}"
                   class="mk-date-input" placeholder="Mulai">
            <span class="text-xs text-slate-400">—</span>
            <input type="date" name="end_date" value="{{ isset($endDate) ? $endDate->format('Y-m-d') : '' }}"
                   class="mk-date-input" placeholder="Akhir">
            <button type="submit" name="period" value="custom"
                    class="mk-filter-btn active" style="background:#0C9044;color:#fff;border-color:#0C9044;">
                <i class="ti ti-filter text-xs mr-1"></i>Terapkan
            </button>
        </div>
    </form>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        {{-- 1. Total Revenue --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Total pendapatan pada periode ini">Total Revenue</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5;">
                    <i class="ti ti-currency-dollar text-base" style="color:#0C9044;"></i>
                </div>
            </div>
            <p class="mk-val">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
            <div class="flex items-center gap-2 mt-2">
                @php $rg = $revenueGrowth ?? 0; @endphp
                <span class="mk-badge {{ $rg >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $rg >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($rg), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- 2. Total Customer --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Jumlah pelanggan unik pada periode ini">Total Customer</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff;">
                    <i class="ti ti-users text-base" style="color:#3b82f6;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($totalCustomers ?? 0, 0, ',', '.') }}</p>
            <div class="flex items-center gap-2 mt-2">
                @php $cg = $customerGrowth ?? 0; @endphp
                <span class="mk-badge {{ $cg >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $cg >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($cg), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- 3. AOV --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Average Order Value — rata-rata nilai per transaksi">AOV</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fefce8;">
                    <i class="ti ti-receipt text-base" style="color:#ca8a04;"></i>
                </div>
            </div>
            <p class="mk-val">Rp {{ number_format($aov ?? 0, 0, ',', '.') }}</p>
            <div class="flex items-center gap-2 mt-2">
                @php $ag = $aovGrowth ?? 0; @endphp
                <span class="mk-badge {{ $ag >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $ag >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($ag), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- 4. Repeat Purchase Rate --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Persentase pelanggan yang melakukan pembelian ulang">Repeat Purchase</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#f0fdf4;">
                    <i class="ti ti-repeat text-base" style="color:#16a34a;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($repeatPurchaseRate ?? 0, 1) }}%</p>
            <div class="flex items-center gap-2 mt-2">
                @php $rpDiff = ($repeatPurchaseRate ?? 0) - ($previousRepeatRate ?? 0); @endphp
                <span class="mk-badge {{ $rpDiff >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $rpDiff >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($rpDiff), 1) }}pp
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- 5. Churn Rate --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Persentase pelanggan yang berhenti bertransaksi">Churn Rate</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fef2f2;">
                    <i class="ti ti-user-minus text-base" style="color:#dc2626;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($churnRate ?? 0, 1) }}%</p>
            <div class="flex items-center gap-2 mt-2">
                @php $chDiff = ($churnRate ?? 0) - ($previousChurnRate ?? 0); @endphp
                {{-- Churn up = bad (red), churn down = good (green) --}}
                <span class="mk-badge {{ $chDiff <= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $chDiff <= 0 ? 'ti-arrow-down-right' : 'ti-arrow-up-right' }} text-xs"></i>
                    {{ number_format(abs($chDiff), 1) }}pp
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- 6. Active Customer Rate --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Persentase pelanggan aktif dari total pelanggan">Active Customer</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff;">
                    <i class="ti ti-user-check text-base" style="color:#2563eb;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($activeCustomerRate ?? 0, 1) }}%</p>
            <div class="mt-2">
                <span class="mk-badge mk-badge-neutral">
                    <i class="ti ti-info-circle text-xs"></i>
                    dari total pelanggan
                </span>
            </div>
        </div>

        {{-- 7. Revenue per Customer --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Rata-rata pendapatan per pelanggan">Rev / Customer</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#faf5ff;">
                    <i class="ti ti-coin text-base" style="color:#7c3aed;"></i>
                </div>
            </div>
            <p class="mk-val">Rp {{ number_format($revenuePerCustomer ?? 0, 0, ',', '.') }}</p>
            <div class="flex items-center gap-2 mt-2">
                @php
                    $rpcPrev = $previousRevenuePerCustomer ?? 0;
                    $rpcDiff = $rpcPrev > 0 ? (($revenuePerCustomer ?? 0) - $rpcPrev) / $rpcPrev * 100 : 0;
                @endphp
                <span class="mk-badge {{ $rpcDiff >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $rpcDiff >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($rpcDiff), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- 8. Gross Margin Rata-rata --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Rata-rata margin kotor dari seluruh produk">Gross Margin</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5;">
                    <i class="ti ti-percentage text-base" style="color:#0C9044;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($grossMarginAvg ?? 0, 1) }}%</p>
            <div class="mt-2">
                <span class="mk-badge mk-badge-neutral">
                    <i class="ti ti-chart-pie text-xs"></i>
                    rata-rata semua produk
                </span>
            </div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
         ZONE B: Insights & Trend Charts
    ═══════════════════════════════════════════════ --}}

    {{-- Alerts & Recommendations --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Alerts --}}
        @if(!empty($alerts) && count($alerts))
        <div class="mk-alert-box" style="background:#fefce8;border:1px solid #fde68a;">
            <div class="flex items-center gap-2 mb-2">
                <i class="ti ti-alert-triangle text-lg" style="color:#ca8a04;"></i>
                <span class="font-semibold text-sm" style="color:#92400e;">Peringatan</span>
            </div>
            <ul class="space-y-1 pl-5 list-disc" style="color:#78350f;">
                @foreach($alerts as $alert)
                    <li>{{ $alert }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Recommendations --}}
        @if(!empty($recommendations) && count($recommendations))
        <div class="mk-alert-box" style="background:#eff6ff;border:1px solid #bfdbfe;">
            <div class="flex items-center gap-2 mb-2">
                <i class="ti ti-bulb text-lg" style="color:#2563eb;"></i>
                <span class="font-semibold text-sm" style="color:#1e3a5f;">Rekomendasi</span>
            </div>
            <ul class="space-y-1 pl-5 list-disc" style="color:#1e40af;">
                @foreach($recommendations as $rec)
                    <li>{{ $rec }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Revenue Trend --}}
        <div class="mk-chart-box lg:col-span-2">
            <h3><i class="ti ti-chart-line text-base mr-1" style="color:#0C9044;"></i>Tren Revenue</h3>
            <div style="height:280px;"><canvas id="chartRevenueTrend"></canvas></div>
        </div>

        {{-- Customer Growth Trend --}}
        <div class="mk-chart-box">
            <h3><i class="ti ti-chart-bar text-base mr-1" style="color:#3b82f6;"></i>Tren Pertumbuhan Customer</h3>
            <div style="height:240px;"><canvas id="chartCustomerGrowth"></canvas></div>
        </div>

        {{-- AOV Trend --}}
        <div class="mk-chart-box">
            <h3><i class="ti ti-chart-dots text-base mr-1" style="color:#ca8a04;"></i>Tren AOV</h3>
            <div style="height:240px;"><canvas id="chartAovTrend"></canvas></div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
         ZONE C: Strategic Focus Tables
    ═══════════════════════════════════════════════ --}}

    @php
        $rankStyles = [
            0 => ['bg' => '#fef9c3', 'color' => '#a16207'],
            1 => ['bg' => '#f1f5f9', 'color' => '#475569'],
            2 => ['bg' => '#fff7ed', 'color' => '#c2410c'],
        ];
        $rankDefault = ['bg' => '#f8fafc', 'color' => '#94a3b8'];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Top 5 Revenue Products --}}
        <div class="mk-chart-box">
            <h3><i class="ti ti-trophy text-base mr-1" style="color:#ca8a04;"></i>Top 5 Produk — Revenue</h3>
            <table class="mk-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th class="text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($top5RevenueProducts ?? collect()) as $i => $p)
                    @php $rs = $rankStyles[$i] ?? $rankDefault; @endphp
                    <tr>
                        <td>
                            <span class="mk-rank" style="background:{{ $rs['bg'] }};color:{{ $rs['color'] }};">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="font-medium">{{ $p->name ?? $p['name'] ?? '-' }}</td>
                        <td class="text-right" style="color:#0C9044;font-weight:600;">
                            Rp {{ number_format($p->total_revenue ?? $p['total_revenue'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top 5 Margin Products --}}
        <div class="mk-chart-box">
            <h3><i class="ti ti-chart-arrows-vertical text-base mr-1" style="color:#16a34a;"></i>Top 5 Produk — Margin Tertinggi</h3>
            <table class="mk-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th class="text-right">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($top5MarginProducts ?? collect()) as $i => $p)
                    @php $rs = $rankStyles[$i] ?? $rankDefault; @endphp
                    <tr>
                        <td>
                            <span class="mk-rank" style="background:{{ $rs['bg'] }};color:{{ $rs['color'] }};">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="font-medium">{{ $p->name ?? $p['name'] ?? '-' }}</td>
                        <td class="text-right font-semibold" style="color:#16a34a;">
                            {{ number_format($p->margin_pct ?? $p['margin_pct'] ?? 0, 1) }}%
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Repeat Products --}}
        <div class="mk-chart-box">
            <h3><i class="ti ti-repeat text-base mr-1" style="color:#7c3aed;"></i>Produk — Repeat Rate Tertinggi</h3>
            <table class="mk-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th class="text-right">Repeat Buyers</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($topRepeatProducts ?? collect()) as $i => $p)
                    @php $rs = $rankStyles[$i] ?? $rankDefault; @endphp
                    <tr>
                        <td>
                            <span class="mk-rank" style="background:{{ $rs['bg'] }};color:{{ $rs['color'] }};">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="font-medium">{{ $p->name ?? $p['name'] ?? '-' }}</td>
                        <td class="text-right font-semibold" style="color:#7c3aed;">
                            {{ number_format($p->repeat_buyers ?? $p['repeat_buyers'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-slate-400 py-4">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
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

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15,23,42,0.92)',
                titleFont: { family: 'Poppins', size: 12 },
                bodyFont: { family: 'Poppins', size: 11 },
                padding: 10,
                cornerRadius: 8,
                displayColors: false,
            }
        }
    };

    // --- Revenue Trend (Line) ---
    const revLabels = @json(($revenueTrend ?? collect())->pluck('date'));
    const revData   = @json(($revenueTrend ?? collect())->pluck('revenue'));

    new Chart(document.getElementById('chartRevenueTrend'), {
        type: 'line',
        data: {
            labels: revLabels,
            datasets: [{
                label: 'Revenue',
                data: revData,
                borderColor: '#0C9044',
                backgroundColor: 'rgba(12,144,68,0.08)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: '#0C9044',
                pointHoverRadius: 5,
                borderWidth: 2,
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', maxTicksLimit: 12 }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10, family: 'Poppins' }, color: '#94a3b8',
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v)
                    },
                    beginAtZero: true,
                }
            },
            plugins: {
                ...chartDefaults.plugins,
                tooltip: {
                    ...chartDefaults.plugins.tooltip,
                    callbacks: {
                        label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                    }
                }
            }
        }
    });

    // --- Customer Growth Trend (Bar) ---
    const custLabels = @json(($customerGrowthTrend ?? collect())->pluck('date'));
    const custData   = @json(($customerGrowthTrend ?? collect())->pluck('new_customers'));

    new Chart(document.getElementById('chartCustomerGrowth'), {
        type: 'bar',
        data: {
            labels: custLabels,
            datasets: [{
                label: 'Customer Baru',
                data: custData,
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderRadius: 6,
                maxBarThickness: 32,
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', maxTicksLimit: 10 }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', precision: 0 },
                    beginAtZero: true,
                }
            }
        }
    });

    // --- AOV Trend (Line) ---
    const aovLabels = @json(($aovTrend ?? collect())->pluck('date'));
    const aovData   = @json(($aovTrend ?? collect())->pluck('aov'));

    new Chart(document.getElementById('chartAovTrend'), {
        type: 'line',
        data: {
            labels: aovLabels,
            datasets: [{
                label: 'AOV',
                data: aovData,
                borderColor: '#ca8a04',
                backgroundColor: 'rgba(202,138,4,0.08)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: '#ca8a04',
                pointHoverRadius: 5,
                borderWidth: 2,
            }]
        },
        options: {
            ...chartDefaults,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', maxTicksLimit: 10 }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10, family: 'Poppins' }, color: '#94a3b8',
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v)
                    },
                    beginAtZero: true,
                }
            },
            plugins: {
                ...chartDefaults.plugins,
                tooltip: {
                    ...chartDefaults.plugins.tooltip,
                    callbacks: {
                        label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                    }
                }
            }
        }
    });

});
</script>
@endsection
