@extends('handai-manager.layouts.master')

@section('title', 'Log Pembelian Stok')

@push('styles')
<style>
    .sbf-input { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; }
    .sbf-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background: #fff; }
    .sbf-select { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; }
    .sbf-select:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background-color: #fff; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-lg font-semibold text-gray-800">Log Pembelian Stok</h1>
        <p class="text-[12px] text-gray-400 mt-0.5">Catatan pembelian bahan baku & stok masuk</p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('manager.finance.stock-batch-log.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1 block">Cari Stok</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama stock..." class="sbf-input w-full pl-9">
                </div>
            </div>
            <div class="flex flex-col">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Urutan</label>
                <select name="sort_date" class="sbf-select w-32">
                    <option value="desc" {{ request('sort_date') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                    <option value="asc" {{ request('sort_date') == 'asc' ? 'selected' : '' }}>Terlama</option>
                </select>
            </div>
            <button type="submit" class="h-9 px-5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Filter</button>
            @if(request('search'))
                <a href="{{ route('manager.finance.stock-batch-log.index') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Reset</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Stok</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Jumlah</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">Satuan</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right">Biaya</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden md:table-cell">Tgl Beli</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden lg:table-cell">Nota</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-center">Disimpan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasData = false; @endphp
                    @foreach ($stockBatches as $batch)
                        @if ($batch->store_id == session('selected_store'))
                            @php $hasData = true; @endphp
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 text-[13px] font-medium text-gray-800">{{ $batch->stock->name ?? $batch->stock_name }}</td>
                                <td class="px-5 py-3 text-right font-mono text-[13px] text-gray-700">{{ number_format($batch->unit_qty) }}</td>
                                <td class="px-5 py-3 text-[13px] text-gray-500 hidden sm:table-cell">{{ $batch->unit->symbol ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-mono text-[13px] text-emerald-700 font-medium">Rp{{ number_format($batch->cost, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-[13px] text-gray-500 hidden md:table-cell">{{ \Carbon\Carbon::parse($batch->buy_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 hidden lg:table-cell">
                                    @if($batch->nota_url && $batch->nota_url !== 'belum ada gambar')
                                        <a href="{{ asset('storage/assets/nota/' . $batch->nota_url) }}" target="_blank" class="text-[12px] text-blue-500 hover:underline">Lihat</a>
                                    @else
                                        <span class="text-[12px] text-gray-400 italic">–</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $batch->isStored === 'Yes' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                        {{ $batch->isStored === 'Yes' ? 'Ya' : 'Belum' }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    @if (!$hasData)
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                <p class="text-[13px] text-gray-400">Tidak ada data pembelian stok.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($stockBatches->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-[12px] text-gray-400">Menampilkan {{ $stockBatches->firstItem() }}–{{ $stockBatches->lastItem() }} dari {{ $stockBatches->total() }}</span>
                <div class="flex items-center gap-1">
                    @if ($stockBatches->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                    @else
                        <a href="{{ $stockBatches->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                    @endif
                    @foreach ($stockBatches->getUrlRange(1, $stockBatches->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-[13px] font-medium transition {{ $page == $stockBatches->currentPage() ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">{{ $page }}</a>
                    @endforeach
                    @if ($stockBatches->hasMorePages())
                        <a href="{{ $stockBatches->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
