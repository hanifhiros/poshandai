@extends('handai-manager.layouts.master')

@section('title', 'Tambah Resep')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    .rcp-input, .rcp-select {
        height: 36px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc;
        padding: 0 12px; font-size: 13px; color: #334155; transition: border-color .15s, box-shadow .15s; width: 100%;
    }
    .rcp-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px; }
    .rcp-input:focus, .rcp-select:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); }
    .variant-tab { cursor: pointer; transition: all .15s; }
    .variant-tab.active { background: #059669; color: #fff; border-color: #059669; }
    .variant-tab:not(.active):hover { background: #f0fdf4; border-color: #86efac; }
    .auto-badge { font-size: 9px; padding: 1px 5px; border-radius: 4px; background: #dbeafe; color: #2563eb; font-weight: 600; line-height: 1.4; }
    .hpp-card { background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1100px] mx-auto"
     x-data="recipeCreate()" x-cloak>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('manager.inventory.recipes.index') }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Tambah Resep</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Buat resep untuk semua varian produk sekaligus dengan auto-scaling</p>
        </div>
    </div>

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <ul class="text-[12px] text-red-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li class="flex items-start gap-1.5">
                        <svg class="w-3 h-3 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manager.inventory.recipes.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="output_type" :value="outputType">
        <input type="hidden" name="product_id" x-bind:disabled="outputType !== 'finished'" :value="selectedProductId">
        <input type="hidden" name="semi_finished_output_id" x-bind:disabled="outputType !== 'semi'" :value="selectedProductId">

        {{-- Step 1: Output Selection --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">1</div>
                <h2 class="text-[13px] font-semibold text-gray-700">Pilih Output</h2>
            </div>

            <div class="flex items-center gap-4 mb-3">
                <label class="inline-flex items-center gap-1">
                    <input type="radio" name="outputType" value="finished" x-model="outputType" class="form-radio" checked>
                    <span class="text-sm">Produk Jadi</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="radio" name="outputType" value="semi" x-model="outputType" class="form-radio">
                    <span class="text-sm">Produk Setengah Jadi</span>
                </label>
            </div>

            <div x-show="outputType === 'finished'">
                <select id="product-select" class="rcp-select" @change="onOutputChange($event.target.value)" x-ref="productSelect">
                    <option value="">-- Pilih Produk --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="outputType === 'semi'">
                <select id="semi-select" class="rcp-select" @change="onOutputChange($event.target.value)">
                    <option value="">-- Pilih Produk Setengah Jadi --</option>
                    @foreach ($semiFinishedProducts as $sfp)
                        <option value="{{ $sfp->id }}">{{ $sfp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Step 2: Variants Overview (shows after product selection) --}}
        <template x-if="selectedProductId && variants.length > 0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">2</div>
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

            {{-- Custom multipliers --}}
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-[11px] text-gray-500 mb-2">
                    <svg class="w-3 h-3 inline -mt-0.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Rasio kelipatan otomatis diambil dari angka di nama varian. Edit manual jika perlu:
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
        </template>

        {{-- Step 3: Ingredients (shows after product selection) --}}
        <template x-if="selectedProductId">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">2</div>
                    <h2 class="text-[13px] font-semibold text-gray-700">Daftar Bahan</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-gray-400">Isi jumlah basis, varian lain otomatis terhitung</span>
                    <button type="button" @click="addIngredient('bahan')"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-transparent hover:border-emerald-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Bahan</span>
                    </button>
                    <button type="button" @click="showNewStockModal = true"
                        class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-transparent hover:border-blue-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Bahan Baru</span>
                    </button>
                </div>
            </div>

            {{-- Ingredients Table --}}
            <div class="overflow-x-auto -mx-5 px-5">
                <table class="w-full" x-show="ingredients.filter(i => (i.type || 'bahan') === 'bahan').length > 0">
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
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(ing, ii) in ingredients" :key="ii">
                            <tr class="border-t border-gray-50" x-show="(ing.type || 'bahan') === 'bahan'">
                                {{-- Stock select --}}
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
                                {{-- Unit select --}}
                                <td class="py-2 pr-2">
                                    <select :id="'unit-'+ii" class="rcp-select unit-select" x-model="ing.unit_id" @change="recalcHpp()">
                                        <option value="">Satuan</option>
                                        @foreach ($units->sortBy('symbol') as $unit)
                                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !ing.unit_id">Pilih satuan.</p>
                                </td>
                                {{-- Quantity cell(s) --}}
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
                                        <div class="text-[11px] text-gray-400 mt-1 font-mono" x-show="ing.stock_id && ing.unit_id">
                                            <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + (unitsById[ing.unit_id] ? (' /' + unitsById[ing.unit_id].symbol) : '')" class="text-emerald-500"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, 0))" class="text-emerald-700"></div>
                                        </div>
                                    </td>
                                </template>
                                <template x-for="(v, vi) in variants" :key="'qty-'+v.id+'-'+ii">
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
                                            <div class="text-[11px] text-gray-400 mt-1 font-mono" x-show="ing.stock_id && ing.unit_id">
                                                <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + (unitsById[ing.unit_id] ? (' /' + unitsById[ing.unit_id].symbol) : '')" class="text-emerald-500"></div>
                                                <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, vi))" class="text-emerald-700"></div>
                                            </div>
                                    </td>
                                </template>
                                {{-- Remove --}}
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

                <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'bahan').length === 0" class="py-10 text-center">
                    <p class="text-[12px] text-gray-400 mb-2">Belum ada bahan. Klik tombol "Tambah Bahan" di atas.</p>
                </div>

                
                
                
            </div>
        </div>
        </template>

        {{-- Produk Setengah Jadi Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4" x-show="selectedProductId">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-[11px] font-bold">3</div>
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
                                            <p class="text-red-600 text-xs mt-1" x-show="attemptedSubmit && !(getIngredientQty(ii,0) > 0)">Isi jumlah produk setengah jadi.</p>
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-1" x-show="ing.sfp_id && ing.unit_id">
                                            <div x-text="'Harga: Rp ' + formatNumber(getSfpIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')" class="text-purple-500"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getSfpIngredientCost(ii, 0))" class="font-medium text-purple-700"></div>
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
                                                   class="rcp-input text-center text-[12px] font-mono" :class="vi !== baseVariantIdx && !ing.manualOverride?.[v.id] ? 'bg-purple-50/50 border-purple-100' : ''"
                                                   placeholder="0">
                                            <span x-show="vi !== baseVariantIdx && !ing.manualOverride?.[v.id]" class="absolute -top-1 -right-1 w-3 h-3 bg-purple-400 rounded-full flex items-center justify-center" title="Auto-calculated">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-1" x-show="ing.sfp_id && ing.unit_id">
                                            <div x-text="'Harga: Rp ' + formatNumber(getSfpIngredientUnitPrice(ii)) + ' /' + (unitsById[ing.unit_id]?.symbol || '')" class="text-purple-500"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getSfpIngredientCost(ii, vi))" class="font-medium text-purple-700"></div>
                                        </div>
                                    </td>
                                </template>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="removeIngredient(ii)" class="w-7 h-7 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Packaging (Kemasan) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4" x-show="selectedProductId">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-[11px] font-bold">5</div>
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
                                </td>
                                <td class="py-2 pr-2">
                                    <select :id="'unit-km-'+ii" class="rcp-select unit-select" x-model="ing.unit_id" @change="recalcHpp()">
                                        <option value="">Satuan</option>
                                        @foreach ($units as $unit)
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
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-1 font-mono" x-show="ing.stock_id && ing.unit_id">
                                            <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + (unitsById[ing.unit_id] ? (' /' + unitsById[ing.unit_id].symbol) : '')" class="text-amber-500"></div>
                                            <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, 0))" class="text-amber-700"></div>
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
                                            <span x-show="vi !== baseVariantIdx && !ing.manualOverride?.[v.id]"
                                                  class="absolute -top-1 -right-1 w-3 h-3 bg-blue-400 rounded-full flex items-center justify-center"
                                                  title="Auto-calculated">
                                                <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            </span>
                                        </div>
                                            <div class="text-[11px] text-gray-400 mt-1 font-mono" x-show="ing.stock_id && ing.unit_id">
                                                <div x-text="'Harga: Rp ' + formatNumber(getIngredientUnitPrice(ii)) + (unitsById[ing.unit_id] ? (' /' + unitsById[ing.unit_id].symbol) : '')" class="text-amber-500"></div>
                                                <div x-text="'Total: Rp ' + formatNumber(getIngredientCost(ii, vi))" class="text-amber-700"></div>
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

                <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'kemasan').length === 0" class="py-6 text-center text-sm text-gray-400">Belum ada kemasan.</div>
            </div>
        </div>

        {{-- Standar Produksi Batch (semi output only) --}}
        <div x-show="outputType === 'semi' && selectedProductId" class="bg-white rounded-xl shadow-sm border border-orange-100 p-5 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[11px] font-bold">2</div>
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
                    <span class="text-xs text-gray-600 font-medium whitespace-nowrap px-2 py-2 bg-orange-50 border border-orange-100 rounded-lg"
                        x-text="unitsById[sfpPrices[selectedProductId]?.unit_id]?.symbol || 'unit'"></span>
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Komposisi bahan di atas menghasilkan berapa [satuan]? Nilai ini digunakan untuk normalisasi HPP per unit.</p>
            </div>
        </div>

        {{-- Upah Produksi Section --}}
        <div x-show="selectedProductId && ingredients.length > 0" class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[11px] font-bold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
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
                    <p class="text-[10px] text-gray-400 mt-1">Upah untuk mengolah 1 batch. HPP per unit = (bahan + upah) ÷ output per batch (<span x-text="(parseFloat(semiOutputQty)||1)"></span> <span x-text="unitsById[sfpPrices[selectedProductId]?.unit_id]?.symbol || 'unit'"></span>)</p>
                </div>
            </div>
        </div>

        {{-- Step 4: HPP Summary --}}
        <template x-if="selectedProductId && ingredients.length > 0">
        <div class="hpp-card rounded-xl shadow-sm border border-emerald-100 p-6 mb-4">
            <div class="flex items-center gap-2 mb-4">
                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[11px] font-bold">3</div>
                <h2 class="text-[13px] font-semibold text-emerald-800">Ringkasan HPP (Harga Pokok Produksi)</h2>
            </div>

            <div class="grid gap-3" :style="'grid-template-columns: repeat(' + (variants.length || 1) + ', minmax(0, 1fr))'">
                <template x-if="variants.length === 0">
                    <div class="bg-white rounded-lg border border-emerald-100 p-4">
                        <template x-if="outputType === 'semi'">
                            <div>
                                <p class="text-[11px] text-gray-500 mb-0.5">HPP per <span x-text="unitsById[sfpPrices[selectedProductId]?.unit_id]?.symbol || 'unit'"></span></p>
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
                                        <span class="font-mono text-gray-600" x-text="(parseFloat(semiOutputQty)||1) + ' ' + (unitsById[sfpPrices[selectedProductId]?.unit_id]?.symbol || 'unit')"></span>
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

        {{-- Daftar Bahan & Kemasan Totals (moved below HPP) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4" x-show="ingredients.length > 0">
            <div class="bg-white rounded-lg border border-gray-100 p-3">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[12px] font-semibold text-gray-700">Daftar Bahan Baku</h3>
                    <div class="text-[12px] font-mono text-gray-700">Rp <span x-text="formatNumber(getTypeTotal('bahan'))"></span></div>
                </div>
                <div class="text-[12px] text-gray-500 space-y-1">
                    <template x-for="(ing, ii) in ingredients.filter(i => (i.type || 'bahan') === 'bahan')" :key="'bb-'+ii">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[240px]" x-text="(stockPrices[ing.stock_id] && stockPrices[ing.stock_id].name) + ' · ' + (ing.baseQty || 0) + ' ' + (unitsById[ing.unit_id]?.symbol || '')"></div>
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
                            <div class="truncate max-w-[240px]" x-text="(sfpPrices[ing.sfp_id] && sfpPrices[ing.sfp_id].name) + ' · ' + (ing.baseQty || 0) + ' ' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                            <div class="font-mono text-purple-700">Rp <span x-text="formatNumber(getSfpIngredientCost(ii, baseVariantIdx))"></span></div>
                        </div>
                    </template>
                    <div x-show="ingredients.filter(i => i.type === 'semi_finished').length === 0" class="text-[12px] text-gray-400">Belum ada.</div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-100 p-3" x-show="outputType === 'finished'">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[12px] font-semibold text-gray-700">Daftar Kemasan</h3>
                    <div class="text-[12px] font-mono text-gray-700">Rp <span x-text="formatNumber(getTypeTotal('kemasan'))"></span></div>
                </div>
                <div class="text-[12px] text-gray-500 space-y-1">
                    <template x-for="(ing, ii) in ingredients.filter(i => (i.type || 'bahan') === 'kemasan')" :key="'km-'+ii">
                        <div class="flex items-center justify-between">
                            <div class="truncate max-w-[240px]" x-text="(stockPrices[ing.stock_id] && stockPrices[ing.stock_id].name) + ' · ' + (ing.baseQty || 0) + ' ' + (unitsById[ing.unit_id]?.symbol || '')"></div>
                            <div class="font-mono text-gray-700">Rp <span x-text="formatNumber(getIngredientCost(ii, baseVariantIdx))"></span></div>
                        </div>
                    </template>
                    <div x-show="ingredients.filter(i => (i.type || 'bahan') === 'kemasan').length === 0" class="text-[12px] text-gray-400">Belum ada kemasan.</div>
                </div>
            </div>
        </div>

        {{-- Hidden form fields for submission --}}
        <template x-if="variants.length > 0" x-for="(v, vi) in variants" :key="'hidden-'+v.id">
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
        <template x-if="selectedProductId">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('manager.inventory.recipes.index') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:bg-gray-100 rounded-lg transition">← Batal</a>
                <button type="submit"
                    class="h-9 px-5 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Simpan Resep
                </button>
            </div>
        </div>
        </template>
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
                <button type="button" @click="showNewStockModal = false" class="h-8 px-4 text-[12px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 hover:bg-gray-100 rounded-lg transition">← Batal</button>
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
function recipeCreate() {
    return {
        outputType: @json(request('output_type','finished')),
        selectedProductId: null,
        variants: [],
        baseVariantIdx: 0,
        currentIngTab: 'bahan',
        ingredients: [],
        attemptedSubmit: false,
        showNewStockModal: false,
        newStock: { name: '', unit_id: '', category_id: '' },
        newStockError: '',
        semiOutputQty: 1,
        laborCost: 0,
        wageFinished: 0,

        // Data from server
        products: @json($products),
        semiFinishedProducts: @json($semiFinishedProducts),
        variantsByProduct: @json($variantsByProduct),
        stockPrices: @json($stockPrices),
        sfpPrices: @json($sfpPrices),
        conversions: @json($conversions),
        unitsById: @json($units->mapWithKeys(function($u){ return [$u->id => ['symbol' => $u->symbol, 'name' => $u->name, 'unit_type' => $u->unit_type]];})),

        init() {
            this.$watch('ingredients', () => this.recalcHpp(), { deep: true });
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

        isFormValid() {
            if (!this.selectedProductId) return false;
            if (!this.ingredients || this.ingredients.length === 0) return false;
            for (let i = 0; i < this.ingredients.length; i++) {
                const ing = this.ingredients[i];
                const hasSource = ing.type === 'semi_finished' ? !!ing.sfp_id : !!ing.stock_id;
                if (!hasSource || !ing.unit_id) return false;
                if (this.outputType === 'finished') {
                    // require at least one quantity, accounting for no variants
                    let anyQty = false;
                    if (this.variants.length === 0) {
                        anyQty = parseFloat(this.getIngredientQty(i, 0)) > 0;
                    } else {
                        for (let vi = 0; vi < this.variants.length; vi++) {
                            const q = parseFloat(this.getIngredientQty(i, vi)) || 0;
                            if (q > 0) { anyQty = true; break; }
                        }
                    }
                    if (!anyQty) return false;
                }
            }
            return true;
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

        onOutputChange(outputId) {
            this.selectedProductId = outputId || null;
            this.ingredients = [];
            this.baseVariantIdx = 0;

            if (this.outputType === 'finished') {
                const pid = outputId;
                if (!pid || !this.variantsByProduct[pid]) {
                    this.variants = [];
                    return;
                }

                // Load variants and detect multipliers
                this.variants = this.variantsByProduct[pid].map(v => ({
                    ...v,
                    ...(() => { const p = this.parseMultiplierFromLabel(v.label); return { multiplier: p.multiplier || 1, customMultiplier: !!p.custom }; })(),
                }));

                // Choose base variant
                const auto = this.variants.filter(v => !v.customMultiplier);
                let baseIdx = 0;
                if (auto.length > 0) {
                    const minMult = Math.min(...auto.map(v => v.multiplier));
                    baseIdx = this.variants.findIndex(v => !v.customMultiplier && v.multiplier === minMult);
                    if (baseIdx === -1) baseIdx = 0;
                }
                this.baseVariantIdx = baseIdx;

                let baseMult = this.variants[this.baseVariantIdx].multiplier || 1;
                this.variants = this.variants.map(v => ({
                    ...v,
                    multiplier: (v.multiplier || 1) / baseMult
                }));
            } else {
                // semi output: no variants
                this.variants = [];
            }

            // Initialize Choices.js on selects after DOM update and prefill kemasan
            if (this.selectedProductId) {
                this.$nextTick(() => { this.initChoices(); if (this.outputType === 'finished') this.addDefaultKemasan(); this.populateUnitDefaults(); });
            }
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
            let oldBase = this.baseVariantIdx;
            let oldBaseMult = this.variants[oldBase].multiplier; // should be 1
            let newBaseMult = this.variants[idx].multiplier;

            // Recalculate all multipliers relative to new base
            this.variants.forEach((v, i) => {
                v.multiplier = v.multiplier / newBaseMult;
            });

            // Recalculate ingredient base quantities
            this.ingredients.forEach(ing => {
                let oldBaseQty = ing.baseQty;
                ing.baseQty = oldBaseQty * newBaseMult;
                // Clear manual overrides
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
            this.ingredients.push({
                stock_id: '',
                sfp_id: '',
                unit_id: '',
                baseQty: 0,
                type: type,
                manualOverride: {}, // { variantId: qty } for manually overridden
            });
            this.$nextTick(() => this.initChoices());
        },

        // Prefill common kemasan items (if available in stock list)
        addDefaultKemasan() {
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

        removeIngredient(idx) {
            this.ingredients.splice(idx, 1);
        },

        onStockChange(ii) {
            const ing = this.ingredients[ii];
            const stockId = ing.stock_id;
            // try to auto-fill unit_id from option dataset
            const sel = document.getElementById('stock-'+ii) || document.getElementById('stock-km-'+ii) || document.querySelector("select.stock-select option[value='" + stockId + "']");
            if (sel && sel.dataset && sel.dataset.unitId) {
                if (!ing.unit_id || ing.unit_id === '') ing.unit_id = String(sel.dataset.unitId);
            }
            const info = this.stockPrices[stockId];
            if (info && info.unit_type) {
                // Auto-filter compatible units (handled visually)
            }
            this.recalcHpp();
        },

        getIngredientQty(ii, vi) {
            const ing = this.ingredients[ii];
            if (!ing) return 0;
            // if there are no variants (semi output), only baseQty matters
            if (this.variants.length === 0) {
                return ing.baseQty || 0;
            }
            const variant = this.variants[vi];
            if (!variant) return 0;

            if (vi === this.baseVariantIdx) {
                return ing.baseQty || 0;
            }

            if (ing.manualOverride && ing.manualOverride[variant.id] !== undefined) {
                return ing.manualOverride[variant.id];
            }

            return Math.round((ing.baseQty || 0) * variant.multiplier * 100) / 100;
        },

        setIngredientQty(ii, vi, val) {
            const num = parseFloat(val) || 0;
            const ing = this.ingredients[ii];
            if (this.variants.length === 0) {
                // semi output: treat all as baseQty
                ing.baseQty = num;
            } else {
                const variant = this.variants[vi];
                if (vi === this.baseVariantIdx) {
                    ing.baseQty = num;
                    // Clear manual overrides when base changes
                    ing.manualOverride = {};
                } else {
                    // Manual override
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

        // Price helpers
        getIngredientUnitPrice(ii) {
            const ing = this.ingredients[ii];
            if (!ing || !ing.stock_id || !ing.unit_id) return 0;
            const stock = this.stockPrices[ing.stock_id];
            if (!stock || !stock.price_per_unit) return 0;
            const rate = this.getConversionRate(parseInt(ing.unit_id), stock.unit_id);
            return Math.round(rate * stock.price_per_unit);
        },

        getIngredientCost(ii, vi) {
            const unitPrice = this.getIngredientUnitPrice(ii);
            const qty = this.getIngredientQty(ii, vi) || 0;
            return Math.round(unitPrice * qty);
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
            // finished product: add wage per unit
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

        recalcHpp() { /* triggered by watchers */ },

        getTypeTotal(type) {
            let total = 0;
            this.ingredients.forEach((ing, ii) => {
                if ((ing.type || 'bahan') !== type) return;
                if (type === 'semi_finished') {
                    total += this.getSfpIngredientCost(ii, this.baseVariantIdx);
                } else {
                    total += this.getIngredientCost(ii, this.baseVariantIdx);
                }
            });
            return total;
        },

        formatNumber(n) {
            return Math.round(n || 0).toLocaleString('id-ID');
        },

        initChoices() {
            // Reinitialize Choices.js on new select elements
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
                // focus first invalid element
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
                    // Add to all stock selects (preserve data-unit-id)
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
