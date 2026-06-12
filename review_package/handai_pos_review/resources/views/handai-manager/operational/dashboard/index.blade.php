@extends('handai-manager.layouts.master')
@section('title', 'Operational Dashboard')

@section('vendor-style')
<style>
    /* ERP Style Variables */
    :root {
        --inv-bg: #f1f5f9;
        --inv-card: #ffffff;
        --inv-border: #e2e8f0;
        --inv-muted: #94a3b8;
        --inv-text: #0f172a;
        --inv-secondary: #475569;
        --inv-accent: #0C9044;
        --inv-accent-light: #ecfdf5;
        --inv-accent-hover: #0a7a3a;
        --inv-success: #10b981;
        --inv-warn: #f59e0b;
        --inv-danger: #ef4444;
        --inv-radius: 12px;
        --inv-shadow: 0 1px 3px 0 rgba(0,0,0,.04), 0 1px 2px -1px rgba(0,0,0,.04);
        --inv-shadow-hover: 0 10px 25px -5px rgba(0,0,0,.06), 0 4px 10px -6px rgba(0,0,0,.04);
    }

    body { background: var(--inv-bg); }

    .op-card {
        background: var(--inv-card);
        border: 1px solid var(--inv-border);
        box-shadow: var(--inv-shadow);
        border-radius: var(--inv-radius);
        padding: 1.25rem;
        transition: all .2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .op-card:hover { box-shadow: var(--inv-shadow-hover); transform: translateY(-1px); }
    
    .op-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .op-card-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--inv-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .op-card-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    
    .op-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--inv-text);
        line-height: 1.2;
    }
    
    .op-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 20px;
    }
    .op-badge-up { background: var(--inv-accent-light); color: var(--inv-accent); }
    .op-badge-down { background: #fef2f2; color: var(--inv-danger); }
    .op-badge-neutral { background: #f8fafc; color: var(--inv-muted); }

    .op-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.85rem; }
    .op-table thead th {
        text-align: left; padding: 10px 12px; font-size: 0.7rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .04em; color: var(--inv-muted);
        border-bottom: 1px solid var(--inv-border);
    }
    .op-table tbody td {
        padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .op-table tbody tr:hover { background: var(--inv-accent-light); }
    .op-table tbody tr:last-child td { border-bottom: none; }
    
    .op-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 34px; padding: 0 14px; font-size: 0.8rem; font-weight: 600;
        border-radius: 8px; border: none; cursor: pointer; transition: all .15s ease;
    }
    .op-btn-primary { background: var(--inv-accent); color: #fff; }
    .op-btn-primary:hover { background: var(--inv-accent-hover); }
    .op-btn-outline { background: #fff; color: var(--inv-secondary); border: 1px solid var(--inv-border); }
    .op-btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

</style>
@endsection

@section('content')
<div class="py-6 px-4 md:px-6 lg:px-8 max-w-[1600px] mx-auto">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Operational Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Ringkasan aktivitas produksi, logistik, dan persediaan hari ini.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('manager.inventory.stock') }}" class="op-btn op-btn-outline">
                <i class="ti ti-packages"></i> Kelola Stok
            </a>
            <a href="{{ route('manager.operational.produksi.create') }}" class="op-btn op-btn-primary">
                <i class="ti ti-plus"></i> Produksi Baru
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Produksi Hari Ini -->
        <div class="op-card">
            <div class="op-card-header">
                <span class="op-card-title">Produksi Hari Ini</span>
                <div class="op-card-icon bg-blue-50 text-blue-600">
                    <i class="ti ti-assembly"></i>
                </div>
            </div>
            <div class="op-value">{{ number_format($prodToday) }} <span class="text-sm font-normal text-gray-400">Batch</span></div>
            <div class="mt-auto pt-4 flex items-center">
                @if($prodGrowth > 0)
                    <span class="op-badge op-badge-up"><i class="ti ti-arrow-upRight"></i> {{ number_format($prodGrowth, 1) }}%</span>
                @elseif($prodGrowth < 0)
                    <span class="op-badge op-badge-down"><i class="ti ti-arrow-downRight"></i> {{ number_format(abs($prodGrowth), 1) }}%</span>
                @else
                    <span class="op-badge op-badge-neutral"><i class="ti ti-minus"></i> 0%</span>
                @endif
                <span class="text-xs text-gray-400 ml-2">vs Kemarin</span>
            </div>
        </div>

        <!-- Pesanan Pending -->
        <div class="op-card">
            <div class="op-card-header">
                <span class="op-card-title">Pesanan Menunggu</span>
                <div class="op-card-icon bg-amber-50 text-amber-600">
                    <i class="ti ti-clock-hour-4"></i>
                </div>
            </div>
            <div class="op-value">{{ number_format($pendingOrders) }}</div>
            <div class="mt-auto pt-4">
                <a href="{{ route('manager.operational.orders.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700 inline-flex items-center gap-1">
                    Lihat antrean <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Waste / Basi -->
        <div class="op-card">
            <div class="op-card-header">
                <span class="op-card-title">Waste/Loss Bln Ini</span>
                <div class="op-card-icon bg-red-50 text-red-600">
                    <i class="ti ti-trash"></i>
                </div>
            </div>
            <div class="op-value text-[1.4rem]">Rp {{ number_format($wasteThisMonth, 0, ',', '.') }}</div>
            <div class="mt-auto pt-4">
                 <a href="{{ route('manager.operational.waste.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700 inline-flex items-center gap-1">
                    Log Pembuangan <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- R&D Aktif -->
        <div class="op-card">
            <div class="op-card-header">
                <span class="op-card-title">R&D Berjalan</span>
                <div class="op-card-icon bg-purple-50 text-purple-600">
                    <i class="ti ti-flask"></i>
                </div>
            </div>
            <div class="op-value">{{ number_format($activeRnD) }}</div>
            <div class="mt-auto pt-4">
                <a href="{{ route('manager.operational.rnd') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700 inline-flex items-center gap-1">
                    Cek progres <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Stok Menipis & Stock Movements -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="op-card">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <div class="w-2 h-6 bg-amber-400 rounded-sm"></div> Peringatan Stok
                    </h3>
                    <a href="{{ route('manager.inventory.stock') }}?status=low_stock" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Sisa Stok</th>
                                <th>Min. Stok</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStocks as $l)
                            <tr>
                                <td class="font-medium text-gray-800">{{ $l->name }}</td>
                                <td><span class="font-bold {{ $l->unit_qty <= 0 ? 'text-red-600' : 'text-amber-600' }}">{{ current(explode('.', $l->unit_qty)) }}</span> {{ $l->unit->symbol ?? '' }}</td>
                                <td class="text-gray-500">{{ current(explode('.', $l->min_stock)) }} {{ $l->unit->symbol ?? '' }}</td>
                                <td>
                                    @if($l->unit_qty <= 0)
                                        <span class="px-2 py-1 text-[10px] font-bold bg-red-100 text-red-700 rounded-lg">HABIS</span>
                                    @else
                                        <span class="px-2 py-1 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-lg">MENIPIS</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('manager.inventory.stock.batch.create', $l->id) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800 underline">Restock</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-400 text-sm">Semua stok berada dalam batas aman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="op-card">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <div class="w-2 h-6 bg-blue-400 rounded-sm"></div> Mutasi Terkini
                    </h3>
                    <a href="{{ route('manager.operational.stock-movements.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Lihat Riwayat</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="op-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Tipe Mutasi</th>
                                <th class="text-right">Qty</th>
                                <th>Catatan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements as $mov)
                            <tr>
                                <td class="font-medium text-gray-800">
                                    {{ $mov->stock ? $mov->stock->name : ($mov->productVariant ? ($mov->productVariant->product->name ?? '').' ('.$mov->productVariant->variantSummary().')' : $mov->item_name) }}
                                </td>
                                <td>
                                    <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 font-medium">
                                        {{ str_replace('_', ' ', strtoupper($mov->movement_type)) }}
                                    </span>
                                </td>
                                <td class="text-right font-mono font-bold {{ $mov->quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($mov->quantity, 2, '.', ''), '0'), '.') }}
                                </td>
                                <td class="text-sm text-gray-500 truncate max-w-[150px]" title="{{ $mov->remarks }}">{{ $mov->remarks ?: '-' }}</td>
                                <td class="text-xs text-gray-400">{{ $mov->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-gray-400 text-sm">Belum ada pergerakan stok.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Column: Shortcuts & Helpers -->
        <div class="space-y-6">
            <div class="op-card">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <div class="w-2 h-6 bg-emerald-400 rounded-sm"></div> Aksi Cepat
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('manager.inventory.stock.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 rounded-xl transition border border-gray-100 hover:border-emerald-200">
                        <i class="ti ti-cube text-xl mb-1 mt-1"></i>
                        <span class="text-xs font-semibold">Tmb B.Baku</span>
                    </a>
                    <a href="{{ route('manager.inventory.stock-batches.create') ?? '#' }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 rounded-xl transition border border-gray-100 hover:border-emerald-200">
                        <i class="ti ti-shopping-cart-plus text-xl mb-1 mt-1"></i>
                        <span class="text-xs font-semibold">Belanja Stok</span>
                    </a>
                    <a href="{{ route('manager.operational.stock-opname.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 rounded-xl transition border border-gray-100 hover:border-emerald-200">
                        <i class="ti ti-checklist text-xl mb-1 mt-1"></i>
                        <span class="text-xs font-semibold">Stock Opname</span>
                    </a>
                    <a href="{{ route('manager.operational.waste.create') }}" class="flex flex-col items-center justify-center p-4 bg-gray-50 hover:bg-red-50 hover:text-red-700 text-gray-600 rounded-xl transition border border-gray-100 hover:border-red-200">
                        <i class="ti ti-trash-x text-xl mb-1 mt-1"></i>
                        <span class="text-xs font-semibold">Catat Waste</span>
                    </a>
                </div>
            </div>

            <div class="op-card bg-gradient-to-br from-slate-800 to-slate-900 border-none text-white !shadow-xl">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-emerald-400">
                        <i class="ti ti-bulb text-xl"></i>
                    </div>
                </div>
                <h4 class="font-bold text-lg mb-2">Tips Efisiensi Operasional</h4>
                <p class="text-sm text-slate-300 leading-relaxed mb-4">Pastikan stock opname dilakukan minimal 1x setiap minggu untuk menghindari selisih berlebih antara data sistem & riil gudang.</p>
                <a href="{{ route('manager.operational.stock-opname.index') }}" class="text-sm font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1 transition">
                    Riwayat Opname <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection