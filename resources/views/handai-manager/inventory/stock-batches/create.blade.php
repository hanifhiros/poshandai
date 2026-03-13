@extends('handai-manager.layouts.master')

@section('title', 'Tambah Batch Stok')

@section('content')
<div class="min-h-screen bg-slate-50/60 py-6 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="max-w-4xl mx-auto mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.inventory.stock') }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Tambah Batch — {{ $stock->name }}</h1>
                <p class="text-sm text-slate-500">Catat pembelian / batch baru untuk item ini</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        @if(session('success'))
            <div class="mb-4">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('manager.inventory.stock.batch.store', $stock->id) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Informasi Batch
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Jumlah Unit <span class="text-red-400">*</span></label>
                            <input type="number" name="unit_qty" required min="1" step="0.001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Satuan <span class="text-red-400">*</span></label>
                            <select name="unit_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                                <option value="">Pilih...</option>
                                @foreach($units as $unit)
                                    @if ($unit->unit_type === $stock->unit->unit_type)
                                        <option value="{{ $unit->id }}" {{ $unit->id == $stock->unit_id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->symbol }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Total Cost (Rp) <span class="text-red-400">*</span></label>
                            <input type="number" name="cost" step="0.01" min="0.01" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Tanggal Beli <span class="text-red-400">*</span></label>
                            <input value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" type="date" name="buy_date" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Upload Nota (Optional)</label>
                            <input type="file" name="nota" accept="image/*" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm">Simpan Batch</button>
                <a href="{{ route('manager.inventory.stock') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-gray-100">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection