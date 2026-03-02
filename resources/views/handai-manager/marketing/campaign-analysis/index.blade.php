@extends('handai-manager.layouts.master')
@section('title', 'Analisis Kampanye & Promosi')

@section('vendor-style')
<style>
    .ca-card {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem;
        padding: 1.25rem;
        transition: box-shadow .2s;
    }
    .ca-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .ca-val { font-size: 1.5rem; font-weight: 700; color: var(--text-primary, #0f172a); line-height: 1.2; }
    .ca-val-sm { font-size: 1.1rem; font-weight: 700; color: var(--text-primary, #0f172a); line-height: 1.2; }
    .ca-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted, #94a3b8); }
    .ca-chart-box {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem; padding: 1.25rem;
    }
    .ca-chart-box h3 { font-size: .85rem; font-weight: 600; color: var(--text-primary, #0f172a); margin-bottom: .75rem; }
    .ca-filter-btn {
        padding: 6px 16px; border-radius: 8px; font-size: .78rem; font-weight: 500;
        border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; transition: all .15s;
    }
    .ca-filter-btn:hover { border-color: #0C9044; color: #0C9044; }
    .ca-filter-btn.active { background: #0C9044; color: #fff; border-color: #0C9044; }
    .ca-date-input {
        height: 34px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0 10px;
        font-size: .78rem; color: #334155; background: #fff;
    }
    .ca-badge {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .65rem; font-weight: 600; padding: 2px 10px; border-radius: 999px;
    }
    .ca-badge-up { background: #ecfdf5; color: #059669; }
    .ca-badge-down { background: #fef2f2; color: #dc2626; }
    .ca-badge-neutral { background: #f8fafc; color: #64748b; }
    .ca-compare { display: flex; gap: .75rem; align-items: flex-end; }
    .ca-compare-item { flex: 1; text-align: center; }
    .ca-compare-divider { width: 1px; background: #e2e8f0; align-self: stretch; margin: .25rem 0; }
    .ca-tbl th { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; padding: .6rem .75rem; border-bottom: 1px solid #f1f5f9; }
    .ca-tbl td { font-size: .82rem; color: #334155; padding: .6rem .75rem; border-bottom: 1px solid #f8fafc; }
    .ca-tbl tr:hover td { background: #f8fafc; }
    .ca-rank { display: inline-flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; border-radius: .5rem; font-size: .7rem; font-weight: 700; }
</style>
@endsection

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">

    {{-- HEADER + PERIOD FILTER --}}
    <form method="GET" action="" class="ca-card flex flex-wrap items-center gap-3">
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
                class="ca-filter-btn {{ ($period ?? 'this_month') === $key ? 'active' : '' }}"
                @if($key !== 'custom') onclick="this.form.querySelectorAll('.ca-date-input').forEach(i => i.disabled = true);" @endif
            >{{ $label }}</button>
        @endforeach

        <div class="flex items-center gap-2 ml-auto">
            <input type="date" name="start_date" value="{{ isset($startDate) ? $startDate->format('Y-m-d') : '' }}"
                   class="ca-date-input" placeholder="Mulai">
            <span class="text-xs text-slate-400">—</span>
            <input type="date" name="end_date" value="{{ isset($endDate) ? $endDate->format('Y-m-d') : '' }}"
                   class="ca-date-input" placeholder="Akhir">
            <button type="submit" name="period" value="custom"
                    class="ca-filter-btn active" style="background:#0C9044;color:#fff;border-color:#0C9044;">
                <i class="ti ti-filter text-xs mr-1"></i>Terapkan
            </button>
        </div>
    </form>

    {{-- COMPARISON CARDS: Promo vs Non-Promo --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Total Orders --}}
        <div class="ca-card">
            <p class="ca-label mb-3"><i class="ti ti-shopping-cart text-sm mr-1"></i>Total Pesanan</p>
            <div class="ca-compare">
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Promo</p>
                    <p class="ca-val" style="color:#0C9044;">{{ number_format($totalPromoOrders) }}</p>
                </div>
                <div class="ca-compare-divider"></div>
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Non-Promo</p>
                    <p class="ca-val">{{ number_format($totalNonPromoOrders) }}</p>
                </div>
            </div>
            @php $orderDiff = $totalNonPromoOrders > 0 ? (($totalPromoOrders - $totalNonPromoOrders) / $totalNonPromoOrders) * 100 : 0; @endphp
            <div class="mt-2 text-center">
                <span class="ca-badge {{ $orderDiff >= 0 ? 'ca-badge-up' : 'ca-badge-down' }}">
                    <i class="ti ti-{{ $orderDiff >= 0 ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                    {{ abs(round($orderDiff, 1)) }}% vs Non-Promo
                </span>
            </div>
        </div>

        {{-- AOV --}}
        <div class="ca-card">
            <p class="ca-label mb-3"><i class="ti ti-receipt text-sm mr-1"></i>Rata-rata Nilai Pesanan (AOV)</p>
            <div class="ca-compare">
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Promo</p>
                    <p class="ca-val-sm" style="color:#0C9044;">Rp {{ number_format($promoAov, 0, ',', '.') }}</p>
                </div>
                <div class="ca-compare-divider"></div>
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Non-Promo</p>
                    <p class="ca-val-sm">Rp {{ number_format($nonPromoAov, 0, ',', '.') }}</p>
                </div>
            </div>
            @php $aovDiff = $nonPromoAov > 0 ? (($promoAov - $nonPromoAov) / $nonPromoAov) * 100 : 0; @endphp
            <div class="mt-2 text-center">
                <span class="ca-badge {{ $aovDiff >= 0 ? 'ca-badge-up' : 'ca-badge-down' }}">
                    <i class="ti ti-{{ $aovDiff >= 0 ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                    {{ abs(round($aovDiff, 1)) }}% vs Non-Promo
                </span>
            </div>
        </div>

        {{-- Repeat Rate --}}
        <div class="ca-card">
            <p class="ca-label mb-3"><i class="ti ti-refresh text-sm mr-1"></i>Tingkat Pembelian Ulang</p>
            <div class="ca-compare">
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Promo</p>
                    <p class="ca-val" style="color:#0C9044;">{{ number_format($promoRepeatRate, 1) }}%</p>
                </div>
                <div class="ca-compare-divider"></div>
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Non-Promo</p>
                    <p class="ca-val">{{ number_format($nonPromoRepeatRate, 1) }}%</p>
                </div>
            </div>
            @php $repeatDiff = $promoRepeatRate - $nonPromoRepeatRate; @endphp
            <div class="mt-2 text-center">
                <span class="ca-badge {{ $repeatDiff >= 0 ? 'ca-badge-up' : 'ca-badge-down' }}">
                    <i class="ti ti-{{ $repeatDiff >= 0 ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                    {{ abs(round($repeatDiff, 1)) }}pp vs Non-Promo
                </span>
            </div>
        </div>

        {{-- Margin --}}
        <div class="ca-card">
            <p class="ca-label mb-3"><i class="ti ti-percentage text-sm mr-1"></i>Margin</p>
            <div class="ca-compare">
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Promo</p>
                    <p class="ca-val {{ $marginAfterPromo >= $marginWithoutPromo ? '' : '' }}"
                       style="color:{{ $marginAfterPromo >= $marginWithoutPromo ? '#0C9044' : '#dc2626' }};">
                        {{ number_format($marginAfterPromo, 1) }}%
                    </p>
                </div>
                <div class="ca-compare-divider"></div>
                <div class="ca-compare-item">
                    <p class="ca-label" style="font-size:.6rem;">Non-Promo</p>
                    <p class="ca-val">{{ number_format($marginWithoutPromo, 1) }}%</p>
                </div>
            </div>
            @php $marginDiff = $marginAfterPromo - $marginWithoutPromo; @endphp
            <div class="mt-2 text-center">
                <span class="ca-badge {{ $marginDiff >= 0 ? 'ca-badge-up' : 'ca-badge-down' }}">
                    <i class="ti ti-{{ $marginDiff >= 0 ? 'arrow-up' : 'arrow-down' }} text-xs"></i>
                    {{ abs(round($marginDiff, 1)) }}pp vs Non-Promo
                </span>
            </div>
        </div>
    </div>

    {{-- PROMO EFFECTIVENESS SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php
            $effectivenessCards = [
                ['label' => 'Revenue Lift', 'key' => 'revenue_lift_pct', 'icon' => 'ti-trending-up'],
                ['label' => 'Margin Impact', 'key' => 'margin_impact_pct', 'icon' => 'ti-chart-pie'],
                ['label' => 'AOV Impact', 'key' => 'aov_impact_pct', 'icon' => 'ti-arrow-autofit-up'],
            ];
        @endphp
        @foreach($effectivenessCards as $ec)
            @php $val = $promoEffectiveness[$ec['key']] ?? 0; @endphp
            <div class="ca-card text-center">
                <div class="inline-flex items-center justify-center w-9 h-9 rounded-xl mb-2"
                     style="background:{{ $val >= 0 ? '#ecfdf5' : '#fef2f2' }};">
                    <i class="ti {{ $ec['icon'] }} text-lg" style="color:{{ $val >= 0 ? '#0C9044' : '#dc2626' }};"></i>
                </div>
                <p class="ca-label mb-1">{{ $ec['label'] }}</p>
                <p class="ca-val" style="color:{{ $val >= 0 ? '#0C9044' : '#dc2626' }};">
                    {{ $val >= 0 ? '+' : '' }}{{ number_format($val, 1) }}%
                </p>
            </div>
        @endforeach
    </div>

    {{-- GROUPED BAR CHART --}}
    <div class="ca-chart-box">
        <h3><i class="ti ti-chart-bar text-base mr-1" style="color:#0C9044;"></i>Perbandingan Promo vs Non-Promo</h3>
        <div style="height:300px;"><canvas id="chartPromoComparison"></canvas></div>
    </div>

    {{-- TOP PROMO PRODUCTS TABLE --}}
    <div class="ca-card">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">
            <i class="ti ti-trophy text-base mr-1" style="color:#0C9044;"></i>Produk Promo Terlaris
        </h3>
        <div class="overflow-x-auto">
            <table class="ca-tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th>Produk</th>
                        <th class="text-right">Revenue (Rp)</th>
                        <th class="text-right">Qty Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPromoProducts as $i => $product)
                        <tr>
                            <td>
                                <span class="ca-rank"
                                      style="background:{{ $i < 3 ? '#ecfdf5' : '#f8fafc' }};color:{{ $i < 3 ? '#0C9044' : '#94a3b8' }};">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="font-medium">{{ $product->name }}</td>
                            <td class="text-right">Rp {{ number_format($product->revenue, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($product->quantity_sold) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-400 py-6">Belum ada data produk promo.</td>
                        </tr>
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
    const ctx = document.getElementById('chartPromoComparison');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['AOV (Rp ribu)', 'Repeat Rate (%)', 'Margin (%)'],
            datasets: [
                {
                    label: 'Promo',
                    data: [
                        {{ round($promoAov / 1000, 1) }},
                        {{ round($promoRepeatRate, 1) }},
                        {{ round($marginAfterPromo, 1) }}
                    ],
                    backgroundColor: '#0C9044',
                    borderRadius: 6,
                    barPercentage: 0.6,
                    categoryPercentage: 0.5
                },
                {
                    label: 'Non-Promo',
                    data: [
                        {{ round($nonPromoAov / 1000, 1) }},
                        {{ round($nonPromoRepeatRate, 1) }},
                        {{ round($marginWithoutPromo, 1) }}
                    ],
                    backgroundColor: '#cbd5e1',
                    borderRadius: 6,
                    barPercentage: 0.6,
                    categoryPercentage: 0.5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 12, weight: '500' }, usePointStyle: true, pointStyle: 'rectRounded', padding: 16 }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12 },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '500' }, color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                }
            }
        }
    });
});
</script>
@endsection
