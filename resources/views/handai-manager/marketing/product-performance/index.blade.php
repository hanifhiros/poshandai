@extends('layouts.master')
@section('title', 'Product Performance')

@section('vendor-style')
<style>
    .pp-card {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem;
        padding: 1.25rem;
        transition: box-shadow .2s;
    }
    .pp-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .pp-chart-box {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem; padding: 1.25rem;
    }
    .pp-chart-box h3 { font-size: .85rem; font-weight: 600; color: var(--text-primary, #0f172a); margin-bottom: .75rem; }
    .pp-table { width: 100%; font-size: .8rem; border-collapse: collapse; }
    .pp-table thead th {
        text-align: left; font-weight: 600; font-size: .7rem; text-transform: uppercase;
        letter-spacing: .03em; color: var(--text-muted, #94a3b8); padding: .5rem .75rem;
        border-bottom: 1px solid #f1f5f9; white-space: nowrap;
    }
    .pp-table thead th a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: 2px; }
    .pp-table thead th a:hover { color: #0C9044; }
    .pp-table thead th a.active-sort { color: #0C9044; font-weight: 700; }
    .pp-table tbody td { padding: .55rem .75rem; border-bottom: 1px solid #f8fafc; color: var(--text-primary, #0f172a); }
    .pp-table tbody tr:hover { background: #f8fafc; }
    .pp-rank {
        width: 22px; height: 22px; border-radius: 6px; display: inline-flex;
        align-items: center; justify-content: center; font-size: .65rem; font-weight: 700;
    }
    .pp-badge {
        display: inline-flex; align-items: center;
        font-size: .65rem; font-weight: 600; padding: 2px 10px; border-radius: 999px;
    }
    .pp-badge-green { background: #ecfdf5; color: #059669; }
    .pp-badge-yellow { background: #fefce8; color: #ca8a04; }
    .pp-badge-red { background: #fef2f2; color: #dc2626; }
    .pp-filter-btn {
        padding: 6px 16px; border-radius: 8px; font-size: .78rem; font-weight: 500;
        border: 1px solid #e2e8f0; background: #fff; color: #475569; cursor: pointer; transition: all .15s;
    }
    .pp-filter-btn:hover { border-color: #0C9044; color: #0C9044; }
    .pp-filter-btn.active { background: #0C9044; color: #fff; border-color: #0C9044; }
    .pp-date-input {
        height: 34px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0 10px;
        font-size: .78rem; color: #334155; background: #fff;
    }
    .pp-select {
        height: 34px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0 10px;
        font-size: .78rem; color: #334155; background: #fff; cursor: pointer; appearance: auto;
    }
    .pp-select:focus { outline: none; border-color: #0C9044; box-shadow: 0 0 0 3px rgba(12,144,68,.1); }
    @media (max-width: 768px) {
        .pp-hide-mobile { display: none; }
    }
</style>
@endsection

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         HEADER + FILTERS
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

    <form method="GET" action="" class="pp-card flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-slate-600 mr-1">
            <i class="ti ti-chart-bar text-base align-middle mr-1"></i>Periode
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
                class="pp-filter-btn {{ ($period ?? 'this_month') === $key ? 'active' : '' }}"
                @if($key !== 'custom') onclick="this.form.querySelectorAll('.pp-date-input').forEach(i => i.disabled = true);" @endif
            >{{ $label }}</button>
        @endforeach

        <div class="flex items-center gap-2 ml-auto flex-wrap">
            <input type="date" name="start_date" value="{{ isset($startDate) ? $startDate->format('Y-m-d') : '' }}"
                   class="pp-date-input" placeholder="Mulai">
            <span class="text-xs text-slate-400">â€”</span>
            <input type="date" name="end_date" value="{{ isset($endDate) ? $endDate->format('Y-m-d') : '' }}"
                   class="pp-date-input" placeholder="Akhir">

            <select name="sort" class="pp-select">
                <option value="revenue" {{ ($sort ?? 'revenue') === 'revenue' ? 'selected' : '' }}>Sort: Revenue</option>
                <option value="margin" {{ ($sort ?? '') === 'margin' ? 'selected' : '' }}>Sort: Margin</option>
                <option value="quantity" {{ ($sort ?? '') === 'quantity' ? 'selected' : '' }}>Sort: Quantity</option>
            </select>

            <select name="category" class="pp-select">
                <option value="">Semua Kategori</option>
                @foreach(($categories ?? collect()) as $cat)
                    <option value="{{ $cat->id }}" {{ ($categoryFilter ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" name="period" value="custom"
                    class="pp-filter-btn active" style="background:#0C9044;color:#fff;border-color:#0C9044;">
                <i class="ti ti-filter text-xs mr-1"></i>Terapkan
            </button>
        </div>
    </form>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         PRODUCT TABLE
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

    @php
        $currentParams = request()->except('sort');
        $sortParam = $sort ?? 'revenue';
    @endphp

    <div class="pp-chart-box">
        <h3><i class="ti ti-package text-base mr-1" style="color:#0C9044;"></i>Performa Produk</h3>

        <div class="overflow-x-auto">
            <table class="pp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th class="pp-hide-mobile">Kategori</th>
                        <th class="text-right">
                            <a href="{{ request()->fullUrlWithQuery(array_merge($currentParams, ['sort' => 'revenue'])) }}"
                               class="{{ $sortParam === 'revenue' ? 'active-sort' : '' }}">
                                Revenue (Rp) <i class="ti ti-arrows-sort text-xs"></i>
                            </a>
                        </th>
                        <th class="text-right pp-hide-mobile">Kontribusi (%)</th>
                        <th class="text-right">
                            <a href="{{ request()->fullUrlWithQuery(array_merge($currentParams, ['sort' => 'margin'])) }}"
                               class="{{ $sortParam === 'margin' ? 'active-sort' : '' }}">
                                Gross Margin (%) <i class="ti ti-arrows-sort text-xs"></i>
                            </a>
                        </th>
                        <th class="text-right">
                            <a href="{{ request()->fullUrlWithQuery(array_merge($currentParams, ['sort' => 'quantity'])) }}"
                               class="{{ $sortParam === 'quantity' ? 'active-sort' : '' }}">
                                Qty Terjual <i class="ti ti-arrows-sort text-xs"></i>
                            </a>
                        </th>
                        <th class="text-right pp-hide-mobile">Repeat Buyers</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($products ?? collect()) as $i => $p)
                    @php
                        $margin = $p->gross_margin ?? 0;
                        $badgeClass = match(true) {
                            $margin >= 50 => 'pp-badge-green',
                            $margin >= 20 => 'pp-badge-yellow',
                            default       => 'pp-badge-red',
                        };
                    @endphp
                    <tr>
                        <td class="text-slate-400">{{ $i + 1 }}</td>
                        <td class="font-medium">{{ $p->name ?? '-' }}</td>
                        <td class="text-slate-500 pp-hide-mobile">{{ $p->category_name ?? '-' }}</td>
                        <td class="text-right font-semibold" style="color:#0C9044;">
                            Rp {{ number_format($p->revenue ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right pp-hide-mobile">
                            {{ number_format($p->contribution_pct ?? 0, 1, ',', '.') }}%
                        </td>
                        <td class="text-right">
                            <span class="pp-badge {{ $badgeClass }}">
                                {{ number_format($margin, 1, ',', '.') }}%
                            </span>
                        </td>
                        <td class="text-right font-semibold">
                            {{ number_format($p->quantity_sold ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right pp-hide-mobile">
                            {{ number_format($p->repeat_buyer_count ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-400 py-8">
                            <i class="ti ti-package-off text-2xl block mb-2"></i>
                            Belum ada data produk
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         INSIGHT CARDS (2Ã—2 Grid)
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

    @php
        $rankStyles = [
            0 => ['bg' => '#fef9c3', 'color' => '#a16207'],
            1 => ['bg' => '#f1f5f9', 'color' => '#475569'],
            2 => ['bg' => '#fff7ed', 'color' => '#c2410c'],
        ];
        $rankDefault = ['bg' => '#f8fafc', 'color' => '#94a3b8'];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Top Margin Products --}}
        <div class="pp-chart-box">
            <h3><i class="ti ti-trending-up text-base mr-1" style="color:#059669;"></i>Top Margin Produk</h3>
            <div class="space-y-2">
                @forelse(($topMarginProducts ?? collect()) as $i => $item)
                @php $rs = $rankStyles[$i] ?? $rankDefault; @endphp
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="pp-rank" style="background:{{ $rs['bg'] }};color:{{ $rs['color'] }};">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium truncate" style="color:var(--text-primary, #0f172a);">{{ $item->name ?? '-' }}</span>
                    <span class="pp-badge pp-badge-green">{{ number_format($item->gross_margin ?? 0, 1, ',', '.') }}%</span>
                </div>
                @empty
                <p class="text-center text-slate-400 py-6 text-sm">Belum ada data</p>
                @endforelse
            </div>
        </div>

        {{-- Low Margin Products --}}
        <div class="pp-chart-box" style="border-color:#fef2f2;">
            <h3><i class="ti ti-trending-down text-base mr-1" style="color:#dc2626;"></i>Low Margin Produk</h3>
            <div class="space-y-2">
                @forelse(($lowMarginProducts ?? collect()) as $i => $item)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-red-50/50 transition-colors">
                    <span class="pp-rank" style="background:#fef2f2;color:#dc2626;">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium truncate" style="color:var(--text-primary, #0f172a);">{{ $item->name ?? '-' }}</span>
                    <span class="pp-badge pp-badge-red">{{ number_format($item->gross_margin ?? 0, 1, ',', '.') }}%</span>
                </div>
                @empty
                <p class="text-center text-slate-400 py-6 text-sm">Belum ada data</p>
                @endforelse
            </div>
        </div>

        {{-- Most Repurchased Products --}}
        <div class="pp-chart-box">
            <h3><i class="ti ti-repeat text-base mr-1" style="color:#7c3aed;"></i>Produk Paling Sering Dibeli Ulang</h3>
            <div class="space-y-2">
                @forelse(($mostRepurchasedProducts ?? collect()) as $i => $item)
                @php $rs = $rankStyles[$i] ?? $rankDefault; @endphp
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="pp-rank" style="background:{{ $rs['bg'] }};color:{{ $rs['color'] }};">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium truncate" style="color:var(--text-primary, #0f172a);">{{ $item->name ?? '-' }}</span>
                    <span class="text-xs font-semibold" style="color:#7c3aed;">{{ number_format($item->repeat_buyer_count ?? 0, 0, ',', '.') }} buyers</span>
                </div>
                @empty
                <p class="text-center text-slate-400 py-6 text-sm">Belum ada data</p>
                @endforelse
            </div>
        </div>

        {{-- Rarely Bought Products --}}
        <div class="pp-chart-box">
            <h3><i class="ti ti-alert-triangle text-base mr-1" style="color:#94a3b8;"></i>Produk Jarang Dibeli</h3>
            <div class="space-y-2">
                @forelse(($rarelyBoughtProducts ?? collect()) as $i => $item)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="pp-rank" style="background:#f8fafc;color:#94a3b8;">{{ $i + 1 }}</span>
                    <span class="flex-1 text-sm font-medium truncate" style="color:#94a3b8;">{{ $item->name ?? '-' }}</span>
                    <span class="text-xs font-semibold" style="color:#94a3b8;">{{ number_format($item->quantity_sold ?? 0, 0, ',', '.') }} qty</span>
                </div>
                @empty
                <p class="text-center text-slate-400 py-6 text-sm">Belum ada data</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         HORIZONTAL BAR CHART â€“ Product Contribution
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}

    <div class="pp-chart-box">
        <h3><i class="ti ti-chart-bar text-base mr-1" style="color:#0C9044;"></i>Product Contribution (%)</h3>
        <div style="height:360px;">
            <canvas id="chartContribution"></canvas>
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

    const top10 = @json(($products ?? collect())->take(10)->map(fn($p) => ['name' => $p->name, 'pct' => $p->contribution_pct])->values());

    if (top10.length === 0) return;

    const labels = top10.map(p => p.name);
    const data   = top10.map(p => parseFloat(p.pct) || 0);

    new Chart(document.getElementById('chartContribution'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Kontribusi (%)',
                data: data,
                backgroundColor: '#0C9044',
                borderRadius: 6,
                barThickness: 22,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: 'Poppins', size: 11 },
                        color: '#94a3b8',
                        callback: v => v + '%'
                    }
                },
                y: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Poppins', size: 11 },
                        color: '#334155',
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.92)',
                    titleFont: { family: 'Poppins', size: 12 },
                    bodyFont: { family: 'Poppins', size: 11 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ctx.parsed.x.toFixed(1) + '%'
                    }
                }
            }
        }
    });

});
</script>
@endsection

