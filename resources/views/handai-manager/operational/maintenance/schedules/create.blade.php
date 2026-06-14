@extends('layouts.master')

@section('title', 'Tambah Jadwal Maintenance')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[700px] mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.maintenance.equipment.show', $equipment) }}"
           class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Jadwal Maintenance</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $equipment->code }} &middot; {{ $equipment->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('manager.operational.maintenance.schedule.store', $equipment) }}"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="text-xs text-gray-500 block mb-1">Nama Tugas <span class="text-red-400">*</span></label>
            <input type="text" name="task_name" value="{{ old('task_name') }}" required
                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500"
                   placeholder="cth: Pembersihan Filter, Kalibrasi Suhu">
            @error('task_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Frekuensi <span class="text-red-400">*</span></label>
                <select name="frequency" required
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                    <option value="">Pilih...</option>
                    @foreach(\App\Models\MaintenanceSchedule::FREQUENCIES as $k => $v)
                    <option value="{{ $k }}" {{ old('frequency') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                @error('frequency')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tanggal Mulai / Due Pertama <span class="text-red-400">*</span></label>
                <input type="date" name="next_due_date" value="{{ old('next_due_date', now()->format('Y-m-d')) }}" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                @error('next_due_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Deskripsi Tugas</label>
            <textarea name="description" rows="3"
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500"
                      placeholder="Langkah-langkah maintenance (opsional)">{{ old('description') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.maintenance.equipment.show', $equipment) }}"
               class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-medium hover:bg-teal-700 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Simpan Jadwal
            </button>
        </div>
    </form>
</div>
@endsection

