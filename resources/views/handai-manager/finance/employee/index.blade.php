@extends('layouts.master')

@section('title', 'Daftar Karyawan')

@push('styles')
<style>
    .emp-input { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; }
    .emp-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background: #fff; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Daftar Karyawan</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Kelola data karyawan toko</p>
        </div>
        <a href="{{ route('manager.finance.employees.create') }}" class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Karyawan
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('manager.finance.employees.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1 block">Cari Karyawan</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau posisi..." class="emp-input w-full pl-9">
                </div>
            </div>
            <button type="submit" class="h-9 px-5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Cari</button>
            @if(request('search'))
                <a href="{{ route('manager.finance.employees.index') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Reset</a>
            @endif
        </div>
    </form>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50/60 border border-emerald-200 text-emerald-700 rounded-lg text-[13px]">
            {{ session('success') }}
        </div>
    @endif

    @if (session('temp_password'))
        <div class="mb-4 px-4 py-3 bg-amber-50/60 border border-amber-200 text-amber-800 rounded-lg text-[13px]" x-data="{ show: true }" x-show="show">
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-semibold">Password sementara:</span>
                    <code class="ml-1 px-2 py-0.5 bg-amber-100 rounded font-mono text-sm select-all">{{ session('temp_password') }}</code>
                    <span class="ml-2 text-amber-600 text-xs">Salin sekarang â€” tidak bisa ditampilkan lagi</span>
                </div>
                <button @click="show = false" class="text-amber-400 hover:text-amber-600 ml-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden md:table-cell">Telepon</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Posisi</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3 font-mono text-[12px] text-gray-400">#{{ $employee->id }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-gray-800 text-[13px]">{{ $employee->name }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-[13px] hidden sm:table-cell">{{ $employee->email }}</td>
                            <td class="px-5 py-3 text-gray-500 text-[13px] hidden md:table-cell">{{ $employee->contact_number }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600">{{ $employee->position }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-[13px] text-emerald-700 font-medium">Rp{{ number_format($employee->salary, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                <p class="text-[13px] text-gray-400">Tidak ada data karyawan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($employees->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-[12px] text-gray-400">Menampilkan {{ $employees->firstItem() }}â€“{{ $employees->lastItem() }} dari {{ $employees->total() }}</span>
                <div class="flex items-center gap-1">
                    @if ($employees->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                    @else
                        <a href="{{ $employees->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                    @endif
                    @foreach ($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-[13px] font-medium transition {{ $page == $employees->currentPage() ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">{{ $page }}</a>
                    @endforeach
                    @if ($employees->hasMorePages())
                        <a href="{{ $employees->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

