@extends('handai-manager.layouts.master')

@section('title', 'Non-Conformances')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-alert-octagon text-red-500"></i> Non-Conformances
            </h1>
        </div>
        <a href="{{ route('manager.operational.qc.dashboard') }}"
           class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
            <i class="ti ti-arrow-left"></i> Dashboard QC
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <form action="{{ route('manager.operational.qc.nc') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-36">
                <label class="text-xs text-gray-500 block mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Semua</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="w-36">
                <label class="text-xs text-gray-500 block mb-1">Severity</label>
                <select name="severity" class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Semua</option>
                    @foreach(\App\Models\QcNonConformance::SEVERITIES as $k => $v)
                    <option value="{{ $k }}" {{ request('severity') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-filter"></i> Filter
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">NC#</th>
                        <th class="px-4 py-3">Inspeksi</th>
                        <th class="px-4 py-3">Masalah</th>
                        <th class="px-4 py-3">Severity</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">PIC</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($nonConformances as $nc)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $nc->nc_number }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.operational.qc.inspections.show', $nc->qc_inspection_id) }}" class="text-cyan-600 hover:underline text-xs">
                                {{ $nc->inspection?->inspection_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-900 max-w-[200px] truncate">{{ $nc->issue_description }}</td>
                        <td class="px-4 py-3">
                            @php
                                $sv = match($nc->severity) {
                                    'minor' => 'bg-blue-100 text-blue-700',
                                    'major' => 'bg-amber-100 text-amber-700',
                                    'critical' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sv }}">{{ ucfirst($nc->severity) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ \App\Models\QcNonConformance::ACTIONS[$nc->action_taken] ?? $nc->action_taken }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $nc->assignee?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $ns = match($nc->status) {
                                    'open' => 'bg-red-100 text-red-700',
                                    'in_progress' => 'bg-amber-100 text-amber-700',
                                    'closed' => 'bg-emerald-100 text-emerald-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ns }}">{{ ucfirst(str_replace('_', ' ', $nc->status)) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($nc->status !== 'closed')
                            <form method="POST" action="{{ route('manager.operational.qc.nc.close', $nc) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-emerald-600 hover:underline">Tutup</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada non-conformance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($nonConformances->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $nonConformances->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
