@extends('layouts.master')

@section('title', 'Jadwal Shift')

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
                <i class="ti ti-calendar text-indigo-500"></i> Jadwal Shift Mingguan
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $weekStart->format('d M Y') }} â€” {{ $weekEnd->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.attendance.schedule', ['week_start' => $weekStart->copy()->subWeek()->format('Y-m-d')]) }}"
               class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                <i class="ti ti-chevron-left"></i> Prev
            </a>
            <a href="{{ route('manager.operational.attendance.schedule', ['week_start' => $weekStart->copy()->addWeek()->format('Y-m-d')]) }}"
               class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                Next <i class="ti ti-chevron-right"></i>
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('manager.operational.attendance.store-schedule') }}">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                            <th class="px-4 py-3 sticky left-0 bg-gray-50 z-10 min-w-[160px]">Karyawan</th>
                            @foreach($dates as $date)
                            <th class="px-3 py-3 text-center min-w-[120px] {{ $date->isToday() ? 'bg-indigo-50' : '' }}">
                                {{ $date->translatedFormat('D') }}<br>
                                <span class="text-gray-400">{{ $date->format('d/m') }}</span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $idx = 0; @endphp
                        @foreach($employees as $emp)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2 sticky left-0 bg-white z-10 font-medium text-gray-900 text-xs">{{ $emp->name }}</td>
                            @foreach($dates as $date)
                            @php
                                $key = $emp->id . '-' . $date->format('Y-m-d');
                                $existing = $schedules[$key]->first ?? ($schedules->has($key) ? $schedules[$key]->first() : null);
                            @endphp
                            <td class="px-2 py-2 {{ $date->isToday() ? 'bg-indigo-50/50' : '' }}">
                                <input type="hidden" name="schedules[{{ $idx }}][employee_id]" value="{{ $emp->id }}">
                                <input type="hidden" name="schedules[{{ $idx }}][date]" value="{{ $date->format('Y-m-d') }}">
                                <select name="schedules[{{ $idx }}][shift_id]" class="w-full h-8 px-2 text-xs border border-gray-200 rounded-lg outline-none focus:border-indigo-400">
                                    <option value="">â€”</option>
                                    @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ $existing && $existing->shift_id == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            @php $idx++; @endphp
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Simpan Jadwal
            </button>
        </div>
    </form>
</div>
@endsection

