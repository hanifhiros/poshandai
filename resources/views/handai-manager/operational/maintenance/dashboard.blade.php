@extends('handai-manager.layouts.master')

@section('title', 'Maintenance Dashboard')

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
                <i class="ti ti-tool text-teal-500"></i> Maintenance Dashboard
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Pemeliharaan peralatan & aset operasional</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.maintenance.equipment.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-list"></i> Daftar Peralatan
            </a>
            <a href="{{ route('manager.operational.maintenance.report') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-medium hover:bg-teal-700 transition shadow-sm">
                <i class="ti ti-report-analytics"></i> Laporan
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Peralatan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalEquipment }}</p>
            <p class="text-xs text-emerald-600 mt-1">{{ $operational }} operasional</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Upcoming (7 hari)</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $upcoming->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Overdue</p>
            <p class="text-2xl font-bold {{ $overdue->count() > 0 ? 'text-red-600' : 'text-gray-400' }} mt-1">{{ $overdue->count() }}</p>
        </div>
    </div>

    {{-- Overdue --}}
    @if($overdue->count() > 0)
    <div class="bg-red-50 rounded-xl border border-red-100 shadow-sm mb-6 overflow-hidden">
        <div class="px-4 py-3 bg-red-100/50 border-b border-red-100">
            <h2 class="text-sm font-bold text-red-700 flex items-center gap-2">
                <i class="ti ti-alert-triangle"></i> Maintenance Terlambat!
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-red-500">
                        <th class="px-4 py-2">Peralatan</th>
                        <th class="px-4 py-2">Tugas</th>
                        <th class="px-4 py-2">Frekuensi</th>
                        <th class="px-4 py-2">Tenggat</th>
                        <th class="px-4 py-2">Terlambat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100">
                    @foreach($overdue as $s)
                    <tr class="hover:bg-red-100/30">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $s->equipment?->name }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $s->task_name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ \App\Models\MaintenanceSchedule::FREQUENCIES[$s->frequency] ?? $s->frequency }}</td>
                        <td class="px-4 py-2 text-red-600">{{ $s->next_due_date->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-red-700 font-bold">{{ $s->next_due_date->diffInDays(now()) }} hari</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Upcoming --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-bold text-gray-700">Maintenance 7 Hari Ke Depan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Peralatan</th>
                        <th class="px-4 py-3">Tugas</th>
                        <th class="px-4 py-3">Frekuensi</th>
                        <th class="px-4 py-3">Tenggat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($upcoming as $s)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $s->equipment?->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $s->task_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ \App\Models\MaintenanceSchedule::FREQUENCIES[$s->frequency] ?? $s->frequency }}</td>
                        <td class="px-4 py-3 text-amber-600">{{ $s->next_due_date->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada jadwal maintenance dalam 7 hari ke depan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
