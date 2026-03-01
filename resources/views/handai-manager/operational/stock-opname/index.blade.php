@extends('handai-manager.layouts.master')

@section('title', 'Stock Opname')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .opn-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .opn-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .opn-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
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
                <i class="ti ti-clipboard-check text-amber-600"></i> Stock Opname
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Rekonsiliasi stok fisik vs stok sistem (audit inventory)</p>
        </div>
        <a href="{{ route('manager.operational.stock-opname.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-medium hover:bg-amber-700 transition shadow-sm">
            <i class="ti ti-plus text-base"></i> Buat Opname Baru
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Adjustment Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalAdjMonth) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Surplus (Lebih)</p>
            <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format(abs($surplusMonth), 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Defisit (Kurang)</p>
            <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format(abs($deficitMonth), 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari item / no. adjustment..."
                           class="opn-input">
                </div>
                <select name="status" class="opn-input sm:w-40">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
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
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">No. Adjustment</th>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3 text-right">Stok Sistem</th>
                        <th class="px-4 py-3 text-right">Stok Aktual</th>
                        <th class="px-4 py-3 text-right">Selisih</th>
                        <th class="px-4 py-3 text-right">Dampak Biaya</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($adjustments as $adj)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 text-gray-600">{{ $adj->adjustment_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $adj->adjustment_number ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $adj->item_name }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($adj->system_qty, 2) }} {{ $adj->unit->name ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($adj->actual_qty, 2) }} {{ $adj->unit->name ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $adj->difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $adj->difference >= 0 ? '+' : '' }}{{ number_format($adj->difference, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $adj->total_cost_impact >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format(abs($adj->total_cost_impact), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusMap = [
                                    'draft' => 'bg-gray-100 text-gray-600',
                                    'approved' => 'bg-blue-50 text-blue-700',
                                    'completed' => 'bg-green-50 text-green-700',
                                ];
                            @endphp
                            <span class="opn-badge {{ $statusMap[$adj->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($adj->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-clipboard-check text-4xl block mb-2"></i>
                            Belum ada stock opname.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $adjustments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
