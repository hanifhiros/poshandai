@extends('layouts.master')

@section('title', 'Standar QC')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1100px] mx-auto">

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-list-check text-cyan-500"></i> Standar QC
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Template checklist inspeksi kualitas</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.qc.dashboard') }}"
               class="inline-flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition">
                <i class="ti ti-arrow-left"></i> Dashboard
            </a>
            <a href="{{ route('manager.operational.qc.standards.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 text-white rounded-xl text-sm font-medium hover:bg-cyan-700 transition shadow-sm">
                <i class="ti ti-plus"></i> Buat Standar Baru
            </a>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($standards as $std)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-medium text-gray-900 flex items-center gap-2">
                        {{ $std->name }}
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-cyan-50 text-cyan-700">{{ \App\Models\QcStandard::CATEGORIES[$std->category] ?? $std->category }}</span>
                        @if(!$std->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                        @endif
                    </h3>
                    @if($std->description)
                    <p class="text-sm text-gray-500 mt-1">{{ $std->description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($std->checklist_items ?? [] as $item)
                        <span class="inline-flex items-center px-2 py-0.5 bg-gray-50 text-gray-600 rounded text-xs">
                            <i class="ti ti-checkbox mr-1"></i> {{ $item }}
                        </span>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('manager.operational.qc.standards.edit', $std) }}"
                   class="p-2 hover:bg-gray-100 rounded-lg transition shrink-0">
                    <i class="ti ti-pencil text-gray-400"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
            Belum ada standar QC.
        </div>
        @endforelse
    </div>

    @if($standards->hasPages())
    <div class="mt-4">{{ $standards->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

