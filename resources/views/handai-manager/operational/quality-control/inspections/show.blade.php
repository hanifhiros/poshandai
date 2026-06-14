@extends('layouts.master')

@section('title', $inspection->inspection_number)

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1100px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.operational.qc.inspections.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="ti ti-arrow-left text-gray-500"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $inspection->inspection_number }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $inspection->item_name }} &middot; {{ $inspection->inspection_date->format('d M Y') }}</p>
            </div>
        </div>
        <a href="{{ route('manager.operational.qc.nc.create', $inspection) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500 text-white rounded-xl text-sm font-medium hover:bg-red-600 transition shadow-sm">
            <i class="ti ti-alert-triangle"></i> Catat Non-Conformance
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Result --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
            @php
                $rc = match($inspection->result) {
                    'pass' => 'bg-emerald-100 text-emerald-700',
                    'fail' => 'bg-red-100 text-red-700',
                    'conditional' => 'bg-amber-100 text-amber-700',
                    default => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $rc }}">
                    {{ \App\Models\QcInspection::RESULTS[$inspection->result] ?? $inspection->result }}
                </span>
                <p class="text-3xl font-bold text-gray-900 mt-3">{{ $inspection->pass_rate }}%</p>
                <p class="text-xs text-gray-500">Pass Rate</p>
            </div>

            <div class="space-y-2 text-sm pt-3 border-t border-gray-100">
                <div class="flex justify-between">
                    <span class="text-gray-500">Tipe</span>
                    <span class="text-gray-900">{{ ucfirst($inspection->inspection_type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Standar</span>
                    <span class="text-gray-900">{{ $inspection->standard?->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Diperiksa</span>
                    <span class="text-gray-900">{{ number_format($inspection->quantity_inspected, 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pass</span>
                    <span class="text-emerald-600 font-medium">{{ number_format($inspection->quantity_passed, 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Fail</span>
                    <span class="text-red-600 font-medium">{{ number_format($inspection->quantity_failed, 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Inspektor</span>
                    <span class="text-gray-900">{{ $inspection->inspector?->name ?? '-' }}</span>
                </div>
            </div>

            @if($inspection->notes)
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Catatan</p>
                <p class="text-sm text-gray-700">{{ $inspection->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Checklist & NC --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Checklist Results --}}
            @if($inspection->checklist_results && count($inspection->checklist_results) > 0)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h2 class="text-sm font-bold text-gray-700 mb-3">Hasil Checklist</h2>
                <div class="space-y-2">
                    @foreach($inspection->checklist_results as $cr)
                    <div class="flex items-center gap-2 text-sm">
                        @if(!empty($cr['passed']))
                        <i class="ti ti-circle-check text-emerald-500"></i>
                        @else
                        <i class="ti ti-circle-x text-red-400"></i>
                        @endif
                        <span class="text-gray-700">{{ $cr['item'] ?? '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Non-Conformances --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-700">Non-Conformances</h2>
                    <a href="{{ route('manager.operational.qc.nc.create', $inspection) }}"
                       class="text-xs text-red-600 hover:underline">+ Tambah NC</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <th class="px-4 py-2">No.</th>
                                <th class="px-4 py-2">Masalah</th>
                                <th class="px-4 py-2">Severity</th>
                                <th class="px-4 py-2">Aksi</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($inspection->nonConformances as $nc)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $nc->nc_number }}</td>
                                <td class="px-4 py-2 text-gray-900">{{ $nc->issue_description }}</td>
                                <td class="px-4 py-2">
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
                                <td class="px-4 py-2 text-gray-500 text-xs">{{ \App\Models\QcNonConformance::ACTIONS[$nc->action_taken] ?? $nc->action_taken }}</td>
                                <td class="px-4 py-2">
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
                                <td class="px-4 py-2">
                                    @if($nc->status !== 'closed')
                                    <form method="POST" action="{{ route('manager.operational.qc.nc.close', $nc) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-emerald-600 hover:underline">Tutup</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Tidak ada non-conformance.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

