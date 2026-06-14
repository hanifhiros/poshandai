@extends('layouts.master')

@section('title', 'R&D Log')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-lg font-semibold text-gray-800">R&D Log</h1>
        <p class="text-[12px] text-gray-400 mt-0.5">Riwayat proyek riset & pengembangan</p>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Nama Project</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">PIC</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden md:table-cell">Deskripsi</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rndHistories as $project)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3 text-[12px] text-gray-500 font-mono">{{ \Carbon\Carbon::parse($project->rnd_date)->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="text-[13px] font-medium text-gray-800">{{ $project->rnd_name }}</span>
                            </td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 hidden sm:table-cell">{{ $project->pic->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-[13px] text-gray-500 hidden md:table-cell max-w-xs truncate">{{ $project->Deskripsi }}</td>
                            <td class="px-5 py-3 text-right font-mono text-[13px] text-emerald-700 font-medium">
                                Rp{{ number_format($project->stockUsages->sum('cost'), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                                <p class="text-[13px] text-gray-400">Tidak ada data R&D ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($rndHistories->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-[12px] text-gray-400">Menampilkan {{ $rndHistories->firstItem() }}â€“{{ $rndHistories->lastItem() }} dari {{ $rndHistories->total() }}</span>
                <div class="flex items-center gap-1">
                    @if ($rndHistories->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                    @else
                        <a href="{{ $rndHistories->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                    @endif
                    @foreach ($rndHistories->getUrlRange(1, $rndHistories->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-[13px] font-medium transition {{ $page == $rndHistories->currentPage() ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">{{ $page }}</a>
                    @endforeach
                    @if ($rndHistories->hasMorePages())
                        <a href="{{ $rndHistories->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

