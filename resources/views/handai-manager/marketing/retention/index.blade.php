@extends('handai-manager.layouts.master')
@section('title', 'Retention & Loyalty')

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

        {{-- Repeat Purchase Rate --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Persentase pelanggan yang melakukan pembelian ulang">Repeat Purchase Rate</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5;">
                    <i class="ti ti-repeat text-base" style="color:#0C9044;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($repeatPurchaseRate ?? 0, 1) }}%</p>
            <div class="flex items-center gap-2 mt-2">
                @php $rrc = $repeatRateChange ?? 0; @endphp
                <span class="mk-badge {{ $rrc >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $rrc >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($rrc), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- Retention Rate --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Persentase pelanggan yang tetap aktif dari periode sebelumnya">Retention Rate</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff;">
                    <i class="ti ti-user-check text-base" style="color:#3b82f6;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($retentionRate ?? 0, 1) }}%</p>
            <div class="flex items-center gap-2 mt-2">
                @php $rtc = $retentionRateChange ?? 0; @endphp
                <span class="mk-badge {{ $rtc >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $rtc >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($rtc), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- Churn Rate (inverted: up = bad) --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Persentase pelanggan yang berhenti bertransaksi">Churn Rate</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fef2f2;">
                    <i class="ti ti-user-minus text-base" style="color:#dc2626;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($churnRate ?? 0, 1) }}%</p>
            <div class="flex items-center gap-2 mt-2">
                @php $crc = $churnRateChange ?? 0; @endphp
                {{-- Churn up = bad (red), churn down = good (green) --}}
                <span class="mk-badge {{ $crc <= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $crc <= 0 ? 'ti-arrow-down-right' : 'ti-arrow-up-right' }} text-xs"></i>
                    {{ number_format(abs($crc), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

        {{-- APF (Average Purchase Frequency) --}}
        <div class="mk-card">
            <div class="flex items-center justify-between mb-3">
                <span class="mk-label" title="Rata-rata frekuensi pembelian per pelanggan">APF</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#faf5ff;">
                    <i class="ti ti-shopping-cart text-base" style="color:#7c3aed;"></i>
                </div>
            </div>
            <p class="mk-val">{{ number_format($apf ?? 0, 1) }}x</p>
            <div class="flex items-center gap-2 mt-2">
                @php $ac = $apfChange ?? 0; @endphp
                <span class="mk-badge {{ $ac >= 0 ? 'mk-badge-up' : 'mk-badge-down' }}">
                    <i class="ti {{ $ac >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                    {{ number_format(abs($ac), 1) }}%
                </span>
                <span style="font-size:.62rem;color:#94a3b8;">vs periode lalu</span>
            </div>
        </div>

    </div>

    {{-- Period Comparison Chart --}}
    <div class="mk-chart-box">
        <h3><i class="ti ti-chart-bar text-base mr-1" style="color:#0C9044;"></i>Perbandingan Periode</h3>
        <div style="height:300px;"><canvas id="chartPeriodComparison"></canvas></div>
    </div>

    {{-- Auto Insights --}}
    @if(!empty($insights) && count($insights))
    <div class="mk-alert-box" style="background:#eff6ff;border:1px solid #bfdbfe;">
        <div class="flex items-center gap-2 mb-2">
            <i class="ti ti-bulb text-lg" style="color:#2563eb;"></i>
            <span class="font-semibold text-sm" style="color:#1e3a5f;">Insight Otomatis</span>
        </div>
        <ul class="space-y-1 pl-5 list-disc" style="color:#1e40af;">
            @foreach($insights as $insight)
                <li>{{ $insight }}</li>
            @endforeach
        </ul>
    </div>
    @endif

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
            tooltip: {
                backgroundColor: 'rgba(15,23,42,0.92)',
                titleFont: { family: 'Poppins', size: 12 },
                bodyFont: { family: 'Poppins', size: 11 },
                padding: 10,
                cornerRadius: 8,
                displayColors: true,
            }
        }
    };

    const labels   = @json($periodComparison['labels'] ?? []);
    const current  = @json($periodComparison['current'] ?? []);
    const previous = @json($periodComparison['previous'] ?? []);

    new Chart(document.getElementById('chartPeriodComparison'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Periode Ini',
                    data: current,
                    backgroundColor: '#0C9044',
                    borderRadius: 6,
                    maxBarThickness: 40,
                },
                {
                    label: 'Periode Lalu',
                    data: previous,
                    backgroundColor: '#cbd5e1',
                    borderRadius: 6,
                    maxBarThickness: 40,
                }
            ]
        },
        options: {
            ...chartDefaults,
            plugins: {
                ...chartDefaults.plugins,
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12, boxHeight: 12, borderRadius: 3,
                        font: { family: 'Poppins', size: 11 },
                        color: '#64748b',
                        padding: 16,
                    }
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: 'Poppins' }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8' },
                    beginAtZero: true,
                }
            }
        }
    });

});
</script>
@endsection
