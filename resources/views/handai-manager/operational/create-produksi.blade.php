@extends('handai-manager.layouts.master')

@section('title', 'Tambah Produksi')

@section('content')
<style>
    .cpd-input { width: 100%; height: 40px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .cpd-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .cpd-label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .cpd-select { width: 100%; height: 40px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; appearance: none; cursor: pointer; }
    .cpd-select:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }

    /* Choices.js multi-select styling */
    .choices__inner {
        min-height: 40px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        position: relative;
    }
    .choices__inner::after {
        content: '';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #94a3b8;
        pointer-events: none;
    }
    .choices.is-open .choices__inner::after {
        transform: translateY(-50%) rotate(180deg);
    }
    .choices__inner.is-focused { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .choices__list--multiple { padding: 4px 8px; }
    .choices__list--multiple .choices__item {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.4);
        color: #064e3b;
        border-radius: 999px;
        padding: 4px 10px 4px 10px;
        margin: 4px 4px 4px 0;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.15s, border-color 0.15s;
    }
    .choices__list--multiple .choices__item:hover {
        background: rgba(16, 185, 129, 0.25);
        border-color: rgba(16, 185, 129, 0.55);
    }
    .choices__list--multiple .choices__button {
        color: #064e3b;
        width: 18px;
        height: 18px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: 0.7;
        transition: opacity 0.15s, background 0.15s;
    }
    .choices__list--multiple .choices__button:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.08);
    }
    .choices__input { min-height: 36px; margin: 0; }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[720px] mx-auto">

    {{-- Back link --}}
    <a href="{{ route('manager.operational.produksi') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-400 hover:text-gray-600 mb-4 group">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Riwayat Produksi
    </a>

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Tambah Produksi</h1>
        <p class="text-[13px] text-gray-400 mt-0.5">Catat aktivitas produksi baru</p>
    </div>

    {{-- Errors --}}
    @if ($errors->any())
    <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-xl text-[13px] text-red-700">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form --}}
    @php
        $initialProductionLines = old('production_lines', [[
            'type' => 'finished',
            'product_variants_id' => old('product_variants_id', ''),
            'semi_finished_product_id' => old('semi_finished_product_id', ''),
            'quantity_produced' => old('quantity_produced', 0),
            'use_bom' => old('use_bom', 'yes'),
        ]]);
    @endphp

    <form method="POST" action="{{ route('manager.operational.produksi.store') }}" class="space-y-5" x-data="produksiForm()">
        @csrf

        {{-- Card: Info Utama --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
            <h2 class="text-[13px] font-bold text-gray-700 flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-emerald-100 inline-flex items-center justify-center">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                Info Utama
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="cpd-label">Tanggal Produksi</label>
                    <input type="date" name="production_date" value="{{ old('production_date', now()->toDateString()) }}" class="cpd-input" required>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="cpd-label">Pilih Nama</label>
                        <div class="flex items-center gap-2">
                            <select x-model="selectedPic" class="cpd-select flex-1">
                                <option value="">— Pilih PIC —</option>
                                <template x-for="emp in employees" :key="emp.id">
                                    <option :value="emp.id" x-text="emp.name"></option>
                                </template>
                            </select>
                            <button type="button" @click="addPic" class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-transparent hover:border-emerald-200 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Tambah
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Pilih satu per satu, lalu tekan Tambah untuk menambahkan ke daftar.</p>
                    </div>

                    <div>
                        <label class="cpd-label">PIC Terpilih</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(id, idx) in picIds" :key="id">
                                <div class="flex items-center gap-1 px-3 py-2 rounded-full bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
                                    <span x-text="employees.find(e => e.id == id)?.name || '—'" class="truncate"></span>
                                    <button type="button" @click="removePic(idx)" class="w-6 h-6 rounded-full flex items-center justify-center text-red-600 hover:bg-red-100" aria-label="Hapus PIC">
                                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 7L14 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M8 11v7a2 2 0 002 2h4a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 15v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M14 15v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    </button>
                                    <input type="hidden" name="pic_ids[]" :value="id" />
                                </div>
                            </template>
                            <div x-show="picIds.length === 0" class="text-[12px] text-gray-400">Belum ada PIC. Tambahkan di samping.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Rincian Produk</h3>

                    <template x-for="(line, index) in lines" :key="index">
                        <div class="border border-gray-100 rounded-xl p-4 mb-3">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium text-gray-700">Item <span x-text="index + 1"></span></p>
                                <button type="button" class="text-xs text-red-600 hover:text-red-800" @click="removeLine(index)" x-show="lines.length > 1">Hapus</button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                <div>
                                    <label class="cpd-label">Jenis</label>
                                    <select :name="`production_lines[${index}][type]`" x-model="line.type" @change="onLineTypeChange(line)" class="cpd-select">
                                        <option value="finished">Produk Jadi</option>
                                        <option value="semi">Setengah Jadi</option>
                                    </select>
                                </div>

                                <div x-show="line.type === 'finished'" x-cloak>
                                    <label class="cpd-label">Produk + Varian</label>
                                    <select :name="`production_lines[${index}][product_variants_id]`" x-model="line.product_variants_id" class="cpd-select" :disabled="line.type !== 'finished'">
                                        <option value="">— Pilih Produk Varian —</option>
                                        @foreach ($productVariants as $variant)
                                        <option value="{{ $variant->id }}">{{ $variant->product->name }} — {{ $variant->options->pluck('name')->join(', ') }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div x-show="line.type === 'semi'" x-cloak>
                                    <label class="cpd-label">Produk Setengah Jadi</label>
                                    <select :name="`production_lines[${index}][semi_finished_product_id]`" x-model="line.semi_finished_product_id" class="cpd-select" :disabled="line.type !== 'semi'">
                                        <option value="">— Pilih Setengah Jadi —</option>
                                        @foreach(\App\Models\SemiFinishedProduct::where('store_id', session('selected_store'))->get() as $sfp)
                                        <option value="{{ $sfp->id }}">{{ $sfp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="cpd-label">Jumlah Diproduksi</label>
                                    <input :name="`production_lines[${index}][quantity_produced]`" type="number" x-model.number="line.quantity_produced" min="1" class="cpd-input" placeholder="0" required>
                                </div>

                                <div x-show="line.type === 'finished'" x-cloak>
                                    <label class="cpd-label">Pengurangan Stok</label>
                                    <select :name="`production_lines[${index}][use_bom]`" x-model="line.use_bom" class="cpd-select" required>
                                        <option value="yes">Otomatis (Resep/BOM)</option>
                                        <option value="no">Manual</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addLine" class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-transparent hover:border-emerald-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Produk
                    </button>
                </div>
            </div>
        </div>

        {{-- Card: Upah Produksi --}}
        <div x-show="totalWage > 0" x-cloak class="bg-white rounded-xl border border-indigo-100 shadow-sm p-5">
            <h2 class="text-[13px] font-bold text-gray-700 flex items-center gap-2 mb-3">
                <span class="w-5 h-5 rounded-md bg-indigo-100 inline-flex items-center justify-center">
                    <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </span>
                Upah Produksi
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-indigo-50/50 rounded-lg p-3">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Total Produksi</p>
                    <p class="text-sm font-bold text-gray-700 font-mono" x-text="totalQuantity.toLocaleString('id-ID')"></p>
                </div>
                <div class="bg-indigo-50/50 rounded-lg p-3">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Total Upah</p>
                    <p class="text-sm font-bold text-indigo-700 font-mono" x-text="'Rp ' + totalWage.toLocaleString('id-ID')"></p>
                </div>
                <div class="bg-indigo-50/50 rounded-lg p-3">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Upah per PIC</p>
                    <p class="text-sm font-bold text-indigo-700 font-mono" x-text="'Rp ' + wagePerPic.toLocaleString('id-ID')"></p>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-2">* Upah akan otomatis dicatat ke jurnal keuangan saat produksi disimpan.</p>
        </div>

        {{-- Card: Manual Bahan --}}
        <div x-show="lines.some(line => line.type === 'finished' && line.use_bom === 'no')" x-cloak class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="text-[13px] font-bold text-gray-700 flex items-center gap-2 mb-4">
                <span class="w-5 h-5 rounded-md bg-amber-100 inline-flex items-center justify-center">
                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </span>
                Input Manual Bahan
            </h2>

            <div id="manual-ingredients" class="space-y-3">
                <div class="flex flex-wrap gap-2 items-end bg-gray-50 rounded-lg p-3">
                    <div class="flex-1 min-w-[130px]">
                        <label class="cpd-label">Bahan</label>
                        <select name="manual_ingredients[0][stock_id]" class="cpd-select" onchange="updateUnitOptions(this, 0)">
                            <option value="">Pilih Bahan</option>
                            @foreach ($stocks as $stock)
                            <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}">{{ $stock->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="cpd-label">Jumlah</label>
                        <input type="number" name="manual_ingredients[0][quantity]" class="cpd-input" placeholder="0">
                    </div>
                    <div class="w-28">
                        <label class="cpd-label">Satuan</label>
                        <select name="manual_ingredients[0][unit_id]" id="unit-select-0" class="cpd-select">
                            <option value="">Satuan</option>
                            @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="button" id="add-ingredient"
                    class="mt-3 inline-flex items-center gap-1 text-[12px] font-semibold text-emerald-600 hover:text-emerald-700 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Bahan
            </button>
        </div>

        {{-- Submit --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('manager.operational.produksi') }}" class="h-10 px-5 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 hover:bg-gray-100 rounded-lg transition inline-flex items-center">← Batal</a>
                <button type="submit" class="h-10 px-6 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm cursor-pointer inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Simpan Produksi
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function produksiForm() {
    return {
        picIds: @json(old('pic_ids', [])),
        selectedPic: '',
        employees: @json($employees->map(fn($e) => ['id' => $e->id, 'name' => $e->name])),
        lines: @json($initialProductionLines),
        wageMap: @json($wageMap ?? []),
        sfpWageMap: @json($sfpWageMap ?? []),

        selectedPic: '',
        addPic() {
            const id = this.selectedPic;
            if (!id) return;
            if (!this.picIds.includes(id)) {
                this.picIds.push(id);
            }
            this.selectedPic = '';
        },
        removePic(index) {
            this.picIds.splice(index, 1);
        },
        onLineTypeChange(line) {
            if (line.type === 'finished') {
                line.semi_finished_product_id = '';
            } else if (line.type === 'semi') {
                line.product_variants_id = '';
                line.use_bom = 'yes';
            }
        },

        getLineWage(line) {
            const qty = parseFloat(line.quantity_produced) || 0;
            if (qty <= 0) return 0;
            if (line.type === 'semi') {
                const sfp = this.sfpWageMap[line.semi_finished_product_id];
                const wage = sfp ? parseFloat(sfp.labor_cost_per_unit) || 0 : 0;
                return Math.round(wage * qty);
            }
            const wage = parseFloat(this.wageMap[line.product_variants_id]) || 0;
            return Math.round(wage * qty);
        },
        get totalWage() {
            if (!Array.isArray(this.lines) || this.lines.length === 0) return 0;
            return this.lines.reduce((sum, line) => sum + this.getLineWage(line), 0);
        },
        get totalQuantity() {
            if (!Array.isArray(this.lines) || this.lines.length === 0) return 0;
            return this.lines.reduce((sum, line) => sum + (parseFloat(line.quantity_produced) || 0), 0);
        },
        get wagePerPic() {
            if (!this.picIds || !this.picIds.length) return 0;
            return Math.round(this.totalWage / this.picIds.length);
        },
        addLine() {
            this.lines.push({ type: 'finished', product_variants_id: '', semi_finished_product_id: '', quantity_produced: 0, use_bom: 'yes' });
        },
        removeLine(index) {
            this.lines.splice(index, 1);
        }
    };
}

  function updateUnitOptions(selectEl, index) {
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    const stockUnitType = selectedOption.getAttribute('data-unit-type');
    const unitSelect = document.querySelector(`#unit-select-${index}`);
    if (!unitSelect) return;
    Array.from(unitSelect.options).forEach(opt => {
        const type = opt.getAttribute('data-unit-type');
        opt.style.display = (!type || type === stockUnitType) ? '' : 'none';
    });
    unitSelect.selectedIndex = 0;
  }

  let ingredientIndex = 1;
  document.getElementById('add-ingredient').addEventListener('click', function () {
    const wrapper = document.getElementById('manual-ingredients');

    let stockOptionsHtml = '<option value="">Pilih Bahan</option>';
    let unitOptionsHtml = '<option value="">Satuan</option>';

    @foreach ($stocks as $stock)
      stockOptionsHtml += `<option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}">{{ $stock->name }}</option>`;
    @endforeach

    @foreach ($units as $unit)
      unitOptionsHtml += `<option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>`;
    @endforeach

    const html = `
      <div class="flex flex-wrap gap-2 items-end bg-gray-50 rounded-lg p-3" id="ingredient-${ingredientIndex}">
        <div class="flex-1 min-w-[130px]">
          <label class="cpd-label">Bahan</label>
          <select name="manual_ingredients[${ingredientIndex}][stock_id]" class="cpd-select" onchange="updateUnitOptions(this, ${ingredientIndex})">
            ${stockOptionsHtml}
          </select>
        </div>
        <div class="w-24">
          <label class="cpd-label">Jumlah</label>
          <input type="number" name="manual_ingredients[${ingredientIndex}][quantity]" class="cpd-input" placeholder="0">
        </div>
        <div class="w-28">
          <label class="cpd-label">Satuan</label>
          <select name="manual_ingredients[${ingredientIndex}][unit_id]" id="unit-select-${ingredientIndex}" class="cpd-select">
            ${unitOptionsHtml}
          </select>
        </div>
        <button type="button" class="w-8 h-10 rounded-lg inline-flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer" onclick="removeIngredientRow(${ingredientIndex})">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', html);
    ingredientIndex++;
  });

  function removeIngredientRow(index) {
    const row = document.getElementById(`ingredient-${index}`);
    if (row) row.remove();
  }
</script>
@endpush

