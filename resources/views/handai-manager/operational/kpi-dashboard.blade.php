@extends('layouts.master')

@section('title', 'KPI Operasional')

@section('page-style')
@vite('resources/css/handai-manager-inventory-stock.css')
@vite('resources/css/handai-manager-finance-dashboard.css')
@endsection

@section('vendor-style')
<style>
    :root {
        --kpi-glass: rgba(255,255,255,0.7);
        --kpi-border: rgba(148,163,184,0.35);
        --kpi-shadow: 0 10px 30px rgba(15,23,42,.08);
        --kpi-shadow-soft: 0 6px 18px rgba(15,23,42,.06);
        --kpi-accent: #0C9044;
        --kpi-accent-soft: rgba(12,144,68,0.12);
        --kpi-muted: #94a3b8;
        --kpi-text: #0f172a;
    }

    body { background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }

    .kpi-hero {
        background: linear-gradient(135deg, rgba(12,144,68,0.08), rgba(255,255,255,0.82));
        border: 1px solid var(--kpi-border);
        border-radius: 18px;
        box-shadow: var(--kpi-shadow-soft);
        overflow: hidden;
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--kpi-muted);
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--kpi-text);
        letter-spacing: -0.02em;
    }
    .kpi-note { font-size: 12px; color: var(--kpi-muted); }
    .kpi-progress {
        width: 100%;
        height: 6px;
        background: rgba(148,163,184,0.2);
        border-radius: 999px;
        overflow: hidden;
    }
    .kpi-progress > span {
        display: block;
        height: 100%;
        background: var(--kpi-accent);
        border-radius: 999px;
    }
    .kpi-chart-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .kpi-actions { display: inline-flex; align-items: center; gap: 8px; }
    .kpi-actions .inv-btn { white-space: nowrap; }
    .mk-chart-box {
        background: var(--kpi-glass);
        border: 1px solid var(--kpi-border);
        box-shadow: var(--kpi-shadow-soft);
        border-radius: 16px;
        padding: 18px;
        overflow: hidden;
        backdrop-filter: blur(14px);
    }
    .mk-chart-box h3 {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--kpi-text);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .mk-chart-sub {
        font-size: 0.72rem;
        color: var(--kpi-muted);
        margin-top: 4px;
    }
    .kpi-actions .inv-btn { flex-shrink: 0; }
    .kpi-card-grid > *,
    .kpi-stat-grid > *,
    .kpi-chart-grid > * {
        min-width: 0;
    }
    .kpi-chart-grid .fc,
    .kpi-card-grid .inv-card-hover,
    .kpi-stat-grid .stat-card {
        overflow: hidden;
    }
    .kpi-chart-grid canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }
    .kpi-trend {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
    }
    .kpi-trend canvas { flex: 0 0 96px; }
    .inv-card-hover,
    .stat-card,
    .fc {
        background: var(--kpi-glass);
        border: 1px solid var(--kpi-border);
        box-shadow: var(--kpi-shadow-soft);
        backdrop-filter: blur(14px);
    }
    .inv-card-hover:hover,
    .stat-card:hover {
        box-shadow: var(--kpi-shadow);
    }
    .section-title h2 { color: var(--kpi-text); }
    .section-title .bar { height: 18px; }
</style>
@endsection

