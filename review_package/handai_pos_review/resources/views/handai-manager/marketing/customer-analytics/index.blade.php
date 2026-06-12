@extends('handai-manager.layouts.master')
@section('title', 'Customer Analytics')

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
    .ca-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted, #94a3b8); }
    .ca-chart-box {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #f1f5f9);
        box-shadow: var(--card-shadow, 0 1px 3px rgba(0,0,0,.04));
        border-radius: 1rem; padding: 1.25rem;
    }
    .ca-chart-box h3 { font-size: .85rem; font-weight: 600; color: var(--text-primary, #0f172a); margin-bottom: .75rem; }
    .ca-table { width: 100%; font-size: .8rem; border-collapse: collapse; }
    .ca-table thead th {
        text-align: left; font-weight: 600; font-size: .7rem; text-transform: uppercase;
        letter-spacing: .03em; color: var(--text-muted, #94a3b8); padding: .5rem .75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .ca-table tbody td { padding: .55rem .75rem; border-bottom: 1px solid #f8fafc; color: var(--text-primary, #0f172a); }
    .ca-table tbody tr:hover { background: #f8fafc; }
    .ca-rank {
        width: 22px; height: 22px; border-radius: 6px; display: inline-flex;
        align-items: center; justify-content: center; font-size: .65rem; font-weight: 700;
    }
    .ca-badge-segment {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: .65rem; font-weight: 600; padding: 2px 10px; border-radius: 999px;
    }
    .ca-badge-loyal { background: #ecfdf5; color: #059669; }
    .ca-badge-regular { background: #eff6ff; color: #2563eb; }
    .ca-badge-new { background: #fefce8; color: #ca8a04; }
    .ca-badge-at-risk { background: #fef2f2; color: #dc2626; }
    .ca-badge-vip {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: .6rem; font-weight: 700; padding: 1px 7px; border-radius: 999px;
        background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #78350f;
    }
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
    .ca-search-input {
        height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 0 12px 0 36px;
        font-size: .8rem; color: #334155; background: #fff; width: 100%; max-width: 280px;
    }
    .ca-search-input:focus { outline: none; border-color: #0C9044; box-shadow: 0 0 0 3px rgba(12,144,68,.1); }
    .ca-export-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 7px 16px; border-radius: 8px; font-size: .78rem; font-weight: 500;
        border: 1px solid #0C9044; background: #fff; color: #0C9044; cursor: pointer; transition: all .15s;
        text-decoration: none;
    }
    .ca-export-btn:hover { background: #0C9044; color: #fff; }
    .ca-pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .ca-pagination a, .ca-pagination span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px; border-radius: 6px;
        font-size: .75rem; font-weight: 500; text-decoration: none; transition: all .15s;
    }
    .ca-pagination a { border: 1px solid #e2e8f0; color: #475569; background: #fff; }
    .ca-pagination a:hover { border-color: #0C9044; color: #0C9044; }
    .ca-pagination .active span { background: #0C9044; color: #fff; border: 1px solid #0C9044; }
    .ca-pagination .disabled span { color: #cbd5e1; border: 1px solid #f1f5f9; background: #f8fafc; cursor: default; }
</style>
@endsection

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════
         HEADER + PERIOD FILTER
    ═══════════════════════════════════════════════ --}}

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

    {{-- ═══════════════════════════════════════════════
         SUMMARY CARDS
    ═══════════════════════════════════════════════ --}}

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">

        {{-- Total Customer --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Total Customer</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#eff6ff;">
                    <i class="ti ti-users text-base" style="color:#3b82f6;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($totalCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Customer Baru --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Customer Baru</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5;">
                    <i class="ti ti-user-plus text-base" style="color:#0C9044;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($newCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Customer Lama --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Customer Lama</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#f0fdf4;">
                    <i class="ti ti-user-check text-base" style="color:#16a34a;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($returningCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Repeat Customer --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Repeat Customer</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#faf5ff;">
                    <i class="ti ti-repeat text-base" style="color:#7c3aed;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($repeatCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Customer Aktif --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Aktif ≤30 Hari</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#ecfdf5;">
                    <i class="ti ti-activity text-base" style="color:#059669;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($activeCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Customer Tidak Aktif --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Tidak Aktif</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fefce8;">
                    <i class="ti ti-user-pause text-base" style="color:#ca8a04;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($inactiveCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Churned Customer --}}
        <div class="ca-card">
            <div class="flex items-center justify-between mb-3">
                <span class="ca-label">Churned</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#fef2f2;">
                    <i class="ti ti-user-off text-base" style="color:#dc2626;"></i>
                </div>
            </div>
            <p class="ca-val">{{ number_format($churnedCustomers ?? 0, 0, ',', '.') }}</p>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
         SEGMENTATION CHARTS
    ═══════════════════════════════════════════════ --}}

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Segmentasi Frekuensi Beli --}}
        <div class="ca-chart-box">
            <h3><i class="ti ti-chart-donut-3 text-base mr-1" style="color:#3b82f6;"></i>Segmentasi Frekuensi Beli</h3>
            <div style="height:280px; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartFreqSegment"></canvas>
            </div>
            <div id="legendFreqSegment" class="flex flex-wrap gap-3 mt-3 justify-center"></div>
        </div>

        {{-- Segmentasi Total Belanja --}}
        <div class="ca-chart-box">
            <h3><i class="ti ti-chart-donut-3 text-base mr-1" style="color:#0C9044;"></i>Segmentasi Total Belanja</h3>
            <div style="height:280px; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartSpendSegment"></canvas>
            </div>
            <div id="legendSpendSegment" class="flex flex-wrap gap-3 mt-3 justify-center"></div>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════════
         CUSTOMER LIST TABLE + HIGH VALUE SIDEBAR
    ═══════════════════════════════════════════════ --}}

    @php
        $highValueIds = ($highValueCustomers ?? collect())->pluck('id')->toArray();
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">

        {{-- Customer List (3 cols) --}}
        <div class="xl:col-span-3 ca-chart-box" x-data="{ search: '{{ request('search', '') }}' }">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="!mb-0"><i class="ti ti-list text-base mr-1" style="color:#475569;"></i>Daftar Customer</h3>
                <div class="flex items-center gap-3">
                    <form method="GET" action="" class="relative">
                        {{-- Preserve period filters --}}
                        @if(request('period'))
                            <input type="hidden" name="period" value="{{ request('period') }}">
                        @endif
                        @if(request('start_date'))
                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        @endif
                        @if(request('end_date'))
                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        @endif
                        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="search" x-model="search" placeholder="Cari nama atau email..."
                               class="ca-search-input">
                    </form>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="ca-export-btn">
                        <i class="ti ti-download text-sm"></i>Export CSV
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="ca-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th class="text-right">Total Order</th>
                            <th class="text-right">Total Belanja</th>
                            <th>Order Terakhir</th>
                            <th>Segment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($customerList ?? collect()) as $i => $c)
                        @php
                            $cId = $c->id;
                            $isVip = in_array($cId, $highValueIds);
                            $segment = strtolower($c->segment ?? '');
                            $segmentLabel = $c->segment ?? '-';
                            $segmentClass = match(true) {
                                str_contains($segment, 'loyal') => 'ca-badge-loyal',
                                str_contains($segment, 'regular') => 'ca-badge-regular',
                                str_contains($segment, 'new') || str_contains($segment, 'baru') => 'ca-badge-new',
                                str_contains($segment, 'risk') || str_contains($segment, 'churn') => 'ca-badge-at-risk',
                                default => 'ca-badge-regular',
                            };
                            $rowNumber = ($customerList instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                ? $customerList->firstItem() + $i
                                : $i + 1;
                        @endphp
                        <tr>
                            <td class="text-slate-400">{{ $rowNumber }}</td>
                            <td class="font-medium">
                                {{ $c->name ?? '-' }}
                                @if($isVip)
                                    <span class="ca-badge-vip ml-1"><i class="ti ti-star-filled text-xs"></i>VIP</span>
                                @endif
                            </td>
                            <td class="text-slate-500">{{ $c->email ?? '-' }}</td>
                            <td class="text-right font-semibold">{{ number_format($c->total_orders ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right font-semibold" style="color:#0C9044;">Rp {{ number_format($c->total_spent ?? 0, 0, ',', '.') }}</td>
                            <td class="text-slate-500">
                                @if(!empty($c->last_order_date))
                                    {{ \Carbon\Carbon::parse($c->last_order_date)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="ca-badge-segment {{ $segmentClass }}">{{ $segmentLabel }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-8">
                                <i class="ti ti-users-minus text-2xl block mb-2"></i>
                                Belum ada data customer
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($customerList instanceof \Illuminate\Pagination\LengthAwarePaginator && $customerList->hasPages())
            <div class="flex items-center justify-between mt-4 pt-4" style="border-top:1px solid #f1f5f9;">
                <span style="font-size:.75rem;color:#94a3b8;">
                    Menampilkan {{ $customerList->firstItem() }}–{{ $customerList->lastItem() }} dari {{ number_format($customerList->total(), 0, ',', '.') }}
                </span>
                <div class="ca-pagination">
                    @if($customerList->onFirstPage())
                        <span class="disabled"><span><i class="ti ti-chevron-left text-xs"></i></span></span>
                    @else
                        <a href="{{ $customerList->previousPageUrl() }}"><i class="ti ti-chevron-left text-xs"></i></a>
                    @endif

                    @foreach($customerList->getUrlRange(max(1, $customerList->currentPage() - 2), min($customerList->lastPage(), $customerList->currentPage() + 2)) as $page => $url)
                        @if($page == $customerList->currentPage())
                            <span class="active"><span>{{ $page }}</span></span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($customerList->hasMorePages())
                        <a href="{{ $customerList->nextPageUrl() }}"><i class="ti ti-chevron-right text-xs"></i></a>
                    @else
                        <span class="disabled"><span><i class="ti ti-chevron-right text-xs"></i></span></span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- High Value Customers Sidebar --}}
        <div class="xl:col-span-1 ca-chart-box">
            <h3><i class="ti ti-crown text-base mr-1" style="color:#f59e0b;"></i>Top Customer (Belanja Tertinggi)</h3>

            @php
                $rankStyles = [
                    0 => ['bg' => '#fef9c3', 'color' => '#a16207'],
                    1 => ['bg' => '#f1f5f9', 'color' => '#475569'],
                    2 => ['bg' => '#fff7ed', 'color' => '#c2410c'],
                ];
                $rankDefault = ['bg' => '#f8fafc', 'color' => '#94a3b8'];
            @endphp

            <div class="space-y-2">
                @forelse(($highValueCustomers ?? collect()) as $i => $hv)
                @php $rs = $rankStyles[$i] ?? $rankDefault; @endphp
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="ca-rank" style="background:{{ $rs['bg'] }};color:{{ $rs['color'] }};">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" style="color:var(--text-primary, #0f172a);">
                            {{ $hv->name ?? $hv['name'] ?? '-' }}
                        </p>
                        <p style="font-size:.65rem;color:#94a3b8;">
                            {{ number_format($hv->total_orders ?? $hv['total_orders'] ?? 0, 0, ',', '.') }} order
                        </p>
                    </div>
                    <span class="text-sm font-bold" style="color:#0C9044;">
                        Rp {{ number_format($hv->total_spent ?? $hv['total_spent'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="text-center text-slate-400 py-6 text-sm">Belum ada data</p>
                @endforelse
            </div>
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

    const chartColors = ['#0C9044', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#ef4444', '#84cc16'];

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
            }
        }
    };

    function buildCustomLegend(chart, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        const data = chart.data;
        data.labels.forEach(function (label, i) {
            const color = data.datasets[0].backgroundColor[i];
            const value = data.datasets[0].data[i];
            const el = document.createElement('div');
            el.style.cssText = 'display:inline-flex;align-items:center;gap:5px;font-size:.72rem;color:#475569;';
            el.innerHTML = '<span style="width:10px;height:10px;border-radius:3px;background:' + color + ';display:inline-block;"></span>' +
                           '<span>' + label + '</span>' +
                           '<span style="font-weight:600;">(' + value + ')</span>';
            container.appendChild(el);
        });
    }

    // --- Frequency Segment Donut ---
    {{-- frequencySegments is an associative array, convert to collection before extracting keys/values --}}
    const freqLabels = @json(collect($frequencySegments ?? [])->keys());
    const freqData   = @json(collect($frequencySegments ?? [])->values());

    if (freqLabels.length > 0) {
        const freqChart = new Chart(document.getElementById('chartFreqSegment'), {
            type: 'doughnut',
            data: {
                labels: freqLabels,
                datasets: [{
                    data: freqData,
                    backgroundColor: chartColors.slice(0, freqLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                ...chartDefaults,
                cutout: '60%',
                plugins: {
                    ...chartDefaults.plugins,
                    legend: { display: false },
                    tooltip: {
                        ...chartDefaults.plugins.tooltip,
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
        buildCustomLegend(freqChart, 'legendFreqSegment');
    }

    // --- Spend Segment Donut ---
    {{-- spendSegments also associative; use keys/values for labels and data --}}
    const spendLabels = @json(collect($spendSegments ?? [])->keys());
    const spendData   = @json(collect($spendSegments ?? [])->values());

    if (spendLabels.length > 0) {
        const spendChart = new Chart(document.getElementById('chartSpendSegment'), {
            type: 'doughnut',
            data: {
                labels: spendLabels,
                datasets: [{
                    data: spendData,
                    backgroundColor: chartColors.slice(0, spendLabels.length).reverse(),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                ...chartDefaults,
                cutout: '60%',
                plugins: {
                    ...chartDefaults.plugins,
                    legend: { display: false },
                    tooltip: {
                        ...chartDefaults.plugins.tooltip,
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
        buildCustomLegend(spendChart, 'legendSpendSegment');
    }

});
</script>
@endsection
