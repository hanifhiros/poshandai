@extends('layouts.master')

@section('title', 'Waste / Basi')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .wst-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .wst-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .wst-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto" x-data="{ showFilter: false }">

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
                <i class="ti ti-trash text-red-500"></i> Waste / Basi
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Catat bahan atau produk yang terbuang, rusak, atau expired</p>
        </div>
        <div class="flex items-center gap-2">
            @include('handai-manager.partials.import-export-modal', ['type' => 'waste', 'label' => 'Waste / Basi'])
            <a href="{{ route('manager.operational.waste.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <i class="ti ti-plus text-base"></i> Catat Waste
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Waste Bulan Ini</p>
            <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalWasteMonth, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Jumlah Kejadian</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalWasteCount) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Alasan Terbanyak</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">
                {{ $topWasteReason ? ($reasons[$topWasteReason->reason] ?? $topWasteReason->reason) : '-' }}
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari item..."
                           class="wst-input">
                </div>
                <select name="reason" class="wst-input sm:w-44">
                    <option value="">Semua Alasan</option>
                    @foreach($reasons as $key => $label)
                    <option value="{{ $key }}" {{ request('reason') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="wst-input sm:w-36" placeholder="Dari">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="wst-input sm:w-36" placeholder="Sampai">
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
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Nilai Kerugian</th>
                        <th class="px-4 py-3">Alasan</th>
                        <th class="px-4 py-3">PIC</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($wasteLogs as $w)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 text-gray-600">{{ $w->waste_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $w->item_name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="wst-badge {{ $w->item_type === 'stock' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                {{ $w->item_type === 'stock' ? 'Bahan' : 'Produk' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700 font-medium">
                            {{ number_format($w->quantity, 1) }} {{ $w->unit->name ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-right text-red-600 font-medium">
                            Rp {{ number_format($w->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="wst-badge
                                {{ $w->reason === 'expired' ? 'bg-orange-50 text-orange-700' : '' }}
                                {{ $w->reason === 'spillage' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                {{ $w->reason === 'quality_reject' ? 'bg-red-50 text-red-700' : '' }}
                                {{ $w->reason === 'damaged' ? 'bg-gray-100 text-gray-700' : '' }}
                                {{ $w->reason === 'other' ? 'bg-slate-100 text-slate-600' : '' }}">
                                {{ $reasons[$w->reason] ?? $w->reason }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $w->pic->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('manager.operational.waste.destroy', $w) }}" method="POST"
                                  onsubmit="return confirm('Hapus record waste ini?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-red-600 transition">
                                    <i class="ti ti-trash text-base"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-trash text-4xl block mb-2"></i>
                            Belum ada catatan waste. Semoga tetap 0!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($wasteLogs->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $wasteLogs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

