@extends('handai-manager.layouts.master')

@section('title', 'Daftar Resep (BOM)')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans" x-data="{ search: '', recipeFilter: 'all' }">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-lg text-sm flex items-center gap-3 shadow-sm" x-data x-init="setTimeout(() => $el.remove(), 4000)">
        <div class="bg-emerald-100 rounded-full p-1">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-lg text-sm flex items-center gap-3 shadow-sm">
        <div class="bg-red-100 rounded-full p-1">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <span class="font-medium">{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-8">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-chef-hat text-emerald-600"></i>
                Daftar Resep
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola komposisi bahan dan harga pokok produksi (HPP)</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            {{-- Search (left) --}}
            <div class="relative w-full md:w-3/5 lg:w-2/3">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="recipe-search" 
                    type="text" 
                    x-model="search" 
                    placeholder="Cari produk... (Ctrl+K)" 
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-shadow shadow-sm"
                >
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                @include('handai-manager.partials.import-export-modal', ['type' => 'recipe', 'label' => 'Resep'])

                <a href="{{ route('manager.inventory.recipes.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Resep Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Overview --}}
    @if(count($groupedBoms) > 0)
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <i class="ti ti-package text-2xl text-emerald-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Produk</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ count($groupedBoms) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                        <i class="ti ti-list-details text-2xl text-blue-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Varian</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ count($hppPerVariant) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                        <i class="ti ti-cash text-2xl text-amber-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Rata-rata HPP</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format(count($hppPerVariant) > 0 ? array_sum($hppPerVariant) / count($hppPerVariant) : 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center">
                        <i class="ti ti-box text-2xl text-gray-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Bahan</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $boms->pluck('stock_id')->unique()->count() }}</p>
                    </div>
                </div>
            </div>
    </div>
    @endif

    {{-- Type Filter Toggle --}}
    <div class="flex items-center mb-5">
        <div class="inline-flex items-center bg-gray-100/80 p-1 rounded-xl gap-0.5">
            <button
                @click="recipeFilter = 'all'"
                :class="recipeFilter === 'all' ? 'bg-white text-gray-800 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-medium rounded-lg transition-all duration-150">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                Semua
            </button>
            <button
                @click="recipeFilter = 'prod'"
                :class="recipeFilter === 'prod' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-medium rounded-lg transition-all duration-150">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                Produk Jadi
            </button>
            <button
                @click="recipeFilter = 'semi'"
                :class="recipeFilter === 'semi' ? 'bg-white text-purple-700 shadow-sm ring-1 ring-gray-200/60' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-5 py-2 text-sm font-medium rounded-lg transition-all duration-150">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                Setengah Jadi
            </button>
        </div>
    </div>

    {{-- Recipe List --}}
    <div class="space-y-6">
        @php
            $sortedBoms = collect($groupedBoms)->sortBy(function($sizes, $outputKey) use ($outputNames) {
                return strtolower($outputNames[$outputKey] ?? '');
            });
        @endphp

        @forelse ($sortedBoms as $outputKey => $sizes)
            @php
                $parts = explode(':', $outputKey);
                $type = $parts[0] ?? '';
                $id = $parts[1] ?? null;
                $displayName = $outputNames[$outputKey] ?? 'Produk Tidak Diketahui';
            @endphp
            <div 
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden transition-all hover:shadow-md"
                x-data="{ open: false }"
                x-show="(recipeFilter === 'all' || recipeFilter === '{{ $type }}') && (!search || '{{ strtolower($displayName) }}'.includes(search.toLowerCase()))"
                x-transition
            >
                {{-- Product Card Header --}}
                <div class="px-6 py-4 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/30 cursor-pointer"
                     @click="open = !open">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100/50 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-900">{{ $displayName }}</h2>
                            <p class="text-xs text-gray-500 font-medium">{{ count($sizes) }} varian ukuran</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center self-end sm:self-auto gap-2">
                        <button type="button" @click.prevent.stop="open = !open" class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors" aria-expanded="false" :aria-expanded="open">
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            <span x-text="open ? 'Tutup Rincian' : 'Buka Rincian'"></span>
                        </button>
                        @if($id)
                        <a href="{{ route('manager.inventory.recipes.edit', ['product' => $id]) }}?output_type={{ $type }}" 
                           @click.stop
                           class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Edit
                        </a>
                        <div class="h-4 w-px bg-gray-200 mx-1"></div>
                         <form action="{{ route('manager.inventory.recipes.destroy-product', ['product' => $id]) }}?output_type={{ $type }}" method="POST" onsubmit="return confirmDelete(this)">
                            @csrf @method('DELETE')
                            <button type="submit" @click.stop class="inline-flex items-center gap-2 px-3.5 py-1.5 text-sm font-medium text-red-600 bg-white border border-red-100 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Variants Table --}}
                 <div class="overflow-x-auto transform origin-top" x-show="open" x-cloak x-collapse
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/50 text-gray-500 font-medium text-xs uppercase tracking-wider border-b border-gray-50">
                            <tr>
                                <th class="px-6 py-3 w-[180px]">Varian</th>
                                <th class="px-6 py-3">Komposisi Bahan</th>
                                <th class="px-6 py-3 text-right w-[120px]">HPP</th>
                                <th class="px-6 py-3 text-right w-[120px]">Harga Jual</th>
                                <th class="px-6 py-3 text-right w-[120px]">Laba / Unit</th>
                                <th class="px-6 py-3 text-right w-[120px]">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($sizes as $sizeId => $bomItems)
                                @php
                                    $hpp = $hppPerVariant[$sizeId] ?? 0;
                                    $sellPrice = $variantPrices[$sizeId] ?? 0;
                                    $profit = $sellPrice - $hpp;
                                    $markup = $hpp > 0 ? ($profit / $hpp * 100) : 0;
                                    $margin = $sellPrice > 0 ? (($sellPrice - $hpp) / $sellPrice * 100) : 0;
                                    // Margin Color Logic
                                    $marginClass = 'bg-gray-100 text-gray-600';
                                    if ($margin >= 50) $marginClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20';
                                    elseif ($margin >= 30) $marginClass = 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20';
                                    elseif ($margin > 0) $marginClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20';
                                    // Profit color
                                    $profitClass = $profit >= 0 ? 'text-emerald-700' : 'text-red-600';
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4 align-top">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-700/10">
                                            {{ $sizesInfo[$sizeId] ?? 'Default' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($bomItems as $bom)
                                                @php
                                                    $ingredientName = $bom->semi_finished_product_id
                                                        ? ($bom->semiFinishedProduct->name ?? 'Deleted SFP')
                                                        : ($bom->stock->name ?? 'Deleted Stock');
                                                    $dotColor = $bom->semi_finished_product_id ? 'bg-purple-400' : 'bg-emerald-400';
                                                @endphp
                                                <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-white border border-gray-200 rounded-md shadow-sm text-xs text-gray-700">
                                                     <div class="w-1.5 h-1.5 rounded-full {{ $dotColor }} flex-shrink-0"></div>
                                                    <span class="font-medium truncate max-w-[10rem]">{{ $ingredientName }}</span>
                                                    <span class="text-gray-400 mx-1">|</span>
                                                    <span class="font-mono text-gray-900 font-semibold ml-auto text-right">{{ rtrim(rtrim(number_format($bom->quantity_required, 2), '0'), '.') }}</span>
                                                    <span class="text-gray-500 ml-0.5">{{ $bom->unit->symbol ?? '' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right align-top">
                                        <div class="font-mono text-gray-900 font-medium">Rp {{ number_format($hpp, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right align-top">
                                        <div class="font-mono text-gray-500">Rp {{ number_format($sellPrice, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right align-top">
                                        <div class="font-mono font-semibold {{ $profitClass }}">Rp {{ number_format($profit, 0, ',', '.') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right align-top">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ $marginClass }}">
                                            {{ number_format($margin, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Belum ada resep</h3>
                <p class="text-gray-500 max-w-sm mx-auto mt-1 mb-6">Tambahkan resep pertama Anda untuk mulai menghitung HPP produk.</p>
                <a href="{{ route('manager.inventory.recipes.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-all hover:scale-[1.02]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Buat Resep Baru
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(form, msg) {
        msg = msg || 'Yakin ingin menghapus resep ini?';
        const confirmed = confirm(msg);
        if (confirmed) {
            window.dispatchEvent(new Event('loading-start'));
        } else {
            setTimeout(() => window.dispatchEvent(new Event('loading-end')), 500);
        }
        return confirmed;
    }

    // Ctrl+K / Cmd+K to focus recipe search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            const el = document.getElementById('recipe-search');
            if (el) {
                e.preventDefault();
                el.focus();
                el.select && el.select();
            }
        }
    });
</script>
@endpush
