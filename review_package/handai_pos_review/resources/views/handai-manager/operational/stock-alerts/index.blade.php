@extends('handai-manager.layouts.master')

@section('title', 'Stock Alerts')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .sa-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .sa-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .sa-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-alert-triangle text-amber-500"></i> Stock Alerts
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Monitoring stok rendah, habis, dan bahan segera kadaluarsa</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.stock-alerts.reorder') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                <i class="ti ti-shopping-cart text-base"></i> Saran Reorder
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Stok Habis</p>
            <p class="text-2xl font-bold {{ $summary['out_of_stock'] > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $summary['out_of_stock'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Stok Rendah</p>
            <p class="text-2xl font-bold {{ $summary['low_stock'] > 0 ? 'text-orange-600' : 'text-gray-400' }} mt-1">{{ $summary['low_stock'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Perlu Reorder</p>
            <p class="text-2xl font-bold {{ $summary['reorder_point'] > 0 ? 'text-amber-600' : 'text-gray-400' }} mt-1">{{ $summary['reorder_point'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Segera Kadaluarsa</p>
            <p class="text-2xl font-bold {{ $summary['expiring_soon'] > 0 ? 'text-purple-600' : 'text-gray-400' }} mt-1">{{ $summary['expiring_soon'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <select name="alert_type" class="sa-input sm:w-48">
                    <option value="">Semua Tipe</option>
                    <option value="out_of_stock" {{ request('alert_type') === 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                    <option value="low_stock" {{ request('alert_type') === 'low_stock' ? 'selected' : '' }}>Stok Rendah</option>
                    <option value="reorder_point" {{ request('alert_type') === 'reorder_point' ? 'selected' : '' }}>Reorder Point</option>
                    <option value="expiring_soon" {{ request('alert_type') === 'expiring_soon' ? 'selected' : '' }}>Kadaluarsa</option>
                </select>
                <select name="alertable_type" class="sa-input sm:w-48">
                    <option value="">Semua Kategori</option>
                    <option value="stock" {{ request('alertable_type') === 'stock' ? 'selected' : '' }}>Bahan Baku</option>
                    <option value="product" {{ request('alertable_type') === 'product' ? 'selected' : '' }}>Produk Jadi</option>
                    <option value="semi_finished" {{ request('alertable_type') === 'semi_finished' ? 'selected' : '' }}>Setengah Jadi</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    <i class="ti ti-search"></i> Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Tipe Alert</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right">Threshold</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $alert->item_name }}</td>
                        <td class="px-4 py-3">
                            @php
                                $catLabel = match($alert->alertable_type) {
                                    'App\\Models\\Stock' => 'Bahan Baku',
                                    'App\\Models\\ProductVariants' => 'Produk',
                                    'App\\Models\\SemiFinishedProduct' => 'Semi-Finished',
                                    'App\\Models\\StockBatch' => 'Batch',
                                    default => 'Lainnya',
                                };
                                $catColor = match($alert->alertable_type) {
                                    'App\\Models\\Stock' => 'bg-blue-50 text-blue-700',
                                    'App\\Models\\ProductVariants' => 'bg-purple-50 text-purple-700',
                                    'App\\Models\\SemiFinishedProduct' => 'bg-cyan-50 text-cyan-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="sa-badge {{ $catColor }}">{{ $catLabel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $typeColor = match($alert->alert_type) {
                                    'out_of_stock' => 'bg-red-50 text-red-700',
                                    'low_stock' => 'bg-orange-50 text-orange-700',
                                    'reorder_point' => 'bg-amber-50 text-amber-700',
                                    'expiring_soon' => 'bg-purple-50 text-purple-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                                $typeLabels = \App\Models\StockAlert::alertTypes();
                            @endphp
                            <span class="sa-badge {{ $typeColor }}">{{ $typeLabels[$alert->alert_type] ?? $alert->alert_type }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $alert->current_quantity <= 0 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ number_format($alert->current_quantity, 1) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ number_format($alert->threshold_quantity, 1) }}</td>
                        <td class="px-4 py-3">
                            @if($alert->status === 'active')
                                <span class="sa-badge bg-red-50 text-red-700">Aktif</span>
                            @else
                                <span class="sa-badge bg-yellow-50 text-yellow-700">Acknowledged</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($alert->status === 'active')
                            <form action="{{ route('manager.operational.stock-alerts.acknowledge', $alert->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs font-medium hover:bg-amber-100 transition">
                                    <i class="ti ti-check"></i> Acknowledge
                                </button>
                            </form>
                            @else
                                <span class="text-xs text-gray-400">{{ $alert->acknowledged_at?->format('d M H:i') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-check-circle text-4xl block mb-2 text-emerald-300"></i>
                            Semua stok dalam kondisi aman! 🎉
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alerts->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $alerts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
