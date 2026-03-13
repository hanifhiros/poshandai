@extends('handai-manager.layouts.master')

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

    /* new KPI card style */
    .kpi-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        transition: box-shadow .2s;
    }
    .kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .kpi-title { font-size: .75rem; color: #64748b; font-weight: 600; text-transform: uppercase; }
    .kpi-value { font-size: 1.75rem; font-weight: 700; color: #111827; margin-top: .25rem; }
    .kpi-trend { font-size: .65rem; font-weight: 600; }
    .kpi-trend-positive { color: #059669; }
    .kpi-trend-negative { color: #dc2626; }
    .kpi-trend-neutral { color: #64748b; }
    .kpi-icon { font-size: 1.25rem; color: inherit; }
</style>
@endsection

@section('content')
<div class="container-xl">
    {{-- Header + filter --}}
    <div class="mk-card mb-4">
        <div class="d-flex justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="page-title mb-1"><i class="ti ti-chart-dots text-indigo-500"></i> Dashboard KPI Operasional</h2>
                <p class="text-muted">Ringkasan performa operasional</p>
            </div>
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
            </form>
        </div>
    </div>

    {{-- Sales Overview --}}
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="mk-card {{ $kpis['sales']['total_orders'] > 100 ? 'bg-green-50' : '' }}" title="Jumlah pesanan dalam rentang waktu">
                <div class="flex justify-between items-center">
                    <div>
                        <i class="ti ti-receipt text-indigo-500 text-2xl"></i>
                        <div class="mk-label">Total Pesanan</div>
                        <div class="mk-val">{{ number_format($kpis['sales']['total_orders']) }}</div>
                    </div>
                    <canvas id="chartOrders" height="60" class="w-24"></canvas>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="mk-card {{ $kpis['sales']['total_revenue'] > 10000000 ? 'bg-green-50' : '' }}" title="Total pendapatan kotor">
                <div class="flex justify-between items-center">
                    <div>
                        <i class="ti ti-coin" text-indigo-500 text-2xl"></i>
                        <div class="mk-label">Total Pendapatan</div>
                        <div class="mk-val">Rp {{ number_format($kpis['sales']['total_revenue'], 0, ',', '.') }}</div>
                    </div>
                    <div class="w-16">
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width:{{ min(100, ($kpis['sales']['total_revenue']/1000000)*100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="mk-card" title="Nilai rata-rata per transaksi">
                <div class="flex justify-between items-center">
                    
                    <div>
                        <i class="ti ti-average" text-indigo-500 text-2xl"></i>
                        <div class="mk-label">Rata-rata Order</div>
                        <div class="mk-val">Rp {{ number_format($kpis['sales']['avg_order_value'], 0, ',', '.') }}</div>
                    </div>
                    <div class="w-16">
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width:{{ min(100, ($kpis['sales']['avg_order_value']/500000)*100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

      <div class="row row-deck row-cards mb-3">
          <div class="col-sm-6 col-lg-6">
              <div class="mk-chart-box">
                  <h3>Retur vs Pesanan</h3>
                  <canvas id="chartReturnRatio" height="120"></canvas>
              </div>
          </div>
          <div class="col-sm-6 col-lg-6">
              <div class="mk-chart-box">
                  <h3>Kehadiran</h3>
                  <canvas id="chartAttendance" height="120"></canvas>
              </div>
          </div>
      </div>

    {{-- Inventory & Returns --}}
    <div class="row row-deck row-cards mb-3">
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card" title="Total nilai stok gudang">
                  <i class="ti ti-package text-2xl text-green-500" title="Total Stok"></i>
                  <div class="mk-label">Nilai Total Stok</div>
                  <div class="mk-val">Rp {{ number_format($kpis['inventory']['total_stock_value'], 0, ',', '.') }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['inventory']['active_alerts'] > 0 ? 'bg-yellow-50' : '' }}" title="Jumlah alert stok">
                  <i class="ti ti-bell-ringing text-2xl text-yellow-500" title="Alert Aktif"></i>
                  <div class="mk-label">Alert Aktif</div>
                  <div class="mk-val">{{ $kpis['inventory']['active_alerts'] }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['inventory']['low_stock_count'] > 0 ? 'bg-yellow-50' : '' }}" title="Produk yang stoknya rendah">
                  <i class="ti ti-alert-circle text-2xl text-yellow-600" title="Stok Rendah"></i>
                  <div class="mk-label">Stok Rendah</div>
                  <div class="mk-val">{{ $kpis['inventory']['low_stock_count'] }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['inventory']['out_of_stock_count'] > 0 ? 'bg-red-50' : '' }}" title="Produk yang stoknya habis">
                  <i class="ti ti-ban text-2xl text-red-500" title="Stok Habis"></i>       
                  <div class="mk-label">Stok Habis</div>
                  <div class="mk-val">{{ $kpis['inventory']['out_of_stock_count'] }}</div>
              </div>
          </div>
      </div>
      <div class="row row-deck row-cards mb-3">
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-receipt-refund text-2xl text-indigo-500"></i>
                  <div class="mk-label">Total Retur</div>
                  <div class="mk-val">{{ $kpis['returns']['total_returns'] }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['returns']['return_rate'] > 5 ? 'bg-red-50' : ($kpis['returns']['return_rate'] > 2 ? 'bg-yellow-50' : '') }}" title="Persentase retur terhadap pesanan">
                  <i class="ti ti-repeat text-2xl text-indigo-500" title="Return Rate"></i>
                  <div class="mk-label">Return Rate</div>
                  <div class="mk-val">{{ $kpis['returns']['return_rate'] }}%</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-coin text-2xl text-indigo-500"></i>
                  <div class="mk-label">Total Refund</div>
                  <div class="mk-val">Rp {{ number_format($kpis['returns']['total_refunded'], 0, ',', '.') }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-timer text-2xl text-indigo-500"></i>
                  <div class="mk-label">Menunggu Proses</div>
                  <div class="mk-val">{{ $kpis['returns']['pending_returns'] }}</div>
              </div>
          </div>

    {{-- Attendance & Maintenance --}}
    <div class="row row-deck row-cards mb-3">
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-clock text-2xl text-green-500"></i>
                  <div class="mk-label">Total Kehadiran</div>
                  <div class="mk-val">{{ $kpis['attendance']['total_records'] }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['attendance']['on_time_rate'] >= 90 ? 'bg-green-50' : ($kpis['attendance']['on_time_rate'] >= 75 ? 'bg-yellow-50' : 'bg-red-50') }}" title="Persentase kehadiran tepat waktu">
                  <i class="ti ti-check text-2xl text-green-500" title="Tepat Waktu"></i>
                  <div class="mk-label">Tepat Waktu</div>
                  <div class="mk-val">{{ $kpis['attendance']['on_time_rate'] }}%</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['attendance']['late_count'] > 0 ? 'bg-red-50' : '' }}" title="Jumlah pegawai terlambat">
                  <i class="ti ti-alert-circle text-2xl text-yellow-500" title="Terlambat"></i>
                  <div class="mk-label">Terlambat</div>
                  <div class="mk-val">{{ $kpis['attendance']['late_count'] }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card {{ $kpis['attendance']['total_overtime_hours'] > 10 ? 'bg-yellow-50' : '' }}" title="Jumlah jam lembur">
                  <i class="ti ti-timer text-2xl text-indigo-500" title="Lembur"></i>
                  <div class="mk-label">Lembur (jam)</div>
                  <div class="mk-val">{{ $kpis['attendance']['total_overtime_hours'] }}</div>
              </div>
          </div>
      </div>
      <div class="row row-deck row-cards mb-3">
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-tool text-2xl text-green-500"></i>
                  <div class="mk-label">Total Perawatan</div>
                  <div class="mk-val">{{ $kpis['maintenance']['total_maintenance'] }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-activity text-2xl text-green-500"></i>
                  <div class="mk-label">Uptime Equip.</div>
                  <div class="mk-val">{{ $kpis['maintenance']['equipment_uptime'] }}%</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-coin text-2xl text-red-500"></i>
                  <div class="mk-label">Biaya Maint.</div>
                  <div class="mk-val">Rp {{ number_format($kpis['maintenance']['total_cost'], 0, ',', '.') }}</div>
              </div>
          </div>
          <div class="col-sm-6 col-lg-3">
              <div class="mk-card">
                  <i class="ti ti-calendar-event text-2xl text-red-500"></i>
                  <div class="mk-label">Jadwal Overdue</div>
                  <div class="mk-val">{{ $kpis['maintenance']['overdue_schedules'] }}</div>
              </div>
          </div>
      </div>
        </div>
    </div>
</div>

{{-- include Chart.js CDN and render small sparklines --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const ordersChart = document.getElementById('chartOrders');
        if(ordersChart) {
            new Chart(ordersChart, {
                type: 'line',
                data: {
                    labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                    datasets: [{
                        data: [12, 19, 3, 5, 2, 3, {{ $kpis['sales']['total_orders'] }}],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.2)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { x:{display:false}, y:{display:false} },
                    plugins: { legend:{display:false}, tooltip:{enabled:false} }
                }
            });
        }
        const prodChart = document.getElementById('chartProduction');
        if(prodChart){
            new Chart(prodChart, {
                type:'bar',
                data:{ labels:[''], datasets:[{ data:[{{ $kpis['production']['completion_rate'] ?? 0 }}], backgroundColor:['#059669'] }]},
                // if production module removed, completion_rate defaults to 0

                options:{responsive:true, maintainAspectRatio:false, scales:{x:{display:false},y:{display:false}}, plugins:{legend:{display:false}, tooltip:{enabled:false}}}
            });
        }

        // additional charts
        const returnChart = document.getElementById('chartReturnRatio');
        if(returnChart){
            const orders = {{ $kpis['sales']['total_orders'] }};
            const returns = {{ $kpis['returns']['total_returns'] }};
            new Chart(returnChart, {
                type:'pie',
                data:{ labels:['Orders','Returns'], datasets:[{ data:[orders - returns, returns], backgroundColor:['#6366f1','#f87171'] }]},
                options:{responsive:true, plugins:{legend:{position:'bottom'}}}
            });
        }
        const attendanceChart = document.getElementById('chartAttendance');
        if(attendanceChart){
            new Chart(attendanceChart, {
                type:'bar',
                data:{ labels:['On Time','Late'], datasets:[{ data:[{{ $kpis['attendance']['on_time_rate'] }}, {{ $kpis['attendance']['late_count'] }}], backgroundColor:['#059669','#facc15'] }]},
                options:{responsive:true, maintainAspectRatio:false, scales:{x:{grid:{display:false}}, y:{beginAtZero:true}}, plugins:{legend:{display:false}, tooltip:{enabled:true}}}
            });
        }
    });
</script>
@endpush
@endsection
