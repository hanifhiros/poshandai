@extends('handai-manager.layouts.master')

@section('title', 'Rekap Absensi')

@section('content')
<style>
    .att-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .att-input:focus { background: #fff; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.08); }
    .att-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto" x-data="{ showClockIn: false }">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-fingerprint text-indigo-500"></i> Rekap Absensi
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Catatan kehadiran karyawan</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.attendance.summary') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                <i class="ti ti-report-analytics"></i> Laporan Bulanan
            </a>
            <button @click="showClockIn = !showClockIn"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                <i class="ti ti-login text-base"></i> Clock In
            </button>
        </div>
    </div>

    {{-- Clock In Form --}}
    <div x-show="showClockIn" x-cloak class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6 p-5">
        <form method="POST" action="{{ route('manager.operational.attendance.clockIn') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="text-xs font-medium text-gray-600 mb-1 block">Karyawan</label>
                <select name="employee_id" required class="att-input">
                    <option value="">Pilih karyawan...</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition h-9">
                Clock In Sekarang
            </button>
        </form>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <select name="employee_id" class="att-input sm:w-48">
                    <option value="">Semua Karyawan</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="att-input sm:w-36">
                    <option value="">Semua Status</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absen</option>
                    <option value="leave" {{ request('status') === 'leave' ? 'selected' : '' }}>Cuti</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="att-input sm:w-36">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="att-input sm:w-36">
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    <i class="ti ti-search"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Shift</th>
                        <th class="px-4 py-3">Clock In</th>
                        <th class="px-4 py-3">Clock Out</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Telat (mnt)</th>
                        <th class="px-4 py-3 text-right">Lembur (mnt)</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $att)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 text-gray-600">{{ $att->attendance_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $att->employee?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $att->shiftSchedule?->shift?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $att->clock_in?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $att->clock_out?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColor = match($att->status) {
                                    'present' => 'bg-emerald-50 text-emerald-700',
                                    'late' => 'bg-amber-50 text-amber-700',
                                    'absent' => 'bg-red-50 text-red-700',
                                    'half_day' => 'bg-orange-50 text-orange-700',
                                    'leave' => 'bg-blue-50 text-blue-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                                $statusLabel = match($att->status) {
                                    'present' => 'Hadir',
                                    'late' => 'Terlambat',
                                    'absent' => 'Absen',
                                    'half_day' => 'Setengah Hari',
                                    'leave' => 'Cuti',
                                    default => $att->status,
                                };
                            @endphp
                            <span class="att-badge {{ $statusColor }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-right {{ $att->late_minutes > 0 ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                            {{ $att->late_minutes > 0 ? $att->late_minutes : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $att->overtime_minutes > 0 ? 'text-blue-600 font-medium' : 'text-gray-400' }}">
                            {{ $att->overtime_minutes > 0 ? $att->overtime_minutes : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($att->clock_in && !$att->clock_out)
                            <form action="{{ route('manager.operational.attendance.clockOut', $att->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-100 transition">
                                    <i class="ti ti-logout"></i> Clock Out
                                </button>
                            </form>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400"><i class="ti ti-fingerprint text-4xl block mb-2"></i>Belum ada data absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
        <div class="p-4 border-t border-gray-100">{{ $attendances->links() }}</div>
        @endif
    </div>
</div>
@endsection
