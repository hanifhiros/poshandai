@extends('handai-manager.layouts.master')

@section('title', 'Manajemen Shift')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto" x-data="{ showForm: false, editId: null, form: { name: '', start_time: '', end_time: '', break_duration_minutes: 60, is_active: true } }">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-clock text-indigo-500"></i> Manajemen Shift
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Definisi shift kerja untuk karyawan</p>
        </div>
        <button @click="showForm = !showForm; editId = null; form = { name: '', start_time: '', end_time: '', break_duration_minutes: 60, is_active: true }"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="ti ti-plus text-base"></i> Tambah Shift
        </button>
    </div>

    {{-- Create/Edit form --}}
    <div x-show="showForm" x-cloak class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6 p-5">
        <form :action="editId ? '/operational/shifts/' + editId : '{{ route('manager.operational.shifts.store') }}'" method="POST">
            @csrf
            <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Nama Shift</label>
                    <input type="text" name="name" x-model="form.name" required class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none" placeholder="e.g. Pagi">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Jam Mulai</label>
                    <input type="time" name="start_time" x-model="form.start_time" required class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Jam Selesai</label>
                    <input type="time" name="end_time" x-model="form.end_time" required class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Istirahat (menit)</label>
                    <input type="number" name="break_duration_minutes" x-model="form.break_duration_minutes" required class="w-full h-9 px-3 text-sm border border-gray-200 rounded-lg focus:border-indigo-400 outline-none">
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    <span x-text="editId ? 'Update' : 'Simpan'"></span>
                </button>
                <button type="button" @click="showForm = false" class="px-5 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Batal</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Nama Shift</th>
                        <th class="px-4 py-3">Jam Mulai</th>
                        <th class="px-4 py-3">Jam Selesai</th>
                        <th class="px-4 py-3">Istirahat</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shifts as $shift)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $shift->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $shift->break_duration_minutes }} menit</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $shift->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $shift->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center flex items-center justify-center gap-1">
                            <button @click="showForm = true; editId = {{ $shift->id }}; form = { name: '{{ $shift->name }}', start_time: '{{ $shift->start_time }}', end_time: '{{ $shift->end_time }}', break_duration_minutes: {{ $shift->break_duration_minutes }}, is_active: {{ $shift->is_active ? 'true' : 'false' }} }"
                                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition">
                                <i class="ti ti-edit text-base"></i>
                            </button>
                            <form action="{{ route('manager.operational.shifts.destroy', $shift->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus shift ini?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-red-600 transition">
                                    <i class="ti ti-trash text-base"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="ti ti-clock text-4xl block mb-2"></i>Belum ada shift. Tambahkan shift pertama!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
