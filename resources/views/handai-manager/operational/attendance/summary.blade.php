@extends('handai-manager.layouts.master')

@section('title', 'Laporan Absensi Bulanan')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-report-analytics text-indigo-500"></i> Laporan Absensi Bulanan
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Ringkasan kehadiran karyawan per bulan</p>
        </div>
        <a href="{{ route('manager.operational.attendance.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Month selector --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6 p-4">
        <form method="GET" class="flex items-center gap-3">
            <input type="month" name="month" value="{{ $month }}" class="h-9 px-3 text-sm border border-gray-200 rounded-lg focus:border-indigo-400 outline-none">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3 text-center">Hadir</th>
                        <th class="px-4 py-3 text-center">Terlambat</th>
                        <th class="px-4 py-3 text-center">Absen</th>
                        <th class="px-4 py-3 text-center">Cuti</th>
                        <th class="px-4 py-3 text-right">Total Telat (mnt)</th>
                        <th class="px-4 py-3 text-right">Total Lembur (jam)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($summaries as $s)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $s['employee']->name }}</td>
                        <td class="px-4 py-3 text-center text-emerald-600 font-bold">{{ $s['total_present'] }}</td>
                        <td class="px-4 py-3 text-center text-amber-600 font-medium">{{ $s['total_late'] }}</td>
                        <td class="px-4 py-3 text-center text-red-600 font-medium">{{ $s['total_absent'] }}</td>
                        <td class="px-4 py-3 text-center text-blue-600">{{ $s['total_leave'] }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $s['total_late_min'] }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $s['total_overtime'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada data absensi untuk bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
