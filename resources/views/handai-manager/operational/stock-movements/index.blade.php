@extends('handai-manager.layouts.master')

@section('title', 'Stock Movement Log')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .mv-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .mv-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .mv-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1440px] mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <i class="ti ti-transfer text-indigo-600"></i> Stock Movement Log
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Seluruh pergerakan stok masuk & keluar secara real-time</p>
        <div class="mt-2">
            @include('handai-manager.partials.import-export-modal', ['type' => 'stock-movement', 'label' => 'Stock Movement'])
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Pembelian (IN)</p>
            <p class="text-xl font-bold text-green-600 mt-1">Rp {{ number_format($purchaseInMonth, 0, ',', '.') }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Bulan ini</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Produksi (OUT)</p>
            <p class="text-xl font-bold text-orange-600 mt-1">Rp {{ number_format($productionOutMonth, 0, ',', '.') }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Bulan ini</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Penjualan (OUT)</p>
            <p class="text-xl font-bold text-blue-600 mt-1">Rp {{ number_format($saleOutMonth, 0, ',', '.') }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Bulan ini</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Pergerakan</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totalMovementsMonth) }}</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Bulan ini</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari item..."
                           class="mv-input">
                </div>
                <select name="type" class="mv-input sm:w-48">
                    <option value="">Semua Tipe</option>
                    @foreach($movementTypes as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ str_replace('_', ' ', $type) }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="mv-input sm:w-36">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="mv-input sm:w-36">
                <button type="submit"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    <i class="ti ti-search"></i>
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
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Biaya/Unit</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Referensi</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $mv)
                    @php
                        $isIn = str_contains($mv->movement_type, '_IN') || str_contains($mv->movement_type, 'RETURN');
                        $typeColors = [
                            'PURCHASE_IN'    => 'bg-green-50 text-green-700',
                            'PRODUCTION_OUT' => 'bg-orange-50 text-orange-700',
                            'PRODUCTION_IN'  => 'bg-emerald-50 text-emerald-700',
                            'SALE_OUT'       => 'bg-blue-50 text-blue-700',
                            'SALE_RETURN'    => 'bg-cyan-50 text-cyan-700',
                            'EXPIRED_OUT'    => 'bg-red-50 text-red-700',
                            'RND_OUT'        => 'bg-purple-50 text-purple-700',
                            'ADJUSTMENT'     => 'bg-yellow-50 text-yellow-700',
                        ];
                        $colorClass = $typeColors[$mv->movement_type] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                            {{ $mv->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ $mv->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="mv-badge {{ $colorClass }}">
                                <i class="ti {{ $isIn ? 'ti-arrow-down-left' : 'ti-arrow-up-right' }} text-[10px]"></i>
                                {{ str_replace('_', ' ', $mv->movement_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">
                                {{ $mv->stock->name ?? ($mv->productVariant->product->name ?? '-') }}
                            </p>
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $isIn ? 'text-green-600' : 'text-red-600' }}">
                            {{ $isIn ? '+' : '-' }}{{ number_format($mv->quantity, 2) }}
                            <span class="text-gray-400 text-xs">{{ $mv->unit->name ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($mv->cost_per_unit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                            Rp {{ number_format($mv->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $mv->reference_type ?? '-' }}
                            @if($mv->reference_id) #{{ $mv->reference_id }} @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 max-w-[150px] truncate">
                            {{ $mv->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-transfer text-4xl block mb-2"></i>
                            Belum ada pergerakan stok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
