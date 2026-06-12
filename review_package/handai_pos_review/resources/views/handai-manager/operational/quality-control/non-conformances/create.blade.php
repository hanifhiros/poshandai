@extends('handai-manager.layouts.master')

@section('title', 'Catat Non-Conformance')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[700px] mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.qc.inspections.show', $inspection) }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Catat Non-Conformance</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $inspection->inspection_number }} &middot; {{ $inspection->item_name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('manager.operational.qc.nc.store', $inspection) }}"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label class="text-xs text-gray-500 block mb-1">Deskripsi Masalah <span class="text-red-400">*</span></label>
            <textarea name="issue_description" rows="2" required
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500"
                      placeholder="Jelaskan masalah yang ditemukan...">{{ old('issue_description') }}</textarea>
            @error('issue_description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Severity <span class="text-red-400">*</span></label>
                <select name="severity" required
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    @foreach(\App\Models\QcNonConformance::SEVERITIES as $k => $v)
                    <option value="{{ $k }}" {{ old('severity') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tindakan <span class="text-red-400">*</span></label>
                <select name="action_taken" required
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    @foreach(\App\Models\QcNonConformance::ACTIONS as $k => $v)
                    <option value="{{ $k }}" {{ old('action_taken') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Corrective Action</label>
            <textarea name="corrective_action" rows="2"
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500"
                      placeholder="Tindakan perbaikan segera...">{{ old('corrective_action') }}</textarea>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Preventive Action</label>
            <textarea name="preventive_action" rows="2"
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500"
                      placeholder="Tindakan pencegahan agar tidak terulang...">{{ old('preventive_action') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">PIC</label>
                <select name="assigned_to"
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Pilih...</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.qc.inspections.show', $inspection) }}"
               class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-red-500 text-white rounded-xl text-sm font-medium hover:bg-red-600 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Simpan NC
            </button>
        </div>
    </form>
</div>
@endsection
