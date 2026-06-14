@extends('layouts.master')

@section('title', 'Database Reseller')

@push('styles')
<style>
    .res-input { height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 12px; font-size: 13px; color: #334155; transition: all .15s ease; }
    .res-input:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); background: #fff; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Database Reseller</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Kelola data reseller & link order</p>
        </div>
        <div class="flex items-center gap-2">
            @include('handai-manager.partials.import-export-modal', ['type' => 'reseller', 'label' => 'Reseller'])
            <a href="{{ route('manager.marketing.resellers.create') }}" class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Reseller
            </a>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('manager.marketing.resellers.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1 block">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" name="search" placeholder="Nama atau kode..." value="{{ request('search') }}" class="res-input w-full pl-9">
                </div>
            </div>
            <button type="submit" class="h-9 px-5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Cari</button>
            @if(request('search'))
                <a href="{{ route('manager.marketing.resellers.index') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Reset</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden sm:table-cell">Kode</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider hidden md:table-cell">Form Order</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right hidden lg:table-cell">Toko Aktif</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-right hidden lg:table-cell">Total Terjual</th>
                        <th class="px-5 py-3 text-[11px] font-medium text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resellers as $reseller)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3">
                                <span class="text-[13px] font-medium text-gray-800">{{ $reseller->name }}</span>
                                <p class="text-[11px] text-gray-400 sm:hidden">{{ $reseller->code }}</p>
                            </td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="font-mono text-[12px] text-gray-500 bg-gray-50 px-1.5 py-0.5 rounded">{{ $reseller->code }}</span>
                            </td>
                            <td class="px-5 py-3 hidden md:table-cell">
                                <div x-data="{ copied: false }" class="flex items-center gap-2">
                                    <a href="{{ url('/customer-order/login') }}?reseller={{ $reseller->code }}&store_id={{ session('selected_store') ?? session('selected_store_') }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="text-[12px] text-blue-500 hover:underline font-medium">Form</a>
                                    <button
                                        @click="navigator.clipboard.writeText('{{ url('/customer-order/login?reseller=' . $reseller->code) }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="h-6 px-2 text-[11px] text-gray-500 hover:text-gray-700 border border-gray-200 rounded-md transition" type="button">
                                        Copy
                                    </button>
                                    <span x-show="copied" x-transition class="text-[11px] text-emerald-600 font-medium">Disalin</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium {{ $reseller->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                    {{ ucfirst($reseller->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-[13px] text-gray-700 hidden lg:table-cell">{{ $reseller->resellerStores->count() }}</td>
                            <td class="px-5 py-3 text-right font-mono text-[13px] text-gray-700 hidden lg:table-cell">{{ number_format($reseller->resellerStores->sum('qty_sold')) }}</td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('manager.marketing.resellers.edit', $reseller->id) }}" class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('manager.marketing.resellers.destroy', $reseller->id) }}" method="POST" onsubmit="return confirmDelete(this)" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                <p class="text-[13px] text-gray-400">Tidak ada data reseller.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($resellers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-[12px] text-gray-400">Menampilkan {{ $resellers->firstItem() }}â€“{{ $resellers->lastItem() }} dari {{ $resellers->total() }}</span>
                <div class="flex items-center gap-1">
                    @if ($resellers->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></span>
                    @else
                        <a href="{{ $resellers->appends(request()->query())->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a>
                    @endif
                    @foreach ($resellers->appends(request()->query())->getUrlRange(1, $resellers->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-[13px] font-medium transition {{ $page == $resellers->currentPage() ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">{{ $page }}</a>
                    @endforeach
                    @if ($resellers->hasMorePages())
                        <a href="{{ $resellers->appends(request()->query())->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(form) {
        const confirmed = confirm('Yakin ingin menghapus reseller ini?');
        if (confirmed) {
            window.dispatchEvent(new Event('loading-start'));
        } else {
            setTimeout(() => {
                window.dispatchEvent(new Event('loading-end'));
            }, 500);
        }
        return confirmed;
    }
</script>
@endpush

