@extends('handai-manager.layouts.master')

@section('title', 'Quality Control Dashboard')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-certificate text-cyan-500"></i> Quality Control
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Dashboard inspeksi & pengendalian mutu bulan ini</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.qc.standards.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-list-check"></i> Standar QC
            </a>
            <a href="{{ route('manager.operational.qc.inspections.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 text-white rounded-xl text-sm font-medium hover:bg-cyan-700 transition shadow-sm">
                <i class="ti ti-plus"></i> Inspeksi Baru
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Inspeksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalInspections }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Pass</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $passCount }}</p>
            @if($totalInspections > 0)
            <p class="text-xs text-gray-400">{{ round(($passCount / $totalInspections) * 100, 1) }}%</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Fail</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $failCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">NC Terbuka</p>
            <p class="text-2xl font-bold {{ $openNc > 0 ? 'text-amber-600' : 'text-gray-400' }} mt-1">{{ $openNc }}</p>
        </div>
    </div>

    {{-- Recent --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">Inspeksi Terakhir</h2>
            <a href="{{ route('manager.operational.qc.inspections.index') }}" class="text-xs text-cyan-600 hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">No.</th>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3 text-right">Diperiksa</th>
                        <th class="px-4 py-3 text-right">Pass Rate</th>
                        <th class="px-4 py-3">Hasil</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentInspections as $ins)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">
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
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $ins->inspection_date->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada inspeksi bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
