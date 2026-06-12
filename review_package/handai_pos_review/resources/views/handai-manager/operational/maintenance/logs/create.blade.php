@extends('handai-manager.layouts.master')

@section('title', 'Log Maintenance')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[700px] mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.maintenance.equipment.show', $equipment) }}"
           class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Catat Maintenance</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $equipment->code }} &middot; {{ $equipment->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('manager.operational.maintenance.log.store', $equipment) }}"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tipe Maintenance <span class="text-red-400">*</span></label>
                <select name="maintenance_type" required
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="preventive" {{ old('maintenance_type') == 'preventive' ? 'selected' : '' }}>Preventive</option>
                    <option value="corrective" {{ old('maintenance_type') == 'corrective' ? 'selected' : '' }}>Corrective</option>
                    <option value="emergency" {{ old('maintenance_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tanggal <span class="text-red-400">*</span></label>
                <input type="date" name="performed_date" value="{{ old('performed_date', now()->format('Y-m-d')) }}" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                @error('performed_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        @if($equipment->schedules->count() > 0)
        <div>
            <label class="text-xs text-gray-500 block mb-1">Jadwal Terkait (opsional)</label>
            <select name="maintenance_schedule_id"
                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                <option value="">— Tanpa jadwal —</option>
                @foreach($equipment->schedules as $sch)
                <option value="{{ $sch->id }}" {{ old('maintenance_schedule_id') == $sch->id ? 'selected' : '' }}>
                    {{ $sch->task_name }} ({{ \App\Models\MaintenanceSchedule::FREQUENCIES[$sch->frequency] ?? $sch->frequency }})
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="text-xs text-gray-500 block mb-1">Deskripsi <span class="text-red-400">*</span></label>
            <textarea name="description" rows="3" required
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500"
                      placeholder="Apa yang dikerjakan pada maintenance ini?">{{ old('description') }}</textarea>
            @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Biaya (Rp)</label>
                <input type="number" name="cost" value="{{ old('cost', 0) }}" min="0" step="0.01"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Downtime (menit)</label>
                <input type="number" name="downtime_minutes" value="{{ old('downtime_minutes') }}" min="0"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Status</label>
                <select name="status"
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Sedang Berjalan</option>
                    <option value="pending_parts" {{ old('status') == 'pending_parts' ? 'selected' : '' }}>Menunggu Part</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Part yang Diganti</label>
            <input type="text" name="parts_replaced" value="{{ old('parts_replaced') }}"
                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500"
                   placeholder="cth: Filter, Belt, Thermostat">
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Petugas</label>
            <select name="performed_by"
                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                <option value="">Pilih petugas...</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('performed_by') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.maintenance.equipment.show', $equipment) }}"
               class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-medium hover:bg-teal-700 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Simpan Log
            </button>
        </div>
    </form>
</div>
@endsection
