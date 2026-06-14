@extends('layouts.master')

@section('title', 'Daftar Peralatan')

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
                <i class="ti ti-cpu text-teal-500"></i> Daftar Peralatan
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Inventaris peralatan & aset operasional</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.maintenance.dashboard') }}"
               class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
                <i class="ti ti-arrow-left"></i> Dashboard
            </a>
            <a href="{{ route('manager.operational.maintenance.equipment.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-medium hover:bg-teal-700 transition shadow-sm">
                <i class="ti ti-plus"></i> Tambah Peralatan
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-6">
        <form action="{{ route('manager.operational.maintenance.equipment.index') }}" method="GET"
              class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500 block mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500"
                       placeholder="Nama / kode peralatan...">
            </div>
            <div class="w-40">
                <label class="text-xs text-gray-500 block mb-1">Kategori</label>
                <select name="category" class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Equipment::CATEGORIES as $k => $v)
                    <option value="{{ $k }}" {{ request('category') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="text-xs text-gray-500 block mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Semua</option>
                    <option value="operational" {{ request('status') == 'operational' ? 'selected' : '' }}>Operasional</option>
                    <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="broken" {{ request('status') == 'broken' ? 'selected' : '' }}>Rusak</option>
                    <option value="retired" {{ request('status') == 'retired' ? 'selected' : '' }}>Retired</option>
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
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Garansi</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($equipment as $eq)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $eq->code }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.operational.maintenance.equipment.show', $eq) }}" class="font-medium text-teal-700 hover:underline">{{ $eq->name }}</a>
                            @if($eq->brand)
                            <p class="text-xs text-gray-400">{{ $eq->brand }} {{ $eq->model_number }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ \App\Models\Equipment::CATEGORIES[$eq->category] ?? $eq->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $sc = match($eq->status) {
                                    'operational' => 'bg-emerald-100 text-emerald-700',
                                    'under_maintenance' => 'bg-amber-100 text-amber-700',
                                    'broken' => 'bg-red-100 text-red-700',
                                    'retired' => 'bg-gray-200 text-gray-500',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                                {{ ucfirst(str_replace('_', ' ', $eq->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($eq->warranty_expiry)
                                @if($eq->is_warranty_active)
                                    <span class="text-emerald-600">s/d {{ $eq->warranty_expiry->format('d M Y') }}</span>
                                @else
                                    <span class="text-gray-400 line-through">{{ $eq->warranty_expiry->format('d M Y') }}</span>
                                @endif
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('manager.operational.maintenance.equipment.show', $eq) }}" class="p-1.5 hover:bg-gray-100 rounded-lg transition" title="Detail">
                                    <i class="ti ti-eye text-gray-500"></i>
                                </a>
                                <a href="{{ route('manager.operational.maintenance.equipment.edit', $eq) }}" class="p-1.5 hover:bg-gray-100 rounded-lg transition" title="Edit">
                                    <i class="ti ti-pencil text-gray-500"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada peralatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($equipment->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $equipment->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

