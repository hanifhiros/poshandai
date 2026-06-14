@extends('layouts.master')

@section('title', 'Production Planning')

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
                <i class="ti ti-calendar-event text-indigo-500"></i> Production Planning (MPS)
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Master Production Schedule & Material Requirements Planning</p>
        </div>
        <a href="{{ route('manager.operational.production-plans.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="ti ti-plus"></i> Buat Plan Baru
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <form action="{{ route('manager.operational.production-plans.index') }}" method="GET"
              class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500 block mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Nama / nomor plan...">
            </div>
            <div class="w-40">
                <label class="text-xs text-gray-500 block mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua</option>
                    @foreach(\App\Models\ProductionPlan::STATUSES as $k => $v)
                    <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-search"></i> Filter
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">No. Plan</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3 text-center">Items</th>
                        <th class="px-4 py-3">Progress</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $plan->plan_number }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.operational.production-plans.show', $plan) }}" class="font-medium text-indigo-700 hover:underline">
                                {{ $plan->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $plan->start_date->format('d M') }} â€” {{ $plan->end_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $plan->items_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $plan->progress }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">{{ $plan->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $sc = match($plan->status) {
                                    'draft' => 'bg-gray-100 text-gray-600',
                                    'confirmed' => 'bg-blue-100 text-blue-700',
                                    'in_progress' => 'bg-amber-100 text-amber-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-600',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                {{ \App\Models\ProductionPlan::STATUSES[$plan->status] ?? $plan->status }}
                                @if($plan->has_shortage) <i class="ti ti-alert-triangle text-red-500 ml-1"></i> @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('manager.operational.production-plans.show', $plan) }}" class="p-1.5 hover:bg-gray-100 rounded-lg transition">
                                <i class="ti ti-eye text-gray-500"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada production plan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $plans->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