@section('content')
<div class="py-8 px-4 md:px-6 lg:px-8 max-w-[1600px] mx-auto" style="min-height:100vh;font-family:'Poppins','Public Sans',sans-serif">
    <div class="kpi-hero mb-6 p-5 fade-in-up">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2.5" style="color:var(--kpi-text);letter-spacing:-0.02em">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:var(--kpi-accent-soft)">
                        <i class="ti ti-chart-dots" style="color:var(--kpi-accent)"></i>
                    </div>
                    Dashboard KPI Operasional
                </h1>
                <p class="text-sm mt-1" style="color:var(--kpi-muted)">Periode {{ $startDate }} sampai {{ $endDate }}</p>
            </div>
            <form method="GET" class="flex flex-wrap gap-2 items-center">
                <input type="date" name="start_date" class="inv-input" value="{{ $startDate }}">
                <input type="date" name="end_date" class="inv-input" value="{{ $endDate }}">
                <button type="submit" class="inv-btn inv-btn-primary">
                    <i class="ti ti-filter"></i> Filter
                </button>
            </form>
        </div>
    </div>

    <div class="section-title fade-in-up fade-delay-1">
        <div class="bar" style="background:var(--inv-accent)"></div>
        <h2>Ringkasan Penjualan</h2>
    </div>
    <div class="kpi-card-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6 fade-in-up fade-delay-1">
        <div class="inv-card-hover p-5" title="Jumlah pesanan dalam rentang waktu">
            <div class="flex items-center justify-between mb-3">
                <span class="kpi-label" style="color:var(--inv-info)">Total Pesanan</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50">
                    <i class="ti ti-receipt text-blue-600"></i>
                </div>
            </div>
            <p class="kpi-value tabular-nums">{{ number_format($kpis['sales']['total_orders']) }} <span class="text-sm font-normal" style="color:var(--inv-muted)">order</span></p>
            <div class="kpi-trend mt-3">
                <span class="kpi-note">Trend 7 hari (estimasi)</span>
                <canvas id="chartOrders" height="44" class="w-28"></canvas>
            </div>
        </div>
        <div class="inv-card-hover p-5" title="Total pendapatan kotor">
            <div class="flex items-center justify-between mb-3">
                <span class="kpi-label" style="color:var(--inv-success)">Total Pendapatan</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50">
                    <i class="ti ti-coin text-emerald-600"></i>
                </div>
            </div>
            <p class="kpi-value tabular-nums">Rp {{ number_format($kpis['sales']['total_revenue'], 0, ',', '.') }}</p>
            <div class="mt-3">
                <div class="kpi-note">Target bulanan Rp 10.000.000</div>
                <div class="kpi-progress">
                    <span style="width: {{ min(100, ($kpis['sales']['total_revenue'] / 10000000) * 100) }}%"></span>
                </div>
            </div>
        </div>
        <div class="inv-card-hover p-5" title="Nilai rata-rata per transaksi">
            <div class="flex items-center justify-between mb-3">
                <span class="kpi-label" style="color:var(--inv-secondary)">Rata-rata Order</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-slate-100">
                    <i class="ti ti-average text-slate-600"></i>
                </div>
            </div>
            <p class="kpi-value tabular-nums">Rp {{ number_format($kpis['sales']['avg_order_value'], 0, ',', '.') }}</p>
            <div class="mt-3">
                <div class="kpi-note">Target rata-rata Rp 500.000</div>
                <div class="kpi-progress">
                    <span style="width: {{ min(100, ($kpis['sales']['avg_order_value'] / 500000) * 100) }}%"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="kpi-chart-grid grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6 fade-in-up fade-delay-2">
        <div class="mk-chart-box">
            <div class="kpi-chart-head">
                <div>
                    <h3><i class="ti ti-chart-pie" style="color:#0C9044;"></i> Retur vs Pesanan</h3>
                    <div class="mk-chart-sub">Perbandingan retur terhadap total order.</div>
                </div>
                <div class="kpi-actions">
                    <a class="inv-btn inv-btn-outline inv-btn-sm" href="{{ route('manager.operational.returns.index') }}" title="Lihat daftar retur">
                        <i class="ti ti-repeat"></i> Retur
                    </a>
                    <a class="inv-btn inv-btn-outline inv-btn-sm" href="{{ route('manager.inventory.stock') }}" title="Cek stok terkait pengembalian">
                        <i class="ti ti-packages"></i> Stok
                    </a>
                </div>
            </div>
            <div style="height:220px"><canvas id="chartReturnRatio"></canvas></div>
        </div>
        <div class="mk-chart-box">
            <div class="kpi-chart-head">
                <div>
                    <h3><i class="ti ti-chart-bar" style="color:#3b82f6;"></i> Kehadiran (persentase)</h3>
                    <div class="mk-chart-sub">Rasio tepat waktu vs terlambat.</div>
                </div>
                <div class="kpi-actions">
                    <a class="inv-btn inv-btn-outline inv-btn-sm" href="{{ route('manager.operational.attendance.summary') }}" title="Lihat ringkasan kehadiran">
                        <i class="ti ti-chart-bar"></i> Ringkasan
                    </a>
                    <a class="inv-btn inv-btn-outline inv-btn-sm" href="{{ route('manager.operational.attendance.schedule') }}" title="Kelola jadwal kehadiran">
                        <i class="ti ti-calendar"></i> Jadwal
                    </a>
                </div>
            </div>
            <div style="height:220px"><canvas id="chartAttendance"></canvas></div>
        </div>
    </div>

    <div class="section-title fade-in-up fade-delay-2">
        <div class="bar" style="background:var(--inv-info)"></div>
        <h2>Inventory</h2>
    </div>
    <div class="kpi-stat-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6 fade-in-up fade-delay-2">
        <div class="stat-card" title="Total nilai stok gudang">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Nilai Total Stok</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i class="ti ti-package text-emerald-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">Rp {{ number_format($kpis['inventory']['total_stock_value'], 0, ',', '.') }}</p>
            <p class="kpi-note">Nilai akumulasi stok saat ini</p>
        </div>
        <div class="stat-card" title="Jumlah alert stok">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Alert Aktif</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="ti ti-bell-ringing text-amber-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['inventory']['active_alerts'] }}</p>
            <p class="kpi-note">Butuh pengecekan ulang</p>
        </div>
        <div class="stat-card" title="Produk yang stoknya rendah">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Stok Rendah</span>
                <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center">
                    <i class="ti ti-alert-circle text-orange-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['inventory']['low_stock_count'] }}</p>
            <p class="kpi-note">Prioritas pembelian ulang</p>
        </div>
        <div class="stat-card" title="Produk yang stoknya habis">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Stok Habis</span>
                <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
                    <i class="ti ti-ban text-red-500"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['inventory']['out_of_stock_count'] }}</p>
            <p class="kpi-note">Perlu tindakan segera</p>
        </div>
    </div>

    <div class="section-title fade-in-up fade-delay-3">
        <div class="bar" style="background:var(--inv-warn)"></div>
        <h2>Retur</h2>
    </div>
    <div class="kpi-stat-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6 fade-in-up fade-delay-3">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Total Retur</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <i class="ti ti-receipt-refund text-indigo-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['returns']['total_returns'] }}</p>
            <p class="kpi-note">Jumlah retur terverifikasi</p>
        </div>
        <div class="stat-card" title="Persentase retur terhadap pesanan">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Return Rate</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="ti ti-repeat text-amber-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['returns']['return_rate'] }}%</p>
            <p class="kpi-note">Ideal di bawah 2%</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Total Refund</span>
                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center">
                    <i class="ti ti-coin text-slate-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">Rp {{ number_format($kpis['returns']['total_refunded'], 0, ',', '.') }}</p>
            <p class="kpi-note">Nilai pengembalian dana</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Menunggu Proses</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <i class="ti ti-timer text-indigo-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['returns']['pending_returns'] }}</p>
            <p class="kpi-note">Perlu tindak lanjut</p>
        </div>
    </div>

    <div class="section-title fade-in-up fade-delay-4">
        <div class="bar" style="background:var(--inv-success)"></div>
        <h2>Kehadiran</h2>
    </div>
    <div class="kpi-stat-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6 fade-in-up fade-delay-4">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Total Kehadiran</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i class="ti ti-clock text-emerald-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['attendance']['total_records'] }}</p>
            <p class="kpi-note">Jumlah catatan presensi</p>
        </div>
        <div class="stat-card" title="Persentase kehadiran tepat waktu">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Tepat Waktu</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i class="ti ti-check text-emerald-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['attendance']['on_time_rate'] }}%</p>
            <p class="kpi-note">Target minimum 90%</p>
        </div>
        <div class="stat-card" title="Jumlah pegawai terlambat">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Terlambat</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="ti ti-alert-circle text-amber-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['attendance']['late_count'] }}</p>
            <p class="kpi-note">Perlu evaluasi shift</p>
        </div>
        <div class="stat-card" title="Jumlah jam lembur">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Lembur (jam)</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <i class="ti ti-timer text-indigo-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['attendance']['total_overtime_hours'] }}</p>
            <p class="kpi-note">Pantau beban kerja</p>
        </div>
    </div>

    <div class="section-title fade-in-up fade-delay-5">
        <div class="bar" style="background:var(--inv-danger)"></div>
        <h2>Maintenance</h2>
    </div>
    <div class="kpi-stat-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 fade-in-up fade-delay-5">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Total Perawatan</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i class="ti ti-tool text-emerald-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['maintenance']['total_maintenance'] }}</p>
            <p class="kpi-note">Tugas perawatan selesai</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Uptime Equip.</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <i class="ti ti-activity text-emerald-600"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['maintenance']['equipment_uptime'] }}%</p>
            <p class="kpi-note">Kinerja peralatan</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Biaya Maint.</span>
                <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
                    <i class="ti ti-coin text-red-500"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">Rp {{ number_format($kpis['maintenance']['total_cost'], 0, ',', '.') }}</p>
            <p class="kpi-note">Total biaya periode ini</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Jadwal Overdue</span>
                <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
                    <i class="ti ti-calendar-event text-red-500"></i>
                </div>
            </div>
            <p class="text-xl font-bold mt-3 tabular-nums" style="color:var(--inv-text)">{{ $kpis['maintenance']['overdue_schedules'] }}</p>
            <p class="kpi-note">Butuh reschedule</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ordersChart = document.getElementById('chartOrders');
        const totalOrders = {{ $kpis['sales']['total_orders'] }};
        if (ordersChart) {
            const base = Math.max(Math.round(totalOrders / 7), 0);
            const orderSeries = [0.6, 0.75, 0.7, 0.85, 0.8, 0.9, 1].map(mult => Math.max(Math.round(base * mult), 0));

            new Chart(ordersChart, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        data: orderSeries,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,0.2)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x: { display: false }, y: { display: false } },
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }

        const returnChart = document.getElementById('chartReturnRatio');
        if (returnChart) {
            const orders = Math.max({{ $kpis['sales']['total_orders'] }}, 0);
            const returns = Math.max({{ $kpis['returns']['total_returns'] }}, 0);
            const baseOrders = Math.max(orders - returns, 0);
            const hasReturnData = orders > 0 || returns > 0;

            new Chart(returnChart, {
                type: 'pie',
                data: {
                    labels: hasReturnData ? ['Orders', 'Returns'] : ['No data', 'Returns'],
                    datasets: [{
                        data: hasReturnData ? [baseOrders, returns] : [1, 0],
                        backgroundColor: ['#0C9044', '#ca8a04']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        const attendanceChart = document.getElementById('chartAttendance');
        if (attendanceChart) {
            const totalRecords = Math.max({{ $kpis['attendance']['total_records'] }}, 0);
            const onTimeRate = Math.max(Math.min({{ $kpis['attendance']['on_time_rate'] }}, 100), 0);
            const lateCount = Math.max({{ $kpis['attendance']['late_count'] }}, 0);
            const lateRate = totalRecords > 0 ? Math.round((lateCount / totalRecords) * 100) : 0;

            new Chart(attendanceChart, {
                type: 'bar',
                data: {
                    labels: ['On Time', 'Late'],
                    datasets: [{
                        data: [onTimeRate, lateRate],
                        backgroundColor: ['#3b82f6', '#ca8a04'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, max: 100, ticks: { callback: value => value + '%' } }
                    },
                    plugins: { legend: { display: false }, tooltip: { enabled: true } }
                }
            });
        }
    });
</script>
@endpush
@endsection

