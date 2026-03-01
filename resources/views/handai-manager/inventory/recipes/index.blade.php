@extends('handai-manager.layouts.master')

@section('title', 'Daftar Resep (BOM)')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Daftar Resep (BOM)</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Bill of Materials untuk setiap produk</p>
        </div>
        <a href="{{ route('manager.inventory.recipes.create') }}" class="h-9 px-4 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Resep
        </a>
    </div>

    @forelse ($groupedBoms as $productId => $sizes)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
            {{-- Product Header --}}
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-[15px] font-semibold text-gray-800">{{ $products[$productId] ?? 'Produk Tidak Diketahui' }}</h2>
            </div>

            @foreach ($sizes as $sizeId => $boms)
                <div class="px-5 py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                        <h3 class="text-[13px] font-medium text-gray-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 mr-1.5">Varian</span>
                            {{ $sizesInfo[$sizeId] ?? 'Tidak Diketahui' }}
                        </h3>
                        <div class="flex gap-1.5">
                            <a href="{{ route('manager.inventory.recipes.edit', ['variant' => $sizeId]) }}"
                               class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('manager.inventory.recipes.destroy', ['variant' => $sizeId]) }}" method="POST" onsubmit="return confirmDelete(this)">
                                @csrf @method('DELETE')
                                <button type="submit" class="h-7 px-2.5 inline-flex items-center gap-1 text-[11px] font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($boms as $bom)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 border border-gray-100 rounded-lg text-[12px] text-gray-600">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                {{ $bom->stock->name ?? '-' }} <span class="font-mono text-emerald-700">{{ $bom->quantity_required }} {{ $bom->unit->symbol ?? '' }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            <p class="text-[13px] text-gray-400">Belum ada resep yang dibuat.</p>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(form) {
        const confirmed = confirm('Yakin ingin menghapus resep ini?');
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
