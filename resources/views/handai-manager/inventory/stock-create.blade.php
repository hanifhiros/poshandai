@extends('handai-manager.layouts.master')

@section('title', 'Tambah Pembelian Bahan')

@section('content')
<div class="min-h-screen bg-slate-50/60 py-6 px-4 sm:px-6 lg:px-8"
     x-data="purchaseForm()"
     x-init="init()">

    {{-- Header --}}
    <div class="max-w-5xl mx-auto mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.inventory.stock') }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Tambah Pembelian Bahan</h1>
                <p class="text-sm text-slate-500">Catat pembelian bahan baku baru ke gudang</p>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-5xl mx-auto mb-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-5xl mx-auto mb-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 002 0V5zm-1 8a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="max-w-5xl mx-auto mb-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ route('manager.inventory.stock.store') }}"
          enctype="multipart/form-data"
          @submit.prevent="submitForm($event)"
          class="max-w-5xl mx-auto space-y-5">
        @csrf

        {{-- ═══════════════════════════════════════════════════════
             SECTION 1: Informasi Pembelian
             ═══════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informasi Pembelian
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                    {{-- Supplier --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            Supplier <span class="text-red-400">*</span>
                        </label>
                        <input type="text"
                               name="supplier_name"
                               x-model="form.supplier_name"
                               placeholder="Nama supplier..."
                               required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                    </div>

                    {{-- Invoice Ref --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            No. Invoice / Referensi
                        </label>
                        <input type="text"
                               name="invoice_ref"
                               x-model="form.invoice_ref"
                               readonly
                               class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-800 cursor-default focus:outline-none transition">
                    </div>

                    {{-- Tanggal Pembelian --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            Tanggal Pembelian <span class="text-red-400">*</span>
                        </label>
                        <input type="date"
                               name="buy_date"
                               x-model="form.buy_date"
                               required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            Metode Pembayaran <span class="text-red-400">*</span>
                        </label>
                        <select name="payment_method"
                                x-model="form.payment_method"
                                required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                            <option value="">-- Pilih --</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="hutang">Hutang (Kredit)</option>
                        </select>
                    </div>

                    {{-- Tanggal Jatuh Tempo (conditional) --}}
                    <div x-show="form.payment_method === 'hutang'" x-transition class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                            Tanggal Jatuh Tempo <span class="text-red-400">*</span>
                        </label>
                        <div class="md:w-1/2">
                            <input type="date"
                                   name="due_date"
                                   x-model="form.due_date"
                                   :required="form.payment_method === 'hutang'"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             SECTION 2: Detail Item Pembelian
             ═══════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Detail Item Pembelian
                </h2>
                <button type="button"
                        @click="addItem()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Item
                </button>
            </div>

            <div class="p-6">
                {{-- Empty state --}}
                <div x-show="items.length === 0" class="text-center py-10 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-sm">Belum ada item. Klik <strong>"Tambah Item"</strong> untuk mulai.</p>
                </div>

                {{-- ===================== Desktop Table ===================== --}}
                <div x-show="items.length > 0" class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                <th class="pb-3 text-left font-medium w-8">#</th>
                                <th class="pb-3 text-left font-medium">Bahan</th>
                                <th class="pb-3 text-left font-medium w-24">Satuan</th>
                                <th class="pb-3 text-right font-medium w-28">Qty</th>
                                <th class="pb-3 text-right font-medium w-36">Harga Satuan</th>
                                <th class="pb-3 text-right font-medium w-36">Subtotal</th>
                                <th class="pb-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in items" :key="item.id">
                                <tr class="border-t border-slate-50 align-top group hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3 text-slate-400 text-xs" x-text="idx + 1"></td>

                                    {{-- Bahan (searchable) --}}
                                    <td class="py-3 pr-2">
                                        <div class="relative" x-data="{ open: false, search: '' }" @click.away="open = false">
                                            <input type="text"
                                                   x-model="search"
                                                   @focus="open = true; search = ''"
                                                   @input="open = true"
                                                   :placeholder="item.stock_name || 'Cari bahan...'"
                                                   :class="item.stock_id ? 'text-slate-800' : 'text-slate-400 italic'"
                                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                                            <input type="hidden" :name="'items['+idx+'][stock_id]'" :value="item.stock_id">
                                            <input type="hidden" :name="'items['+idx+'][unit_id]'" :value="item.unit_id">

                                            {{-- Dropdown --}}
                                            <div x-show="open" x-transition
                                                 class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                                <template x-for="s in filteredStocks(search)" :key="s.id">
                                                    <button type="button"
                                                            @click="selectStock(idx, s); open = false; search = s.name"
                                                            class="w-full text-left px-3 py-2 text-sm hover:bg-emerald-50 hover:text-emerald-700 transition flex items-center justify-between">
                                                        <span x-text="s.name"></span>
                                                        <span class="text-xs text-slate-400 ml-2" x-text="s.unit_symbol"></span>
                                                    </button>
                                                </template>
                                                <div x-show="filteredStocks(search).length === 0"
                                                     class="px-3 py-2 text-xs text-slate-400 italic">
                                                    Tidak ditemukan
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Satuan (readonly) --}}
                                    <td class="py-3 pr-2">
                                        <div class="px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-sm text-slate-500 text-center"
                                             x-text="item.unit_symbol || '-'"></div>
                                    </td>

                                    {{-- Qty --}}
                                    <td class="py-3 pr-2">
                                        <input type="number"
                                               :name="'items['+idx+'][unit_qty]'"
                                               x-model.number="item.unit_qty"
                                               step="0.001" min="0.001"
                                               placeholder="0"
                                               @input="recalcItemCost(idx); recalc()"
                                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                                    </td>

                                    {{-- Harga Satuan --}}
                                    <td class="py-3 pr-2">
                                        <input type="number"
                                               x-model.number="item.unit_price"
                                               step="1" min="0"
                                               placeholder="0"
                                               @input="recalcItemCost(idx); recalc()"
                                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                                    </td>

                                    {{-- Subtotal --}}
                                    <td class="py-3 pr-2">
                                        <input type="hidden" :name="'items['+idx+'][cost]'" :value="item.cost">
                                        <div class="px-3 py-2 bg-emerald-50/60 border border-emerald-100 rounded-lg text-sm text-right font-semibold text-emerald-700"
                                             x-text="formatRp(item.cost)"></div>
                                    </td>

                                    {{-- Hapus --}}
                                    <td class="py-3 text-center">
                                        <button type="button"
                                                @click="removeItem(idx)"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition opacity-0 group-hover:opacity-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- ===================== Mobile Stacked ===================== --}}
                <div x-show="items.length > 0" class="md:hidden space-y-4">
                    <template x-for="(item, idx) in items" :key="item.id">
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 relative">
                            <button type="button"
                                    @click="removeItem(idx)"
                                    class="absolute top-3 right-3 text-slate-400 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <div class="text-xs font-medium text-slate-400 mb-3">Item #<span x-text="idx+1"></span></div>

                            {{-- Bahan (mobile) --}}
                            <div class="mb-3" x-data="{ open: false, search: '' }" @click.away="open = false">
                                <label class="text-xs text-slate-500 mb-1 block">Bahan</label>
                                <input type="text"
                                       x-model="search"
                                       @focus="open = true; search = ''"
                                       :placeholder="item.stock_name || 'Cari bahan...'"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                                <input type="hidden" :name="'items['+idx+'][stock_id]'" :value="item.stock_id">
                                <input type="hidden" :name="'items['+idx+'][unit_id]'" :value="item.unit_id">
                                <div x-show="open" x-transition
                                     class="absolute z-30 left-4 right-4 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <template x-for="s in filteredStocks(search)" :key="s.id">
                                        <button type="button"
                                                @click="selectStock(idx, s); open = false; search = s.name"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-emerald-50 transition flex justify-between">
                                            <span x-text="s.name"></span>
                                            <span class="text-xs text-slate-400" x-text="s.unit_symbol"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Satuan</label>
                                    <div class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 text-center"
                                         x-text="item.unit_symbol || '-'"></div>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Qty</label>
                                    <input type="number"
                                           :name="'items['+idx+'][unit_qty]'"
                                           x-model.number="item.unit_qty"
                                           step="0.001" min="0.001"
                                           @input="recalcItemCost(idx); recalc()"
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Harga Satuan</label>
                                    <input type="number"
                                           x-model.number="item.unit_price"
                                           step="1" min="0"
                                           @input="recalcItemCost(idx); recalc()"
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Subtotal</label>
                                    <input type="hidden" :name="'items['+idx+'][cost]'" :value="item.cost">
                                    <div class="px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-lg text-sm text-right font-semibold text-emerald-700"
                                         x-text="formatRp(item.cost)"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             SECTION 3: Ringkasan & Total
             ═══════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Ringkasan Total
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left: notes & upload --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Catatan</label>
                            <textarea name="purchase_notes"
                                      x-model="form.purchase_notes"
                                      rows="3"
                                      placeholder="Catatan tambahan untuk pembelian ini..."
                                      class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Upload Nota / Invoice</label>
                            <label class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl text-sm text-slate-500 hover:border-emerald-300 hover:text-emerald-600 cursor-pointer transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="notaFileName || 'Pilih file (JPG, PNG, PDF — maks 4 MB)'"></span>
                                <input type="file"
                                       name="nota"
                                       accept="image/*,.pdf"
                                       class="hidden"
                                       @change="notaFileName = $event.target.files[0]?.name || ''">
                            </label>
                        </div>
                    </div>

                    {{-- Right: totals card --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-100 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Subtotal (<span x-text="items.length"></span> item)</span>
                                <span class="font-medium text-slate-700" x-text="formatRp(subtotal)"></span>
                            </div>

                            <div class="flex justify-between items-center text-sm gap-4">
                                <span class="text-slate-500 whitespace-nowrap">Diskon</span>
                                <div class="w-36">
                                    <input type="number"
                                           name="discount"
                                           x-model.number="form.discount"
                                           @input="recalc()"
                                           step="1" min="0"
                                           placeholder="0"
                                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                                </div>
                            </div>

                            <div class="flex justify-between items-center text-sm gap-4">
                                <span class="text-slate-500 whitespace-nowrap">Pajak</span>
                                <div class="w-36">
                                    <input type="number"
                                           name="tax"
                                           x-model.number="form.tax"
                                           @input="recalc()"
                                           step="1" min="0"
                                           placeholder="0"
                                           class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-sm text-right focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-3 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-semibold text-slate-700">Grand Total</span>
                                <span class="text-xl font-bold text-emerald-600" x-text="formatRp(grandTotal)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             SECTION 4: Tombol Simpan
             ═══════════════════════════════════════════════════════ --}}
        <div class="flex items-center justify-between bg-white rounded-2xl border border-slate-200/80 shadow-sm px-6 py-4">
            <a href="{{ route('manager.inventory.stock') }}"
               class="text-sm text-slate-500 hover:text-slate-700 transition">
                &larr; Batal
            </a>
            <button type="submit"
                    :disabled="!isValid || submitting"
                    :class="isValid && !submitting
                        ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-200'
                        : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition">
                {{-- Spinner --}}
                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="submitting ? 'Menyimpan...' : 'Simpan Pembelian'"></span>
            </button>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════
         Confirmation Modal
         ═══════════════════════════════════════════════════════ --}}
    <div x-show="showConfirm" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
         style="display:none;">
        <div @click.away="showConfirm = false" x-transition
             class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="text-center mb-5">
                <div class="w-12 h-12 mx-auto mb-3 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Pembelian</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Simpan <strong x-text="items.length"></strong> item dari
                    <strong x-text="form.supplier_name || '-'"></strong>
                    senilai <strong x-text="formatRp(grandTotal)"></strong>?
                </p>
            </div>
            <div class="flex gap-3">
                <button type="button"
                        @click="showConfirm = false"
                        class="flex-1 py-2.5 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="button"
                        @click="confirmSubmit()"
                        class="flex-1 py-2.5 rounded-xl text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm">
                    Ya, Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function purchaseForm() {
    return {
        // Stock master data from server
        stocksData: @json($stocksJson),

        form: {
            supplier_name:  '{{ old("supplier_name", "") }}',
            invoice_ref:    '{{ old("invoice_ref", $autoInvoiceRef) }}',
            buy_date:       '{{ old("buy_date", date("Y-m-d")) }}',
            payment_method: '{{ old("payment_method", "") }}',
            due_date:       '{{ old("due_date", "") }}',
            discount:       {{ old('discount', 0) }},
            tax:            {{ old('tax', 0) }},
            purchase_notes: '',
        },

        items: [],
        nextId: 1,
        subtotal: 0,
        grandTotal: 0,
        notaFileName: '',
        submitting: false,
        showConfirm: false,
        pendingForm: null,

        init() {
            this.addItem();
        },

        addItem() {
            this.items.push({
                id:          this.nextId++,
                stock_id:    '',
                stock_name:  '',
                unit_id:     '',
                unit_symbol: '',
                unit_qty:    '',
                unit_price:  '',
                cost:        0,
            });
            this.$nextTick(() => {
                const inputs = document.querySelectorAll('input[placeholder="Cari bahan..."]');
                if (inputs.length) inputs[inputs.length - 1].focus();
            });
        },

        removeItem(idx) {
            this.items.splice(idx, 1);
            this.recalc();
        },

        selectStock(idx, stock) {
            this.items[idx].stock_id    = stock.id;
            this.items[idx].stock_name  = stock.name;
            this.items[idx].unit_id     = stock.unit_id;
            this.items[idx].unit_symbol = stock.unit_symbol;
        },

        filteredStocks(search) {
            if (!search) return this.stocksData;
            const q = search.toLowerCase();
            return this.stocksData.filter(s => s.name.toLowerCase().includes(q));
        },

        recalcItemCost(idx) {
            const item = this.items[idx];
            item.cost = Math.round((parseFloat(item.unit_qty) || 0) * (parseFloat(item.unit_price) || 0));
        },

        recalc() {
            this.items.forEach((item, idx) => this.recalcItemCost(idx));
            this.subtotal   = this.items.reduce((sum, item) => sum + (parseFloat(item.cost) || 0), 0);
            this.grandTotal = this.subtotal - (parseFloat(this.form.discount) || 0) + (parseFloat(this.form.tax) || 0);
            if (this.grandTotal < 0) this.grandTotal = 0;
        },

        formatRp(val) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
        },

        get isValid() {
            if (!this.form.supplier_name.trim()) return false;
            if (!this.form.buy_date) return false;
            if (!this.form.payment_method) return false;
            if (this.form.payment_method === 'hutang' && !this.form.due_date) return false;
            if (this.items.length === 0) return false;
            return this.items.every(i => i.stock_id && parseFloat(i.unit_qty) > 0 && parseFloat(i.cost) >= 0);
        },

        submitForm(e) {
            if (!this.isValid) return;
            this.pendingForm = e.target;
            this.showConfirm = true;
        },

        confirmSubmit() {
            this.showConfirm = false;
            this.submitting  = true;
            if (this.pendingForm) {
                this.pendingForm.submit();
            }
        }
    }
}
</script>
@endsection
