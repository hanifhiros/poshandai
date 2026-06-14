@extends('layouts.master')

@section('title', $plan->name)

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.operational.production-plans.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="ti ti-arrow-left text-gray-500"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $plan->plan_number }} &middot; {{ $plan->start_date->format('d M') }} â€” {{ $plan->end_date->format('d M Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($plan->status === 'draft')
            <form method="POST" action="{{ route('manager.operational.production-plans.confirm', $plan) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                    <i class="ti ti-check"></i> Konfirmasi Plan
                </button>
            </form>
            @endif
            @if($plan->status === 'confirmed')
            <form method="POST" action="{{ route('manager.operational.production-plans.start', $plan) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600 transition shadow-sm">
                    <i class="ti ti-player-play"></i> Mulai Produksi
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('manager.operational.production-plans.recalculate-mrp', $plan) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                    <i class="ti ti-refresh"></i> Hitung Ulang MRP
                </button>
            </form>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
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
            <span class="inline-flex items-center px-2 py-0.5 mt-2 rounded-full text-xs font-medium {{ $sc }}">
                {{ \App\Models\ProductionPlan::STATUSES[$plan->status] ?? $plan->status }}
            </span>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Progress</p>
            <div class="flex items-center gap-2 mt-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-indigo-500 h-2.5 rounded-full" style="width: {{ $plan->progress }}%"></div>
                </div>
                <span class="text-sm font-bold text-gray-900">{{ $plan->progress }}%</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Item Produksi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $plan->items->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Material Kurang</p>
            <p class="text-2xl font-bold {{ $shortages->count() > 0 ? 'text-red-600' : 'text-emerald-600' }} mt-1">
                {{ $shortages->count() }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Plan Items --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-700">Item Produksi (MPS)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <th class="px-4 py-2">Produk</th>
                            <th class="px-4 py-2 text-right">Plan</th>
                            <th class="px-4 py-2 text-right">Selesai</th>
                            <th class="px-4 py-2">Target</th>
                            <th class="px-4 py-2">PIC</th>
                            @if(in_array($plan->status, ['in_progress']))
                            <th class="px-4 py-2"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($plan->items as $item)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $item->item_name }}</td>
                            <td class="px-4 py-2 text-right text-gray-700">{{ number_format($item->planned_quantity, 0) }}</td>
                            <td class="px-4 py-2 text-right">
                                <span class="{{ $item->status === 'completed' ? 'text-emerald-600 font-bold' : 'text-gray-700' }}">
                                    {{ number_format($item->produced_quantity, 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $item->target_date->format('d M') }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500">{{ $item->assignee?->name ?? '-' }}</td>
                            @if(in_array($plan->status, ['in_progress']))
                            <td class="px-4 py-2">
                                @if($item->status !== 'completed')
                                <form method="POST" action="{{ route('manager.operational.production-plans.complete-item', $item) }}"
                                      class="flex items-center gap-1" x-data="{ qty: '' }">
                                    @csrf
                                    <input type="number" name="produced_quantity" x-model="qty" min="1" step="1"
                                           class="w-16 rounded border-gray-200 text-xs py-1 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Qty">
                                    <button type="submit" :disabled="!qty"
                                            class="px-2 py-1 bg-emerald-500 text-white rounded text-xs hover:bg-emerald-600 disabled:opacity-40 transition">
                                        <i class="ti ti-check"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-emerald-600"><i class="ti ti-circle-check"></i></span>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MRP Aggregated --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-700">Kebutuhan Material (MRP)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <th class="px-4 py-2">Material</th>
                            <th class="px-4 py-2 text-right">Butuh</th>
                            <th class="px-4 py-2 text-right">Tersedia</th>
                            <th class="px-4 py-2 text-right">Kurang</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($aggregated as $mat)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2 font-medium text-gray-900">
                                {{ $mat->material_name }}
                                @if($mat->unit) <span class="text-xs text-gray-400">({{ $mat->unit?->name }})</span> @endif
                            </td>
                            <td class="px-4 py-2 text-right text-gray-700">{{ number_format($mat->total_required, 1) }}</td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ number_format($mat->available, 1) }}</td>
                            <td class="px-4 py-2 text-right {{ $mat->total_shortage > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                                {{ $mat->total_shortage > 0 ? number_format($mat->total_shortage, 1) : '-' }}
                            </td>
                            <td class="px-4 py-2">
                                @if($mat->total_shortage > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <i class="ti ti-alert-triangle mr-1"></i> Kurang
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    Cukup
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada kebutuhan material.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($plan->notes)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Catatan</p>
        <p class="text-sm text-gray-700">{{ $plan->notes }}</p>
    </div>
    @endif
</div>
@endsection

