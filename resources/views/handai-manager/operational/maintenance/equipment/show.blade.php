@extends('handai-manager.layouts.master')

@section('title', $equipment->name)

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1200px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.operational.maintenance.equipment.index') }}"
               class="p-2 hover:bg-gray-100 rounded-lg transition">
                <i class="ti ti-arrow-left text-gray-500"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $equipment->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $equipment->code }} &middot; {{ \App\Models\Equipment::CATEGORIES[$equipment->category] ?? $equipment->category }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.maintenance.schedule.create', $equipment) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-medium hover:bg-amber-600 transition shadow-sm">
                <i class="ti ti-calendar-plus"></i> Jadwal Baru
            </a>
            <a href="{{ route('manager.operational.maintenance.log.create', $equipment) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-medium hover:bg-teal-700 transition shadow-sm">
                <i class="ti ti-clipboard-plus"></i> Log Maintenance
            </a>
            <a href="{{ route('manager.operational.maintenance.equipment.edit', $equipment) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-pencil"></i> Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
            <h2 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                <i class="ti ti-info-circle text-teal-500"></i> Detail
            </h2>
            @php
                $sc = match($equipment->status) {
                    'operational' => 'bg-emerald-100 text-emerald-700',
                    'under_maintenance' => 'bg-amber-100 text-amber-700',
                    'broken' => 'bg-red-100 text-red-700',
                    'retired' => 'bg-gray-200 text-gray-500',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">
                        {{ ucfirst(str_replace('_', ' ', $equipment->status)) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Brand</span>
                    <span class="text-gray-900">{{ $equipment->brand ?: '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Model</span>
                    <span class="text-gray-900">{{ $equipment->model_number ?: '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">No. Seri</span>
                    <span class="text-gray-900 font-mono text-xs">{{ $equipment->serial_number ?: '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Lokasi</span>
                    <span class="text-gray-900">{{ $equipment->location ?: '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tgl Beli</span>
                    <span class="text-gray-900">{{ $equipment->purchase_date?->format('d M Y') ?: '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Harga Beli</span>
                    <span class="text-gray-900">{{ $equipment->purchase_cost ? 'Rp '.number_format($equipment->purchase_cost, 0, ',', '.') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Garansi</span>
                    <span class="{{ $equipment->is_warranty_active ? 'text-emerald-600' : 'text-gray-400' }}">
                        {{ $equipment->warranty_expiry?->format('d M Y') ?: '-' }}
                        @if($equipment->is_warranty_active) <i class="ti ti-shield-check"></i> @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Biaya Maint.</span>
                    <span class="text-gray-900 font-bold">Rp {{ number_format($equipment->total_maintenance_cost, 0, ',', '.') }}</span>
                </div>
            </div>
            @if($equipment->notes)
            <div class="pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Catatan</p>
                <p class="text-sm text-gray-700">{{ $equipment->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Schedules --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-700">Jadwal Maintenance</h2>
                    <a href="{{ route('manager.operational.maintenance.schedule.create', $equipment) }}"
                       class="text-xs text-teal-600 hover:underline">+ Tambah</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <th class="px-4 py-2">Tugas</th>
                                <th class="px-4 py-2">Frekuensi</th>
                                <th class="px-4 py-2">Terakhir</th>
                                <th class="px-4 py-2">Berikutnya</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($equipment->schedules as $sch)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $sch->task_name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ \App\Models\MaintenanceSchedule::FREQUENCIES[$sch->frequency] ?? $sch->frequency }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $sch->last_performed_date?->format('d M Y') ?: '-' }}</td>
                                <td class="px-4 py-2 {{ $sch->is_overdue ? 'text-red-600 font-bold' : 'text-amber-600' }}">
                                    {{ $sch->next_due_date->format('d M Y') }}
                                    @if($sch->is_overdue) <i class="ti ti-alert-triangle text-xs"></i> @endif
                                </td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sch->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $sch->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="{{ route('manager.operational.maintenance.schedule.destroy', $sch) }}"
                                          onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 hover:bg-red-50 rounded transition">
                                            <i class="ti ti-trash text-red-400 hover:text-red-600"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada jadwal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Logs --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-700">Riwayat Maintenance</h2>
                    <a href="{{ route('manager.operational.maintenance.log.create', $equipment) }}"
                       class="text-xs text-teal-600 hover:underline">+ Tambah Log</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">Tipe</th>
                                <th class="px-4 py-2">Deskripsi</th>
                                <th class="px-4 py-2">Biaya</th>
                                <th class="px-4 py-2">Downtime</th>
                                <th class="px-4 py-2">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($equipment->logs()->latest('performed_date')->take(20)->get() as $log)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-2 text-gray-500">{{ $log->performed_date->format('d M Y') }}</td>
                                <td class="px-4 py-2">
                                    @php
                                        $tc = match($log->maintenance_type) {
                                            'preventive' => 'bg-blue-100 text-blue-700',
                                            'corrective' => 'bg-amber-100 text-amber-700',
                                            'emergency' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tc }}">
                                        {{ ucfirst($log->maintenance_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-gray-700 max-w-[200px] truncate">{{ $log->description }}</td>
                                <td class="px-4 py-2 text-gray-900">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $log->downtime_minutes ? $log->downtime_minutes.' min' : '-' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $log->performer?->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada log.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
