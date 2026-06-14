@extends('layouts.master')

@section('title', 'Riwayat Produksi Setengah Jadi')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans">

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-lg text-sm flex items-center gap-3 shadow-sm" x-data x-init="setTimeout(() => $el.remove(), 4000)">
        <div class="bg-emerald-100 rounded-full p-1">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-history text-amber-600"></i>
                Riwayat Produksi Setengah Jadi
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Histori proses pembuatan produk setengah jadi</p>
        </div>
        <a href="{{ route('manager.inventory.semi-finished.index') }}"
            class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama produk..."
                    class="h-9 px-3 border border-gray-200 rounded-lg text-sm w-48 focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
                <input type="date" name="from" value="{{ request('from') }}"
                    class="h-9 px-3 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ request('to') }}"
                    class="h-9 px-3 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
            </div>
            <button type="submit" class="h-9 px-4 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition">Filter</button>
            <a href="{{ route('manager.inventory.semi-finished.production-history') }}" class="h-9 px-3 inline-flex items-center text-sm text-gray-500 hover:text-gray-700">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    @if($productions->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-4">
                <i class="ti ti-history text-amber-400 text-2xl"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-700">Belum ada riwayat produksi</h3>
            <p class="text-xs text-gray-400 mt-1">Produksi produk setengah jadi untuk mulai mencatat riwayat.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="text-left px-4 py-3">Tanggal</th>
                            <th class="text-left px-3 py-3">Produk Setengah Jadi</th>
                            <th class="text-left px-3 py-3">PIC</th>
                            <th class="text-right px-3 py-3">Qty Diproduksi</th>
                            <th class="text-right px-3 py-3">Biaya Bahan</th>
                            <th class="text-right px-3 py-3">Upah</th>
                            <th class="text-right px-3 py-3">Total Biaya</th>
                            <th class="text-left px-3 py-3">Bahan Dipakai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($productions as $prod)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $prod->production_date->format('d M Y') }}</td>
                                <td class="px-3 py-3">
                                    <span class="font-semibold text-gray-800">{{ $prod->semiFinishedProduct?->name ?? '-' }}</span>
                                </td>
                                <td class="px-3 py-3 text-gray-600">{{ $prod->pic?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-right font-mono font-semibold text-gray-700">
                                    {{ number_format($prod->quantity_produced, 1) }}
                                    <span class="text-xs text-gray-400">{{ $prod->semiFinishedProduct?->unit?->symbol ?? '' }}</span>
                                </td>
                                <td class="px-3 py-3 text-right font-mono text-gray-600">Rp {{ number_format($prod->material_cost, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right font-mono text-gray-600">Rp {{ number_format($prod->labor_cost, 0, ',', '.') }}</td>
                                <td class="px-3 py-3 text-right font-mono font-semibold text-amber-700">Rp {{ number_format($prod->total_cost, 0, ',', '.') }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($prod->materialUsages as $usage)
                                            <span class="inline-flex px-1.5 py-0.5 rounded bg-gray-100 text-[10px] text-gray-600">
                                                {{ $usage->stock?->name ?? $usage->stock_name }}:
                                                {{ number_format($usage->quantity_used, 2) }} {{ $usage->unit?->symbol ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $productions->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

