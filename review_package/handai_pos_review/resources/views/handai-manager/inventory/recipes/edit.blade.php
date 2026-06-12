@extends('handai-manager.layouts.master')

@section('title', 'Edit Resep - ' . ($isSemi ?? false ? ('Produk Setengah Jadi (ID ' . ($outputId ?? '') . ')') : ($product->name ?? '')))

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .rcp-input, .rcp-select {
        height: 40px; border-radius: 10px; border: 1px solid #e6edf0; background: #fbfcfd;
        padding: 0 12px; font-size: 13px; color: #334155; transition: border-color .12s, box-shadow .12s; width: 100%;
    }
    .rcp-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }
    .rcp-input:focus, .rcp-select:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); }
    .auto-badge { font-size: 10px; padding: 2px 6px; border-radius: 6px; background: #eef2ff; color: #1e40af; font-weight: 700; line-height: 1.2; }
    .hpp-card { background: linear-gradient(135deg, #fbfff9 0%, #f6fff7 100%); border: 1px solid #ecfdf3; }
    [x-cloak] { display: none !important; }
    .card-tight { padding: 1rem; }
    .card-loose { padding: 1.25rem; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 max-w-6xl mx-auto"
    x-data="recipeEdit()" x-cloak>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('manager.inventory.recipes.index') }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            @php $type = request('output_type','finished'); if ($type === 'prod') $type = 'finished'; $isSemi = $type === 'semi'; @endphp
            <h1 class="text-lg font-semibold text-gray-800">Edit Resep: {{ $isSemi ? 'Produk Setengah Jadi (ID '.$outputId.')' : $product->name }}</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">{{ $isSemi ? 'Perbarui resep untuk produk setengah jadi ini' : 'Perbarui resep untuk semua varian sekaligus' }}</p>
        </div>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <ul class="text-[12px] text-red-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li class="flex items-start gap-1.5">
                        <svg class="w-3.5 h-3.5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manager.inventory.recipes.update', $outputId) }}?output_type={{ $type }}" method="POST" @submit.prevent="submitForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="output_type" value="{{ $type }}">
        <input type="hidden" name="product_id" value="{{ $isSemi ? '' : $product->id }}" {{ $isSemi ? 'disabled' : '' }}>
        <input type="hidden" name="semi_finished_output_id" value="{{ $isSemi ? $outputId : '' }}" {{ $isSemi ? '' : 'disabled' }}">

        {{-- Product Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-semibold text-gray-800">{{ $isSemi ? 'Produk Setengah Jadi (ID ' . $outputId . ')' : $product->name }}</h2>
                    @if(!$isSemi)
                    <p class="text-[12px] text-gray-400"><span x-text="variants.length"></span> varian</p>
                    @endif
                </div>
            </div>
        </div>


        {{-- Variants & Multipliers --}}
        @if(!$isSemi)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">1</div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Varian & Rasio Kelipatan</h2>
                </div>
                <span class="text-[11px] text-gray-400">Klik varian untuk set sebagai basis (1x)</span>
            </div>

            <div class="flex flex-wrap gap-2 mb-3">
                <template x-for="(v, vi) in variants" :key="v.id">
                    <button type="button" @click="setBaseVariant(vi)"
                        :class="baseVariantIdx === vi ? 'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-emerald-50 hover:border-emerald-300'"
                        class="px-3 py-2 rounded-lg border text-[12px] font-medium transition flex items-center gap-2">
                        <span x-text="v.label"></span>
                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded"
                              :class="baseVariantIdx === vi ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500'"
                              x-text="baseVariantIdx === vi ? '1x (basis)' : getMultiplier(vi).toFixed(1) + 'x'"></span>
                    </button>
                </template>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 card-tight">
                <p class="text-[11px] text-gray-500 mb-2">
                    <svg class="w-3 h-3 inline -mt-0.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Edit rasio kelipatan jika perlu:
                </p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="(v, vi) in variants" :key="'mult-'+v.id">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-gray-500 truncate max-w-[100px]" x-text="v.label"></span>
                            <input type="number" step="0.1" min="0.1"
                                   :value="v.multiplier"
                                   @input="updateMultiplier(vi, $event.target.value)"
                                   class="w-16 h-7 rounded-md border border-gray-200 text-center text-[12px] font-mono focus:border-emerald-400 focus:ring-1 focus:ring-emerald-100"
                                   :disabled="vi === baseVariantIdx">
                            <span class="text-[11px] text-gray-400">x</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        @endif

        {{-- Ingredients --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">
                        <span x-text="variants.length ? 2 : 1"></span>
                    </div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Daftar Bahan</h2>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-[11px] text-gray-400">Isi jumlah basis, varian lain otomatis terhitung</span>
                    <button type="button" @click="showNewStockModal = true"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-transparent hover:border-blue-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Bahan Baru</span>
                    </button>
                    <button type="button" @click="addIngredient()"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-transparent hover:border-emerald-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Bahan</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto -mx-5 px-5">
                <table class="w-full" x-show="outputType==='semi' ? ingredients.filter(i => i.type !== 'kemasan').length > 0 : ingredients.filter(i => (i.type || 'bahan') === 'bahan').length > 0">
                    <thead>
                        <tr class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            <th class="text-left pb-2 pr-2 w-[200px]">Bahan Baku</th>
                            <th class="text-left pb-2 pr-2 w-[80px]">Satuan</th>
                            <template x-for="(v, vi) in variants" :key="'th-'+v.id">
                                <th class="text-center pb-2 px-1 min-w-[100px]">
                                    <span x-text="v.label" class="truncate block max-w-[120px]"></span>
                                    <span class="auto-badge" x-text="baseVariantIdx === vi ? 'BASIS' : getMultiplier(vi).toFixed(1) + 'x'"></span>
                                </th>
                            </template>
                            <template x-if="variants.length === 0">
                                <th class="text-center pb-2 px-1 min-w-[100px]">Jumlah</th>
                            </template>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(ing, ii) in ingredients" :key="ii">
                            <tr class="border-t border-gray-50"
                                x-show="outputType==='semi' ? true : ((ing.type || 'bahan') === 'bahan')">
                                <td class="py-2 pr-2">
                                    <select :id="'stock-'+ii" class="rcp-select stock-select"
                                            x-model="ing.stock_id" @change="onStockChange(ii)">
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach ($stocks->sortBy('name') as $stock)
                                            <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}" data-unit-id="{{ $stock->unit_id }}">{{ $stock->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !ing.stock_id">Pilih bahan.</p>
                                </td>
                                <td class="py-2 pr-2">
                                    <select :id="'unit-'+ii" class="rcp-select unit-select" x-model="ing.unit_id" @change="recalcHpp()">
                                        <option value="">Satuan</option>
                                        @foreach ($units->sortBy('symbol') as $unit)
                                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !ing.unit_id">Pilih satuan.</p>
                                </td>
                                <template x-if="variants.length === 0">
                                    <td class="py-2 px-1">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   :value="getIngredientQty(ii, 0)"
                                                   @input="setIngredientQty(ii, 0, $event.target.value)"
                                                   @focus="$event.target.select()"
                                                   class="rcp-input text-center text-[12px] font-mono"
                                                   placeholder="0">
                                            <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !(getIngredientQty(ii,0) > 0)">Isi jumlah bahan.</p>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')" class="text-emerald-500"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, 0))" class="font-medium text-emerald-700"></div>
                                        </div>
                                    </td>
                                </template>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="removeIngredient(ii)"
                                        class="w-7 h-7 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- message shown when no bahan present, positioned outside scroll container for clarity -->
            <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'bahan').length === 0" class="py-10 text-center">
                <p class="text-[12px] text-gray-400 mb-2">Belum ada bahan. Klik tombol "Tambah Bahan" di atas.</p>
            </div>
        </div>

        {{-- Semi-Finished Products Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4" x-show="outputType === 'finished' || outputType === 'prod'">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-[11px] font-bold">
                    <span x-text="variants.length ? 3 : 2"></span>
                </div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Produk Setengah Jadi</h2>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-[11px] text-gray-400">Gunakan produk setengah jadi sebagai bahan</span>
                    <button type="button" @click="addIngredient('semi_finished')"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg border border-transparent hover:border-purple-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Setengah Jadi</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto -mx-5 px-5">
                <table class="w-full" x-show="ingredients.filter(i => i.type === 'semi_finished').length > 0">
                    <thead>
                        <tr class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            <th class="text-left pb-2 pr-2 w-[200px]">Produk Setengah Jadi</th>
                            <th class="text-left pb-2 pr-2 w-[80px]">Satuan</th>
                            <template x-for="(v, vi) in variants" :key="'th-sf-'+v.id">
                                <th class="text-center pb-2 px-1 min-w-[100px]">
                                    <span x-text="v.label" class="truncate block max-w-[120px]"></span>
                                    <span class="auto-badge" x-text="baseVariantIdx === vi ? 'BASIS' : getMultiplier(vi).toFixed(1) + 'x'"></span>
                                </th>
                            </template>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(ing, ii) in ingredients" :key="'sf-'+ii">
                            <tr class="border-t border-gray-50" x-show="ing.type === 'semi_finished'">
                                <td class="py-2 pr-2">
                                    <select class="rcp-select" x-model="ing.sfp_id" @change="onSfpChange(ii)">
                                        <option value="">-- Pilih Produk Setengah Jadi --</option>
                                        @foreach ($semiFinishedProducts as $sfp)
                                            <option value="{{ $sfp->id }}" data-unit-id="{{ $sfp->unit_id }}">{{ $sfp->name }} (HPP: Rp {{ number_format($sfp->price_per_unit, 0, ',', '.') }}/{{ $sfp->unit->symbol ?? '' }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-2 pr-2">
                                    <select class="rcp-select" x-model="ing.unit_id" @change="recalcHpp()">
                                        <option value="">Satuan</option>
                                        @foreach ($units->sortBy('symbol') as $unit)
                                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <template x-if="variants.length === 0">
                                    <td class="py-2 px-1">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   :value="getIngredientQty(ii, 0)"
                                                   @input="setIngredientQty(ii, 0, $event.target.value)"
                                                   @focus="$event.target.select()"
                                                   class="rcp-input text-center text-[12px] font-mono"
                                                   placeholder="0">
                                            <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !(getIngredientQty(ii,0) > 0)">Isi jumlah bahan.</p>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1 text-center" x-show="ing.sfp_id && ing.unit_id">
                                            <div x-text="'Harga: Rp ' + formatNumber(getSfpIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getSfpIngredientCost(ii, 0))" class="font-medium text-purple-700"></div>
                                            <div x-show="sfpPrices[ing.sfp_id]" class="text-[11px] text-gray-500 mt-0.5">
                                                <span class="text-gray-400">Upah/unit:</span>
                                                <span class="font-mono text-purple-700"> Rp <span x-text="formatNumber( ( (sfpPrices[ing.sfp_id]?.labor_cost || 0) / (sfpPrices[ing.sfp_id]?.output_qty || 1) ).toFixed(2) )"></span></span>
                                            </div>
                                        </div>
                                    </td>
                                </template>
                                <template x-for="(v, vi) in variants" :key="'qty-sf-'+v.id+'-'+ii">
                                    <td class="py-2 px-1">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   :value="getIngredientQty(ii, vi)"
                                                   @input="setIngredientQty(ii, vi, $event.target.value)"
                                                   @focus="$event.target.select()"
                                                   class="rcp-input text-center text-[12px] font-mono"
                                                   :class="vi !== baseVariantIdx && !ing.manualOverride?.[v.id] ? 'bg-purple-50/50 border-purple-100' : ''"
                                                   placeholder="0">
                                            <span x-show="vi !== baseVariantIdx && !ing.manualOverride?.[v.id]"
                                                  class="absolute -top-1 -right-1 w-3 h-3 bg-purple-400 rounded-full flex items-center justify-center"
                                                  title="Auto-calculated">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1 text-center" x-show="ing.sfp_id && ing.unit_id">
                                            <div x-text="'Harga: Rp ' + formatNumber(getSfpIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getSfpIngredientCost(ii, vi))" class="font-medium text-purple-700"></div>
                                            <div x-show="sfpPrices[ing.sfp_id]" class="text-[11px] text-gray-500 mt-0.5">
                                                <span class="text-gray-400">Upah/unit:</span>
                                                <span class="font-mono text-purple-700"> Rp <span x-text="formatNumber( ( (sfpPrices[ing.sfp_id]?.labor_cost || 0) / (sfpPrices[ing.sfp_id]?.output_qty || 1) ).toFixed(2) )"></span></span>
                                            </div>
                                        </div>
                                    </td>
                                </template>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="removeIngredient(ii)"
                                        class="w-7 h-7 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div x-show="ingredients.filter(i => i.type === 'semi_finished').length === 0" class="py-6 text-center text-sm text-gray-400">Belum ada produk setengah jadi.</div>
            </div>
        </div>

        {{-- Packaging (Kemasan) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-[11px] font-bold">
                    <span x-text="variants.length ? 4 : 3"></span>
                </div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Daftar Kemasan</h2>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-[11px] text-gray-400">Kelola daftar kemasan terpisah dari bahan baku</span>
                    <button type="button" @click="showNewStockModal = true"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-transparent hover:border-blue-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Kemasan Baru</span>
                    </button>
                    <button type="button" @click="addIngredient('kemasan')"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-transparent hover:border-amber-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Kemasan</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto -mx-5 px-5">
                <table class="w-full" x-show="ingredients.filter(i => (i.type || 'bahan') === 'kemasan').length > 0">
                    <thead>
                        <tr class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            <th class="text-left pb-2 pr-2 w-[200px]">Kemasan</th>
                            <th class="text-left pb-2 pr-2 w-[80px]">Satuan</th>
                            <template x-if="variants.length === 0">
                                <th class="text-center pb-2 px-1 min-w-[100px]">Jumlah</th>
                            </template>
                            <template x-for="(v, vi) in variants" :key="'th-km-'+v.id">
                                <th class="text-center pb-2 px-1 min-w-[100px]">
                                    <span x-text="v.label" class="truncate block max-w-[120px]"></span>
                                    <span class="auto-badge" x-text="baseVariantIdx === vi ? 'BASIS' : getMultiplier(vi).toFixed(1) + 'x'"></span>
                                </th>
                            </template>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(ing, ii) in ingredients" :key="'km-'+ii">
                            <tr class="border-t border-gray-50" x-show="(ing.type || 'bahan') === 'kemasan'">
                                <td class="py-2 pr-2">
                                    <select :id="'stock-km-'+ii" class="rcp-select stock-select"
                                            x-model="ing.stock_id" @change="onStockChange(ii)">
                                        <option value="">-- Pilih Kemasan --</option>
                                        @foreach ($stocks->sortBy('name') as $stock)
                                            <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}" data-unit-id="{{ $stock->unit_id }}">{{ $stock->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !ing.stock_id">Pilih kemasan.</p>
                                </td>
                                <td class="py-2 pr-2">
                                    <select :id="'unit-km-'+ii" class="rcp-select unit-select" x-model="ing.unit_id" @change="recalcHpp()">
                                        <option value="">Satuan</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !ing.unit_id">Pilih satuan.</p>
                                </td>
                                <template x-if="variants.length === 0">
                                    <td class="py-2 px-1">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   :value="getIngredientQty(ii, 0)"
                                                   @input="setIngredientQty(ii, 0, $event.target.value)"
                                                   @focus="$event.target.select()"
                                                   class="rcp-input text-center text-[12px] font-mono"
                                                   placeholder="0">
                                            <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !(getIngredientQty(ii,0) > 0)">Isi jumlah bahan.</p>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')" class="text-amber-500"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, 0))" class="font-medium text-amber-700"></div>
                                        </div>
                                    </td>
                                </template>
                                <template x-for="(v, vi) in variants" :key="'qty-km-'+v.id+'-'+ii">
                                    <td class="py-2 px-1">
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0"
                                                   :value="getIngredientQty(ii, vi)"
                                                   @input="setIngredientQty(ii, vi, $event.target.value)"
                                                   @focus="$event.target.select()"
                                                   class="rcp-input text-center text-[12px] font-mono"
                                                   :class="vi !== baseVariantIdx && !ing.manualOverride?.[v.id] ? 'bg-blue-50/50 border-blue-100' : ''"
                                                   placeholder="0">
                                                <template x-if="vi === baseVariantIdx">
                                                    <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !hasAnyQty(ii)">Isi minimal satu kolom jumlah untuk bahan ini.</p>
                                                </template>
                                            <span x-show="vi !== baseVariantIdx && !ing.manualOverride?.[v.id]"
                                                  class="absolute -top-1 -right-1 w-3 h-3 bg-blue-400 rounded-full flex items-center justify-center"
                                                  title="Auto-calculated">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                        </div>
                                            <div class="text-[11px] text-gray-500 mt-1">
                                                <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')" class="text-amber-500"></div>
                                                <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, vi))" class="font-medium text-amber-700"></div>
                                            </div>
                                    </td>
                                </template>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="removeIngredient(ii)"
                                        class="w-7 h-7 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'kemasan').length === 0" class="py-10 text-center">
                    <p class="text-[12px] text-gray-400 mb-2">Belum ada kemasan. Klik tombol "Tambah Kemasan" di atas.</p>
                </div>
            </div>
        </div>

        {{-- Standar Produksi Batch (semi output only) --}}
        <div x-show="outputType === 'semi'" class="bg-white rounded-xl shadow-sm border border-orange-100 p-5 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[11px] font-bold">
                    <span x-text="variants.length ? 5 : 4"></span>
                </div>
                <div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Standar Produksi Batch</h2>
                    <p class="text-[11px] text-gray-400">HPP per unit = (total bahan + upah) ÷ output per batch</p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Output per Batch Resep Ini <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-2 max-w-sm">
                    <input type="number" name="semi_output_qty" x-model.number="semiOutputQty" step="0.001" min="0.001"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-orange-300" placeholder="1000">
                    <span class="text-xs text-gray-600 font-medium whitespace-nowrap px-2 py-2 bg-orange-50 border border-orange-100 rounded-lg">{{ $isSemi ? ($sfpOutput->unit->symbol ?? 'unit') : 'unit' }}</span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Komposisi bahan di atas menghasilkan berapa [satuan]?</p>
            </div>
        </div>

        {{-- Upah Produksi Section --}}
        <div x-show="ingredients.length > 0" class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[11px] font-bold">
                    <span x-text="variants.length ? 6 : 5"></span>
                </div>
                <div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Upah Produksi</h2>
                    <p class="text-[11px] text-gray-400">Biaya tenaga kerja yang dihitung ke dalam HPP</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div x-show="outputType === 'finished'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Upah Produksi Produk Jadi (per unit)</label>
                    <div class="relative">
                        <input type="number" name="wage_per_unit" x-model.number="wageFinished" step="1" min="0"
                            class="w-full px-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-indigo-300" placeholder="2000">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Upah untuk menghasilkan 1 unit produk jadi. Misal: membuat 1 menu → Rp 2.000</p>
                </div>
                <div x-show="outputType === 'semi'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Upah Produksi Produk Setengah Jadi (per batch)</label>
                    <div class="relative">
                        <input type="number" name="semi_labor_cost" x-model.number="laborCost" step="1" min="0"
                            class="w-full px-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:border-indigo-300" placeholder="5000">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Upah untuk mengolah 1 batch. HPP per unit = (bahan + upah) ÷ output per batch (<span x-text="(parseFloat(semiOutputQty)||1)"></span> {{ $isSemi ? ($sfpOutput->unit->symbol ?? 'unit') : 'unit' }})</p>
                </div>
            </div>
        </div>

        {{-- HPP Summary (show whenever recipe has ingredients) --}}
        <template x-if="ingredients.length > 0">
        <div class="hpp-card rounded-xl shadow-sm border border-emerald-100 p-6 mb-4">
                <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">
                    <span x-text="variants.length ? 7 : 6"></span>
                </div>
                <h2 class="text-[13px] font-semibold text-emerald-800">Ringkasan HPP (Harga Pokok Produksi)</h2>
            </div>

            <div class="grid gap-3" :style="'grid-template-columns: repeat(' + (variants.length || 1) + ', minmax(0, 1fr))'">
                <template x-if="variants.length === 0">
                    <div class="bg-white rounded-lg border border-emerald-100 p-4">
                        <template x-if="outputType === 'semi'">
                            <div>
                                <p class="text-[11px] text-gray-500 mb-0.5">HPP per {{ $isSemi ? ($sfpOutput->unit->symbol ?? 'unit') : 'unit' }}</p>
                                <p class="text-lg font-bold text-emerald-700 font-mono" x-text="'Rp ' + formatNumber(calcHpp(0))"></p>
                                <div class="mt-2 pt-2 border-t border-emerald-50 space-y-1 text-[11px]">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Total bahan batch</span>
                                        <span class="font-mono text-gray-600" x-text="'Rp ' + formatNumber(calcBahanTotal())"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">+ Upah batch</span>
                                        <span class="font-mono text-gray-600" x-text="'Rp ' + formatNumber(laborCost||0)"></span>
                                    </div>
                                    <div class="flex justify-between font-medium border-t border-emerald-50 pt-1">
                                        <span class="text-gray-500">÷ Output batch</span>
                                        <span class="font-mono text-gray-600" x-text="(parseFloat(semiOutputQty)||1) + ' {{ $isSemi ? ($sfpOutput->unit->symbol ?? 'unit') : 'unit' }}'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="outputType !== 'semi'">
                            <div>
                                <p class="text-lg font-bold text-emerald-700 font-mono" x-text="'Rp ' + formatNumber(calcHpp(0))"></p>
                                <div class="mt-2 pt-2 border-t border-emerald-50 space-y-1 text-[11px]" x-show="wageFinished > 0">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Bahan + Kemasan</span>
                                        <span class="font-mono text-gray-600" x-text="'Rp ' + formatNumber(calcBahanTotal())"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">+ Upah produksi</span>
                                        <span class="font-mono text-indigo-600" x-text="'Rp ' + formatNumber(wageFinished)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-for="(v, vi) in variants" :key="'hpp-'+v.id">
                        <div class="bg-white rounded-lg border border-emerald-100 p-4">
                        <p class="text-[11px] text-gray-500 font-medium mb-1 truncate" x-text="v.label"></p>
                        <p class="text-lg font-bold text-emerald-700 font-mono" x-text="'Rp ' + formatNumber(calcHpp(vi))"></p>
                        <div class="mt-2 pt-2 border-t border-gray-100 space-y-1">
                            <div class="flex justify-between text-[11px]" x-show="wageFinished > 0">
                                <span class="text-gray-400">Upah /unit</span>
                                <span class="font-mono text-indigo-600" x-text="'Rp ' + formatNumber(wageFinished)"></span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-gray-400">Harga Jual</span>
                                <span class="text-gray-600 font-mono" x-text="'Rp ' + formatNumber(v.price)"></span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-gray-400">Margin</span>
                                <span class="font-semibold font-mono"
                                      :class="v.price > 0 && ((v.price - calcHpp(vi)) / v.price * 100) >= 50 ? 'text-emerald-600' : ((v.price - calcHpp(vi)) / v.price * 100) >= 30 ? 'text-amber-600' : 'text-red-600'"
                                      x-text="v.price > 0 ? (((v.price - calcHpp(vi)) / v.price * 100).toFixed(1) + '%') : '-'"></span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-gray-400">Profit</span>
                                <span class="font-mono text-gray-600" x-text="'Rp ' + formatNumber(v.price - calcHpp(vi))"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        </template>

        {{-- Bahan / Kemasan / Setengah Jadi Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4" x-show="ingredients.length > 0">
            <div class="bg-white rounded-lg border border-gray-100 p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[12px] font-semibold text-gray-700">Daftar Bahan Baku</h3>
                    <div class="text-[12px] font-mono text-gray-700">Rp <span x-text="formatNumber(getTypeTotal('bahan'))"></span></div>
                </div>
                <div class="text-[12px] text-gray-500 space-y-1">
                    <template x-for="(ing, ii) in ingredients.filter(i => (i.type || 'bahan') === 'bahan')" :key="'bb-'+ii">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[240px]" x-text="(stockPrices[ing.stock_id]?.name || '—') + ' · ' + (ing.baseQty || 0) + ' ' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                            <div class="font-mono text-gray-700">Rp <span x-text="formatNumber(getIngredientCost(ii, baseVariantIdx))"></span></div>
                        </div>
                    </template>
                    <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'bahan').length === 0" class="text-[12px] text-gray-400">Belum ada bahan baku.</div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-purple-100 p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[12px] font-semibold text-purple-700">Produk Setengah Jadi</h3>
                    <div class="text-[12px] font-mono text-purple-700">Rp <span x-text="formatNumber(getTypeTotal('semi_finished'))"></span></div>
                </div>
                <div class="text-[12px] text-gray-500 space-y-1">
                    <template x-for="(ing, ii) in ingredients.filter(i => i.type === 'semi_finished')" :key="'sf-tot-'+ii">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[240px]" x-text="(sfpPrices[ing.sfp_id]?.name || '—') + ' · ' + (ing.baseQty || 0) + ' ' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                            <div class="font-mono text-purple-700">Rp <span x-text="formatNumber(getSfpIngredientCost(ii, baseVariantIdx))"></span></div>
                        </div>
                    </template>
                    <div x-show="ingredients.filter(i => i.type === 'semi_finished').length === 0" class="text-[12px] text-gray-400">Belum ada.</div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-100 p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[12px] font-semibold text-gray-700">Daftar Kemasan</h3>
                    <div class="text-[12px] font-mono text-gray-700">Rp <span x-text="formatNumber(getTypeTotal('kemasan'))"></span></div>
                </div>
                <div class="text-[12px] text-gray-500 space-y-1">
                    <template x-for="(ing, ii) in ingredients.filter(i => (i.type || 'bahan') === 'kemasan')" :key="'km-'+ii">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[240px]" x-text="(stockPrices[ing.stock_id]?.name || '—') + ' · ' + (ing.baseQty || 0) + ' ' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                            <div class="font-mono text-gray-700">Rp <span x-text="formatNumber(getIngredientCost(ii, baseVariantIdx))"></span></div>
                        </div>
                    </template>
                    <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'kemasan').length === 0" class="text-[12px] text-gray-400">Belum ada kemasan.</div>
                </div>
            </div>
        </div>

        {{-- Rincian Upah Produksi --}}
        <div class="bg-white rounded-lg border border-indigo-100 p-4 mt-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-[13px] font-semibold text-gray-700">Rincian Upah Produksi</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-[13px]">
                <div class="p-3 bg-indigo-50 rounded-lg">
                    <div class="text-xs text-gray-500">Upah / unit (Produk Jadi)</div>
                    <div class="font-mono text-indigo-700 text-lg mt-1">Rp <span x-text="formatNumber(wageFinished)"></span></div>
                </div>
                <div class="p-3 bg-indigo-50 rounded-lg">
                    <div class="text-xs text-gray-500">Upah / batch (Setengah Jadi)</div>
                    <div class="font-mono text-indigo-700 text-lg mt-1">Rp <span x-text="formatNumber(laborCost)"></span></div>
                </div>
                <div class="p-3 bg-indigo-50 rounded-lg">
                    <div class="text-xs text-gray-500">Upah setara / unit (Semi → per unit)</div>
                    <div class="font-mono text-indigo-700 text-lg mt-1">Rp <span x-text="formatNumber(Math.round(((parseFloat(laborCost)||0) / (parseFloat(semiOutputQty)||1)) * 100) / 100)"></span></div>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-500">Catatan: Nilai upah per unit untuk produk jadi diambil dari field "Upah Produksi". Untuk produk setengah jadi, upah batch dibagi dengan output per batch untuk menghasilkan upah per unit setara.</div>
        </div>

        <template x-if="ingredients.filter(i => i.type === 'semi_finished').length > 0">
            <div class="mt-3 bg-white rounded-lg border border-indigo-50 p-3">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Rincian Upah untuk Bahan Setengah Jadi</h4>
                <div class="space-y-2 text-[13px] text-gray-700">
                    <template x-for="sfpId in [...new Set(ingredients.filter(i => i.type === 'semi_finished').map(i => i.sfp_id))]" :key="sfpId">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[60%]" x-text="(sfpPrices[sfpId]?.name || '—')"></div>
                            <div class="font-mono text-indigo-700">Rp <span x-text="formatNumber( ((sfpPrices[sfpId]?.labor_cost||0) / (sfpPrices[sfpId]?.output_qty||1)).toFixed(2) )"></span></div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Rekap Upah Produksi (paling bawah) --}}
        <div class="mt-4 bg-white rounded-lg border border-emerald-50 p-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Rekap Upah Produksi</h4>
            <div class="space-y-2 text-[13px] text-gray-700">
                <div class="flex items-center justify-between">
                    <div x-text="outputType === 'semi' ? 'Upah Produk Setengah Jadi (per batch)' : 'Upah Produk Jadi (per unit)'"></div>
                    <div class="font-mono">Rp <span x-text="formatNumber(outputType === 'semi' ? laborCost : wageFinished)"></span></div>
                </div>
                <template x-if="ingredients.filter(i => i.type === 'semi_finished').length > 0">
                    <template x-for="sfpId in [...new Set(ingredients.filter(i => i.type === 'semi_finished').map(i => i.sfp_id))]" :key="'recap-'+sfpId">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[60%]">Upah dari: <span x-text="(sfpPrices[sfpId]?.name || '—')"></span></div>
                            <div class="font-mono">Rp <span x-text="formatNumber((function(){ let sum=0; ingredients.forEach((ing,ii)=>{ if(ing.type==='semi_finished' && ing.sfp_id==sfpId){ sum += getSfpWageContribution(ii, baseVariantIdx); } }); return sum; })())"></span></div>
                        </div>
                    </template>
                </template>
                <div class="border-t pt-2 flex items-center justify-between text-sm font-semibold">
                    <div>Total Upah per Unit (rekap)</div>
                    <div class="font-mono text-emerald-700">Rp <span x-text="formatNumber(getTotalProductionWagePerBaseUnit())"></span></div>
                </div>
            </div>
        </div>

        {{-- Hidden form fields --}}
        <template x-if="variants.length > 0">
            <div>
                <template x-for="(v, vi) in variants" :key="'hidden-'+v.id">
                    <div>
                        <template x-for="(ing, ii) in ingredients" :key="'h-'+v.id+'-'+ii">
                            <div>
                                <input type="hidden" :name="'variants['+v.id+'][ingredients]['+ii+'][stock_id]'" :value="ing.type === 'semi_finished' ? '' : ing.stock_id">
                                <input type="hidden" :name="'variants['+v.id+'][ingredients]['+ii+'][semi_finished_product_id]'" :value="ing.type === 'semi_finished' ? ing.sfp_id : ''">
                                <input type="hidden" :name="'variants['+v.id+'][ingredients]['+ii+'][quantity]'" :value="getIngredientQty(ii, vi)">
                                <input type="hidden" :name="'variants['+v.id+'][ingredients]['+ii+'][unit_id]'" :value="ing.unit_id">
                                <input type="hidden" :name="'variants['+v.id+'][ingredients]['+ii+'][type]'" :value="ing.type || 'bahan'">
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="variants.length === 0">
            <div>
                <template x-for="(ing, ii) in ingredients" :key="'h-semi-'+ii">
                    <div>
                        <input type="hidden" :name="'variants[0][ingredients]['+ii+'][stock_id]'" :value="ing.type === 'semi_finished' ? '' : ing.stock_id">
                        <input type="hidden" :name="'variants[0][ingredients]['+ii+'][semi_finished_product_id]'" :value="ing.type === 'semi_finished' ? ing.sfp_id : ''">
                        <input type="hidden" :name="'variants[0][ingredients]['+ii+'][quantity]'" :value="getIngredientQty(ii, 0)">
                        <input type="hidden" :name="'variants[0][ingredients]['+ii+'][unit_id]'" :value="ing.unit_id">
                        <input type="hidden" :name="'variants[0][ingredients]['+ii+'][type]'" :value="ing.type || 'bahan'">
                    </div>
                </template>
            </div>
        </template>

        {{-- Submit --}}
        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('manager.inventory.recipes.index') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition">Batal</a>
            <button type="submit"
                class="h-9 px-5 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Simpan Perubahan
            </button>
        </div>
    </form>

    {{-- New Stock Modal --}}
    <div x-show="showNewStockModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm" style="display:none">
        <div @click.away="showNewStockModal = false" class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
            <h3 class="text-[15px] font-semibold text-gray-800 mb-4">Tambah Bahan Baru</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Nama Bahan</label>
                    <input type="text" x-model="newStock.name" class="rcp-input" placeholder="Contoh: Susu Full Cream" />
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Satuan</label>
                    <select x-model="newStock.unit_id" class="rcp-select">
                        <option value="">-- Pilih --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->symbol }} ({{ $unit->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Kategori</label>
                    <select x-model="newStock.category_id" class="rcp-select">
                        <option value="">-- Pilih --</option>
                        @foreach(\App\Models\StockCategory::orderBy('name')->get() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p x-show="newStockError" class="text-[12px] text-red-600 mt-2" x-text="newStockError"></p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="showNewStockModal = false" class="h-8 px-4 text-[12px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Batal</button>
                <button type="button" @click="saveNewStock()" class="h-8 px-4 text-[12px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
@endpush

@push('scripts')
<script>
function recipeEdit() {
    return {
        outputType: @json($type),
        variants: [],
        baseVariantIdx: 0,
        ingredients: [],
        attemptedSubmit: false,
        showNewStockModal: false,
        newStock: { name: '', unit_id: '', category_id: '' },
        newStockError: '',
        semiOutputQty: @json($isSemi ? max(1, (float)($sfpOutput->output_qty ?? 1)) : 1),
        laborCost: @json($isSemi ? (float)($sfpOutput->labor_cost ?? 0) : 0),
        wageFinished: @json(!$isSemi && $product ? (float)($product->wage_per_unit ?? 0) : 0),
        selectedProductId: @json((string)$outputId),

        stockPrices: @json($stockPrices),
        sfpPrices: @json($sfpPrices),
        conversions: @json($conversions),
        existingBoms: @json($existingBoms),
        rawVariants: @json($variants),
        unitsById: @json($units->mapWithKeys(function($u){ return [$u->id => ['symbol' => $u->symbol, 'name' => $u->name, 'unit_type' => $u->unit_type]];})),

        init() {
            // guard missing rawVariants
            if (!Array.isArray(this.rawVariants)) {
                console.warn('recipeEdit: rawVariants undefined, defaulting to []');
                this.rawVariants = [];
            }
            // Setup variants with multipliers (250ml=1, 500ml=2, 1000ml=4; cup/ice => custom)
            this.variants = this.rawVariants.map(v => ({
                ...v,
                ...(() => { const p = this.parseMultiplierFromLabel(v.label); return { multiplier: p.multiplier || 1, customMultiplier: !!p.custom }; })(),
            }));

            // Choose base variant as smallest auto-detected multiplier (ignore customMultiplier)
            const auto = this.variants.filter(v => !v.customMultiplier);
            let baseIdx = 0;
            if (auto.length > 0) {
                const minMult = Math.min(...auto.map(v => v.multiplier));
                baseIdx = this.variants.findIndex(v => !v.customMultiplier && v.multiplier === minMult);
                if (baseIdx === -1) baseIdx = 0;
            }
            this.baseVariantIdx = baseIdx;

            // For semi outputs we use a single base column (no variants)
            if (this.outputType === 'semi') {
                this.variants = [];
                this.baseVariantIdx = 0;
            }

            // Normalize multipliers relative to base (if we have any variants)
            let baseMult = 1;
            if (this.variants.length > 0) {
                baseMult = this.variants[this.baseVariantIdx].multiplier || 1;
                this.variants.forEach(v => { v.multiplier = (v.multiplier || 1) / baseMult; });
            }

            // Load existing BOMs into ingredients
            this.loadExistingBoms();
            // Prefill kemasan if none exist for this product (only for finished outputs)
            if (this.outputType === 'finished') {
                this.addDefaultKemasanIfMissing();
            }

            this.$watch('ingredients', () => this.recalcHpp(), { deep: true });
            this.$nextTick(() => { this.initChoices(); this.populateUnitDefaults(); });
        },

        isFormValid() {
            if (!this.ingredients || this.ingredients.length === 0) return false;
            for (let i = 0; i < this.ingredients.length; i++) {
                const ing = this.ingredients[i];
                const hasSource = ing.type === 'semi_finished' ? !!ing.sfp_id : !!ing.stock_id;
                if (!hasSource || !ing.unit_id) return false;
                if (this.variants.length > 0) {
                    let anyQty = false;
                    for (let vi = 0; vi < this.variants.length; vi++) {
                        const q = parseFloat(this.getIngredientQty(i, vi)) || 0;
                        if (q > 0) { anyQty = true; break; }
                    }
                    if (!anyQty) return false;
                }
            }
            return true;
        },


        // If ingredients have stock_id but missing unit_id, attempt to set unit_id from option data
        populateUnitDefaults() {
            this.ingredients.forEach((ing, ii) => {
                if (ing.stock_id && (!ing.unit_id || ing.unit_id === '')) {
                    const sel = document.querySelector("select.stock-select option[value='" + ing.stock_id + "']");
                    if (sel && sel.dataset && sel.dataset.unitId) {
                        ing.unit_id = String(sel.dataset.unitId);
                    }
                }
            });
        },

        hasAnyQty(ii) {
            if (this.variants.length === 0) {
                return parseFloat(this.getIngredientQty(ii, 0)) > 0;
            }
            for (let vi = 0; vi < this.variants.length; vi++) {
                const q = parseFloat(this.getIngredientQty(ii, vi)) || 0;
                if (q > 0) return true;
            }
            return false;
        },

        loadExistingBoms() {
            // Find the base variant's BOMs as the ingredient template. For semi outputs there are no variants,
            // so we just merge all existingBoms entries.
            let baseVid = this.variants[this.baseVariantIdx]?.id;
            let baseBoms = this.existingBoms[baseVid] || [];

            // if we have no base and there are other keys, pick first any
            if (baseBoms.length === 0) {
                // try any variant key
                for (let vid in this.existingBoms) {
                    if (this.existingBoms[vid] && this.existingBoms[vid].length > 0) {
                        baseBoms = this.existingBoms[vid];
                        break;
                    }
                }
            }

            // Build ingredients from base BOMs
            baseBoms.forEach(bom => {
                let ing = {
                    stock_id: bom.stock_id ? String(bom.stock_id) : '',
                    sfp_id: bom.semi_finished_product_id ? String(bom.semi_finished_product_id) : '',
                    unit_id: String(bom.unit_id),
                    baseQty: bom.quantity,
                    type: bom.type ? bom.type : (bom.is_packaging ? 'kemasan' : 'bahan'),
                    manualOverride: {},
                };

                // Check other variants for this ingredient - if qty differs from expected auto, mark as manual override
                this.variants.forEach((v, vi) => {
                    if (vi === this.baseVariantIdx) return;
                    let vBoms = this.existingBoms[v.id] || [];
                    let match;
                    if (bom.semi_finished_product_id) {
                        match = vBoms.find(b => b.semi_finished_product_id == bom.semi_finished_product_id);
                    } else {
                        match = vBoms.find(b => b.stock_id == bom.stock_id);
                    }
                    if (match) {
                        let expectedQty = Math.round(bom.quantity * v.multiplier * 100) / 100;
                        if (Math.abs(match.quantity - expectedQty) > 0.01) {
                            ing.manualOverride[v.id] = match.quantity;
                        }
                    }
                });

                this.ingredients.push(ing);
            });
        },

        parseMultiplierFromLabel(label) {
            if (!label) return { multiplier: 1, custom: false };
            const l = label.toLowerCase();
            if (l.includes('cup') || l.includes('gelas')) return { multiplier: 1, custom: true };
            if (l.includes('es') || l.includes('ice')) return { multiplier: 1, custom: true };

            const m = l.match(/(\d+\.?\d*)\s*ml/);
            if (m) {
                const val = parseFloat(m[1]);
                if (val === 250) return { multiplier: 1, custom: false };
                if (val === 500) return { multiplier: 2, custom: false };
                if (val === 1000) return { multiplier: 4, custom: false };
                return { multiplier: (val / 250), custom: false };
            }

            const num = l.match(/(\d+\.?\d*)/);
            if (num) return { multiplier: (parseFloat(num[1]) / 250), custom: false };
            return { multiplier: 1, custom: false };
        },

        setBaseVariant(idx) {
            let newBaseMult = this.variants[idx].multiplier;
            this.variants.forEach(v => { v.multiplier = v.multiplier / newBaseMult; });
            this.ingredients.forEach(ing => {
                ing.baseQty = ing.baseQty * newBaseMult;
                ing.manualOverride = {};
            });
            this.baseVariantIdx = idx;
        },

        updateMultiplier(vi, val) {
            this.variants[vi].multiplier = parseFloat(val) || 1;
            this.recalcHpp();
        },

        getMultiplier(vi) {
            return this.variants[vi]?.multiplier || 1;
        },

        addIngredient(type = 'bahan') {
            this.ingredients.push({ stock_id: '', sfp_id: '', unit_id: '', baseQty: 0, type: type, manualOverride: {} });
            this.$nextTick(() => this.initChoices());
        },

        removeIngredient(idx) {
            this.ingredients.splice(idx, 1);
        },

        onStockChange(ii) {
            const ing = this.ingredients[ii];
            const stockId = ing.stock_id;
            const sel = document.getElementById('stock-'+ii) || document.getElementById('stock-km-'+ii) || document.querySelector("select.stock-select option[value='" + stockId + "']");
            if (sel && sel.dataset && sel.dataset.unitId) {
                if (!ing.unit_id || ing.unit_id === '') ing.unit_id = String(sel.dataset.unitId);
            }
            this.recalcHpp();
        },

        getIngredientQty(ii, vi) {
            const ing = this.ingredients[ii];
            if (!ing) return 0;
            if (this.variants.length === 0) {
                return ing.baseQty || 0;
            }
            const variant = this.variants[vi];
            if (!variant) return 0;
            if (vi === this.baseVariantIdx) return ing.baseQty || 0;
            if (ing.manualOverride && ing.manualOverride[variant.id] !== undefined) return ing.manualOverride[variant.id];
            return Math.round((ing.baseQty || 0) * variant.multiplier * 100) / 100;
        },


        setIngredientQty(ii, vi, val) {
            const num = parseFloat(val) || 0;
            const ing = this.ingredients[ii];
            if (this.variants.length === 0) {
                ing.baseQty = num;
            } else {
                const variant = this.variants[vi];
                if (vi === this.baseVariantIdx) {
                    ing.baseQty = num;
                    ing.manualOverride = {};
                } else {
                    if (!ing.manualOverride) ing.manualOverride = {};
                    const expectedAuto = Math.round((ing.baseQty || 0) * variant.multiplier * 100) / 100;
                    if (Math.abs(num - expectedAuto) < 0.01) {
                        delete ing.manualOverride[variant.id];
                    } else {
                        ing.manualOverride[variant.id] = num;
                    }
                }
            }
            this.recalcHpp();
        },


        getConversionRate(fromUnitId, toUnitId) {
            if (fromUnitId == toUnitId) return 1;
            let conv = this.conversions.find(c => c.from == fromUnitId && c.to == toUnitId);
            if (conv) return conv.rate;
            let rev = this.conversions.find(c => c.from == toUnitId && c.to == fromUnitId);
            if (rev && rev.rate > 0) return 1 / rev.rate;
            return 1;
        },

        calcHpp(vi) {
            let total = 0;
            this.ingredients.forEach((ing, ii) => {
                const qty = this.getIngredientQty(ii, vi);
                if (!qty) return;

                if (ing.type === 'semi_finished') {
                    const sfp = this.sfpPrices[ing.sfp_id];
                    if (!sfp) return;
                    const rate = this.getConversionRate(parseInt(ing.unit_id), sfp.unit_id);
                    total += qty * rate * sfp.price_per_unit;
                } else {
                    const stock = this.stockPrices[ing.stock_id];
                    if (!stock) return;
                    const rate = this.getConversionRate(parseInt(ing.unit_id), stock.unit_id);
                    total += qty * rate * stock.price_per_unit;
                }
            });
            if (this.outputType === 'semi') {
                const labor = parseFloat(this.laborCost) || 0;
                const outQty = parseFloat(this.semiOutputQty) || 1;
                return Math.round((total + labor) / outQty);
            }
            const wage = parseFloat(this.wageFinished) || 0;
            return Math.round(total + wage);
        },

        calcBahanTotal() {
            let total = 0;
            this.ingredients.forEach((ing, ii) => {
                const qty = this.getIngredientQty(ii, 0);
                if (!qty) return;
                if (ing.type === 'semi_finished') {
                    const sfp = this.sfpPrices[ing.sfp_id];
                    if (!sfp) return;
                    const rate = this.getConversionRate(parseInt(ing.unit_id), sfp.unit_id);
                    total += qty * rate * sfp.price_per_unit;
                } else {
                    const stock = this.stockPrices[ing.stock_id];
                    if (!stock) return;
                    const rate = this.getConversionRate(parseInt(ing.unit_id), stock.unit_id);
                    total += qty * rate * stock.price_per_unit;
                }
            });
            return Math.round(total);
        },

        getIngredientUnitPrice(ii) {
            const ing = this.ingredients[ii];
            if (!ing || !ing.stock_id) return 0;
            const stock = this.stockPrices[ing.stock_id];
            if (!stock) return 0;
            const rate = this.getConversionRate(parseInt(ing.unit_id), stock.unit_id);
            return rate * (stock.price_per_unit || 0);
        },

        getIngredientCost(ii, vi) {
            const qty = this.getIngredientQty(ii, vi) || 0;
            const unitPrice = this.getIngredientUnitPrice(ii) || 0;
            return Math.round(qty * unitPrice);
        },

        // Semi-finished product helpers
        onSfpChange(ii) {
            const ing = this.ingredients[ii];
            const sfpId = ing.sfp_id;
            const info = this.sfpPrices[sfpId];
            if (info && info.unit_id && (!ing.unit_id || ing.unit_id === '')) {
                ing.unit_id = String(info.unit_id);
            }
            this.recalcHpp();
        },

        getSfpIngredientUnitPrice(ii) {
            const ing = this.ingredients[ii];
            if (!ing || !ing.sfp_id || !ing.unit_id) return 0;
            const sfp = this.sfpPrices[ing.sfp_id];
            if (!sfp || !sfp.price_per_unit) return 0;
            const rate = this.getConversionRate(parseInt(ing.unit_id), sfp.unit_id);
            return Math.round(rate * sfp.price_per_unit);
        },

        getSfpIngredientCost(ii, vi) {
            const unitPrice = this.getSfpIngredientUnitPrice(ii);
            const qty = this.getIngredientQty(ii, vi) || 0;
            return Math.round(unitPrice * qty);
        },

        getTypeTotal(type) {
            const vi = this.baseVariantIdx;
            let t = 0;
            this.ingredients.forEach((ing, ii) => {
                if ((ing.type || 'bahan') === type) {
                    if (type === 'semi_finished') {
                        t += this.getSfpIngredientCost(ii, vi);
                    } else {
                        t += this.getIngredientCost(ii, vi);
                    }
                }
            });
            return Math.round(t);
        },

        getSfpWagePerOutput(sfpId) {
            const s = this.sfpPrices[sfpId];
            if (!s) return 0;
            const labor = parseFloat(s.labor_cost || 0);
            const out = parseFloat(s.output_qty || 1) || 1;
            return labor / out;
        },

        getSfpWageContribution(ii, vi) {
            const ing = this.ingredients[ii];
            if (!ing || ing.type !== 'semi_finished' || !ing.sfp_id) return 0;
            const sfpId = ing.sfp_id;
            const sfp = this.sfpPrices[sfpId];
            if (!sfp) return 0;
            const rate = this.getConversionRate(parseInt(ing.unit_id), sfp.unit_id) || 1;
            const usedQtyInSfpUnits = (this.getIngredientQty(ii, vi) || 0) * rate;
            const wagePerOutput = this.getSfpWagePerOutput(sfpId);
            return Math.round(usedQtyInSfpUnits * wagePerOutput);
        },

        getTotalSfpWage() {
            const vi = this.baseVariantIdx;
            const seen = new Set();
            let total = 0;
            this.ingredients.forEach((ing, ii) => {
                if (ing.type === 'semi_finished' && ing.sfp_id) {
                    // sum contribution per ingredient (some sfp may appear multiple times)
                    total += this.getSfpWageContribution(ii, vi);
                }
            });
            return Math.round(total);
        },

        getTotalProductionWagePerBaseUnit() {
            if (this.outputType === 'semi') {
                // semi recipes store laborCost per batch; convert to per-unit
                const batch = parseFloat(this.semiOutputQty) || 1;
                const unitWage = (parseFloat(this.laborCost) || 0) / batch;
                return Math.round(unitWage + this.getTotalSfpWage());
            }
            return Math.round((parseFloat(this.wageFinished) || 0) + this.getTotalSfpWage());
        },

        // If no kemasan entries exist, insert common defaults (botol 250/500/1000, stiker, cup)
        addDefaultKemasanIfMissing() {
            const hasKemasan = this.ingredients.some(i => (i.type || 'bahan') === 'kemasan');
            if (hasKemasan) return;

            const defaults = [
                { kw: ['botol', '250'] },
                { kw: ['botol', '500'] },
                { kw: ['botol', '1000'] },
                { kw: ['stiker'] },
                { kw: ['cup'] },
            ];

            defaults.forEach(d => {
                let foundId = '';
                for (const [id, info] of Object.entries(this.stockPrices || {})) {
                    const name = (info.name || '').toLowerCase();
                    const ok = d.kw.every(k => name.includes(String(k).toLowerCase()));
                    if (ok) { foundId = id; break; }
                }

                if (foundId) {
                    const info = this.stockPrices[foundId] || {};
                    this.ingredients.push({ stock_id: String(foundId), unit_id: info.unit_id ? String(info.unit_id) : '', baseQty: 0, type: 'kemasan', manualOverride: {} });
                } else {
                    this.ingredients.push({ stock_id: '', unit_id: '', baseQty: 0, type: 'kemasan', manualOverride: {} });
                }
            });
            this.$nextTick(() => this.initChoices());
        },

        recalcHpp() {},

        formatNumber(n) {
            return Math.round(n || 0).toLocaleString('id-ID');
        },

        initChoices() {
            this.$nextTick(() => {
                document.querySelectorAll('.stock-select:not(.choices-initialized)').forEach(el => {
                    el.classList.add('choices-initialized');
                    try { new Choices(el, { searchEnabled: true, shouldSort: false, itemSelectText: '', allowHTML: true }); } catch(e) {}
                });
            });
        },

        submitForm() {
            this.attemptedSubmit = true;
            if (!this.isFormValid()) {
                this.$nextTick(() => {
                    const el = this.$root.querySelector('.stock-select:not([value])') || this.$root.querySelector('.rcp-input');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                return;
            }
            window.dispatchEvent(new Event('loading-start'));
            this.$el.closest('form').submit();
        },

        async saveNewStock() {
            this.newStockError = '';
            if (!this.newStock.name) { this.newStockError = 'Nama harus diisi.'; return; }
            if (!this.newStock.unit_id) { this.newStockError = 'Satuan harus dipilih.'; return; }
            if (!this.newStock.category_id) { this.newStockError = 'Kategori harus dipilih.'; return; }
            try {
                const resp = await fetch('{{ route("manager.inventory.stock.quick-create") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ name: this.newStock.name, unit_id: this.newStock.unit_id, stock_category_id: this.newStock.category_id })
                });
                const data = await resp.json();
                if (!resp.ok) { this.newStockError = data.message || 'Gagal menyimpan.'; return; }
                if (data.success && data.stock) {
                    const s = data.stock;
                    this.stockPrices[s.id] = { price_per_unit: 0, unit_id: parseInt(this.newStock.unit_id), name: s.name, unit_type: s.unit_type || '' };
                    document.querySelectorAll('.stock-select').forEach(sel => {
                        const opt = document.createElement('option');
                        opt.value = s.id; opt.textContent = s.name;
                        opt.setAttribute('data-unit-id', s.unit_id ?? this.newStock.unit_id);
                        sel.appendChild(opt);
                    });
                    this.showNewStockModal = false;
                    this.newStock = { name: '', unit_id: '', category_id: '' };
                }
            } catch(e) { this.newStockError = 'Error: ' + e.message; }
        },
    };
}
</script>
@endpush
