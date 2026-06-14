@extends('layouts.master')

@section('title', 'Laporan Maintenance')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-report-analytics text-teal-500"></i> Laporan Maintenance
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Ringkasan biaya & aktivitas maintenance</p>
        </div>
        <a href="{{ route('manager.operational.maintenance.dashboard') }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
            <i class="ti ti-arrow-left"></i> Dashboard
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <form action="{{ route('manager.operational.maintenance.report') }}" method="GET"
              class="flex flex-wrap gap-3 items-end">
            <div class="w-40">
                <label class="text-xs text-gray-500 block mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div class="w-40">
                <label class="text-xs text-gray-500 block mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-filter"></i> Filter
            </button>
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Biaya</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($report->sum('total_cost'), 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Aktivitas</p>
            <p class="text-2xl font-bold text-teal-600 mt-1">{{ $report->sum('total_logs') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Downtime</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($report->sum('total_downtime')) }} min</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Peralatan Aktif</p>
            <p class="text-2xl font-bold text-gray-600 mt-1">{{ $report->count() }}</p>
        </div>
    </div>

    {{-- Table per Equipment --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">Detail per Peralatan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Peralatan</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Jumlah Maint.</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-right">Total Downtime</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($report as $r)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.operational.maintenance.equipment.show', $r->id) }}" class="font-medium text-teal-700 hover:underline">
                                {{ $r->name }}
                            </a>
                            <p class="text-xs text-gray-400 font-mono">{{ $r->code }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ \App\Models\Equipment::CATEGORIES[$r->category] ?? $r->category }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ $r->total_logs }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($r->total_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ number_format($r->total_downtime) }} min</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data maintenance di periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if($report->count() > 0)
                <tfoot>
                    <tr class="bg-gray-50 font-bold text-sm">
                        <td class="px-4 py-3" colspan="2">Total</td>
                        <td class="px-4 py-3 text-right">{{ $report->sum('total_logs') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($report->sum('total_cost'), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($report->sum('total_downtime')) }} min</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

