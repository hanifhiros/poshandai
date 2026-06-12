@extends('handai-manager.layouts.master')

@section('title', 'Inspeksi QC')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-clipboard-check text-cyan-500"></i> Riwayat Inspeksi
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.qc.dashboard') }}"
               class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
                <i class="ti ti-arrow-left"></i> Dashboard
            </a>
            <a href="{{ route('manager.operational.qc.inspections.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 text-white rounded-xl text-sm font-medium hover:bg-cyan-700 transition shadow-sm">
                <i class="ti ti-plus"></i> Inspeksi Baru
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <form action="{{ route('manager.operational.qc.inspections.index') }}" method="GET"
              class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500 block mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500"
                       placeholder="Item / nomor inspeksi...">
            </div>
            <div class="w-36">
                <label class="text-xs text-gray-500 block mb-1">Tipe</label>
                <select name="type" class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Semua</option>
                    <option value="production" {{ request('type') == 'production' ? 'selected' : '' }}>Produksi</option>
                    <option value="incoming" {{ request('type') == 'incoming' ? 'selected' : '' }}>Bahan Masuk</option>
                    <option value="outgoing" {{ request('type') == 'outgoing' ? 'selected' : '' }}>Produk Keluar</option>
                </select>
            </div>
            <div class="w-36">
                <label class="text-xs text-gray-500 block mb-1">Hasil</label>
                <select name="result" class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Semua</option>
                    @foreach(\App\Models\QcInspection::RESULTS as $k => $v)
                    <option value="{{ $k }}" {{ request('result') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-search"></i> Filter
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">No. Inspeksi</th>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Pass Rate</th>
                        <th class="px-4 py-3">Hasil</th>
                        <th class="px-4 py-3">Inspektor</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inspections as $ins)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-mono text-xs">
                            <a href="{{ route('manager.operational.qc.inspections.show', $ins) }}" class="text-cyan-600 hover:underline">{{ $ins->inspection_number }}</a>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $ins->item_name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ ucfirst($ins->inspection_type) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($ins->quantity_inspected, 0) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $ins->pass_rate >= 90 ? 'text-emerald-600' : ($ins->pass_rate >= 70 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $ins->pass_rate }}%
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $rc = match($ins->result) {
                                    'pass' => 'bg-emerald-100 text-emerald-700',
                                    'fail' => 'bg-red-100 text-red-700',
                                    'conditional' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $rc }}">
                                {{ \App\Models\QcInspection::RESULTS[$ins->result] ?? $ins->result }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $ins->inspector?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $ins->inspection_date->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada inspeksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inspections->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $inspections->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
