@extends('handai-manager.layouts.master')


@section('title', 'Dashboard')

@section('vendor-style')

@endsection

@section('page-style')

    @vite('resources/css/handai-reseller-index.css')

@endsection

@section('content')

<div class="max-w-screen-xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-4 ">Dashboard</h1>
        <p>Selamat datang di dashboard</p>

        <div class="mb-4">
            <form method="GET">
                <label for="store_id">Pilih Toko:</label>
                <select name="store_id" onchange="this.form.submit()" class="border p-2 rounded">
                    <option value="all" {{ $selectedStoreId === 'all' ? 'selected' : '' }}>All Stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $selectedStoreId == $store->id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        
        <div class="bg-white p-5 shadow rounded">
            <h3 class="text-sm text-gray-500">Total Penjualan</h3>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
        
        <div class="bg-white p-5 shadow rounded mt-4">
            <h3 class="text-sm text-gray-500">Total Pesanan</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $totalOrders }}</p>
        </div>
        
        
        @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 shadow rounded">
                <h3 class="text-sm text-gray-500">Total Penjualan</h3>
                <p class="value-text">
                    <span class="text-sm align-top">Rp</span>
                    <span class="text-2xl md:text-2xl lg:text-3xl font-bold">
                        {{ \App\Helpers\NumberFormatter::short($totalSales) }}
                    </span>
                </p>
                <p class="text-green-500 text-sm">0%</p>
            </div>
            <div class="bg-white p-5 shadow rounded">
                <h3 class="text-sm text-gray-500">Transaksi</h3>
                <p class="value-text">
                    <span class="text-2xl md:text-2xl lg:text-3xl font-bold">
                        {{ \App\Helpers\NumberFormatter::short($totalTransaction) }}
                    </span>
                </p>
                <p class="text-green-500 text-sm">0%</p>
            </div>

            <div class="bg-white p-5 shadow rounded">
                <h3 class="text-sm text-gray-500">Laba Bersih</h3>
               {{-- di dalam value-text --}}
            <p class="value-text">
                <span class="text-sm align-top">Rp</span>
                <span class="text-2xl md:text-2xl lg:text-3xl font-bold">
                    {{ \App\Helpers\NumberFormatter::short($LabaBersih) }}
                </span>
            </p>
                <p class="text-green-500 text-sm">0%</p>
            </div>
        </section>
        @endif
        <!-- Analytics Section -->
        
        <section class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-4">

            <!-- Produk Terlaris -->
            <!-- Produk Terlaris Toggle -->
<div class="bg-white p-5 shadow rounded">
    <h3 class="text-lg font-semibold">Produk Terlaris</h3>

    <!-- Tombol Switch -->
    <div class="flex gap-2 mt-4 mb-4">
        <button onclick="switchPie('bulanan')" id="btn-pie-bulanan" class="bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded">
            Bulan Ini
        </button>
        <button onclick="switchPie('semua')" id="btn-pie-semua" class="text-gray-700 px-3 py-1 rounded hover:bg-gray-100">
            Semua Waktu
        </button>
    </div>

    <!-- Tab Bulan Ini (Default Tampil) -->
    <div id="pie-bulanan" class="chart-container">
        @if($produkTerlarisBulanIni->count() > 0)
            <canvas id="pieChartBulanIni" class="w-full h-64"></canvas>
        @else
            <p class="text-gray-500">Belum ada data bulan ini.</p>
        @endif
    </div>

    <!-- Tab Semua Waktu (Tersembunyi) -->
    <div id="pie-semua" class="chart-container hidden">
        @if($produkTerlarisSemua->count() > 0)
            <canvas id="pieChartSemua" class="w-full h-64"></canvas>
        @else
            <p class="text-gray-500">Belum ada data semua waktu.</p>
        @endif
    </div>
    
</div>      
            {{-- <!-- Stok Minimum -->
            <div class="bg-white p-6 shadow-md rounded-xl border border-[#E2E8F0]">
                <h3 class="text-xl font-semibold mb-1 flex items-center gap-2 text-[#0C9044]">
                    <span>📦</span> Stok Minimum
                </h3>
                <p class="text-gray-500 text-sm mb-4">Menampilkan stok dengan jumlah kurang dari 20 unit.</p>
            
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left border-separate border-spacing-0">
                        <thead class="bg-white text-xs font-semibold uppercase text-[#0C9044] border-b border-[#E2E8F0]">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-lg">Nama Barang</th>
                                <th class="px-4 py-3 text-center">Jumlah</th>
                                <th class="px-4 py-3 text-center rounded-tr-lg">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-800">
                            @forelse($stocksMinimum as $stock)
                                <tr class="transition hover:bg-[#F6FFF8] rounded-lg">
                                    <td class="px-4 py-3 border-t border-[#F0F0F0] first:rounded-bl-lg">{{ $stock->name }}</td>
                                    <td class="px-4 py-3 text-center border-t border-[#F0F0F0]">{{ $stock->unit_qty }} {{ $stock->unit->symbol }}</td>
                                    <td class="px-4 py-3 text-center font-semibold border-t border-[#F0F0F0] 
                                        {{ $stock->unit_qty == 0 ? 'text-red-600' : 'text-yellow-500' }}">
                                        {{ $stock->unit_qty == 0 ? 'Habis' : 'Menipis' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-400 py-4">Tidak ada stok yang menipis.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            
                <div class="mt-4">
                    {{ $stocksMinimum->links('vendor.pagination.custom-tailwind') }}
                </div>
            </div> --}}
            
            

            <!-- Pesanan Perlu Dikirim -->
            <div class="bg-white p-6 shadow-md rounded-xl border border-[#E2E8F0]">
                <h3 class="text-xl font-semibold mb-1 flex items-center gap-2 text-[#0C9044]">
                    📬 Pesanan Perlu Dikirim
                </h3>
                <p class="text-gray-500 text-sm mb-4">Daftar pesanan yang belum dikirim ke pelanggan.</p>
            
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto text-sm text-left border-separate border-spacing-0">
                        <thead class="bg-white text-xs font-semibold uppercase text-[#0C9044] tracking-wider border-b border-[#E2E8F0]">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-lg">Nama Customer</th>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center rounded-tr-lg">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-800">
                            @forelse($pendingOrders as $order)
                                <tr class="hover:bg-[#F6FFF8] transition rounded-lg">
                                    <td class="px-4 py-3 border-t border-[#F0F0F0] first:rounded-bl-lg">{{ $order->customer_name }}</td>
                                    <td class="px-4 py-3 border-t border-[#F0F0F0]">{{ $order->product_name }}</td>
                                    <td class="px-4 py-3 text-center border-t border-[#F0F0F0]">
                                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-400 py-4">
                                        Belum ada pesanan yang perlu dikirim.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            
                <div class="mt-4">
                    {{ $pendingOrders->links('vendor.pagination.custom-tailwind') }}
                </div>
            </div>
                        
        </section>

        <!-- Total Penjualan Chart -->
        @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
        <div class="bg-white p-5 shadow rounded mt-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-semibold">📈 Total Penjualan</h3>
                    <p class="text-sm text-gray-500">Lihat berdasarkan: Hari / Minggu / Tahun</p>
                </div>
                <div>
                    <select id="filterWaktu" onchange="updateSalesChart()" class="border rounded px-2 py-1 text-sm">
                        <option value="minggu" selected>Minggu</option>
                        <option value="hari">Hari</option>
                        <option value="bulan">Bulan</option> <!-- tambahkan ini -->
                        <option value="tahun">Tahun</option>
                    </select>
                    
                </div>
            </div>
        
            <div class="overflow-x-auto">
                <div class="min-w-[600px] md:min-w-[800px] lg:min-w-[950px] h-[220px]">
                    <canvas id="salesChart" class="w-full h-full"></canvas>
                </div>
                <div class="flex justify-between mt-4">
                    <button onclick="prevBatch()" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
                        ⬅️ Sebelumnya
                    </button>
                    <span id="chartRangeLabel" class="text-sm text-gray-600">-</span>
                    <button onclick="nextBatch()" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-sm">
                        Selanjutnya ➡️
                    </button>
                </div>
                
                
            </div>

            
        </div>
        @endif
        
        
        
    </div>
     





@endsection

@section('vendor-script')
@endsection
@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartColors = [
    "#0C9044", // Handai Green (utama)
    "#0FAD57", // Handai Green terang
    "#34D399", // Emerald Soft
    "#6EE7B7", // Minty Breeze
    "#A7F3D0", // Soft Mint
    "#064E3B", // Deep Forest Green
    "#10B981", // Teal Green
    "#16A34A", // Grass Green
    "#4ADE80", // Fresh Leaf
    "#86EFAC"  // Light Lime
];




    const chartLabels = {
        hari: {!! json_encode($penjualanHarian->pluck('tanggal')) !!},
        minggu: {!! json_encode($penjualanMingguan->pluck('minggu_ke')->map(fn($m) => 'Minggu ' . $m)) !!},
        bulan: {!! json_encode($penjualanBulanan->pluck('bulan')) !!},
        tahun: {!! json_encode($penjualanTahunan->pluck('tahun')) !!}
    };

    const chartData = {
        hari: {!! json_encode($penjualanHarian->pluck('total_penjualan')) !!},
        minggu: {!! json_encode($penjualanMingguan->pluck('total_penjualan')) !!},
        bulan: {!! json_encode($penjualanBulanan->pluck('total_penjualan')) !!},
        tahun: {!! json_encode($penjualanTahunan->pluck('total_penjualan')) !!}
    };

    let salesChart = null;
    let chartType = 'hari';
    let currentBatchIndex = 0;
    const batchSizes = { hari: 7, minggu: 4, bulan: 6, tahun: 5 };

    function getBatchData(type, index) {
        const labels = chartLabels[type];
        const data = chartData[type];
        const size = batchSizes[type];

        const start = Math.max(0, labels.length - (index + 1) * size);
        const end = labels.length - index * size;

        return {
            labels: labels.slice(start, end),
            data: data.slice(start, end),
            range: `${labels[start] || '-'} - ${labels[end - 1] || '-'}`
        };
    }

    function renderSalesChart(type = chartType, index = currentBatchIndex) {
        chartType = type;
        currentBatchIndex = index;

        const ctx = document.getElementById('salesChart').getContext('2d');
        if (salesChart) salesChart.destroy();

        const { labels, data, range } = getBatchData(type, index);
        document.getElementById('chartRangeLabel').innerText = range;

        salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Total Penjualan',
                    data,
                    backgroundColor: chartColors,
                    borderRadius: 10,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => 'Rp' + new Intl.NumberFormat().format(val)
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    function updateSalesChart() {
        const selected = document.getElementById('filterWaktu').value;
        currentBatchIndex = 0;
        renderSalesChart(selected);
    }

    function prevBatch() {
        currentBatchIndex++;
        renderSalesChart(chartType, currentBatchIndex);
    }

    function nextBatch() {
        if (currentBatchIndex > 0) {
            currentBatchIndex--;
            renderSalesChart(chartType, currentBatchIndex);
        }
    }

    // PIE CHARTS
    let chartBulanan = null, chartSemua = null;

    function switchPie(tab) {
        const bulanan = document.getElementById('pie-bulanan');
        const semua = document.getElementById('pie-semua');
        const btnBulanan = document.getElementById('btn-pie-bulanan');
        const btnSemua = document.getElementById('btn-pie-semua');

        bulanan.classList.toggle('hidden', tab !== 'bulanan');
        semua.classList.toggle('hidden', tab !== 'semua');

        btnBulanan.classList.toggle('bg-indigo-100', tab === 'bulanan');
        btnSemua.classList.toggle('bg-indigo-100', tab === 'semua');

        if (tab === 'bulanan' && !chartBulanan) {
            renderPieChart('pieChartBulanIni', {!! json_encode($produkTerlarisBulanIni->pluck('name')) !!}, {!! json_encode($produkTerlarisBulanIni->pluck('total')) !!}, 'bulanan');
        } else if (tab === 'semua' && !chartSemua) {
            renderPieChart('pieChartSemua', {!! json_encode($produkTerlarisSemua->pluck('name')) !!}, {!! json_encode($produkTerlarisSemua->pluck('total')) !!}, 'semua');
        }
    }

    function renderPieChart(id, labels, data, type) {
        const ctx = document.getElementById(id).getContext('2d');
        const colors = chartColors;

        const chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors.slice(0, data.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.chart._metasets[0].total;
                                const percent = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });

        if (type === 'bulanan') chartBulanan = chart;
        else chartSemua = chart;
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderSalesChart('minggu'); // default awal
        switchPie('bulanan');       // default awal
    });
</script>
@endsection
