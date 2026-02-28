@extends('handai-manager.layouts.master')

@section('title', 'Tambah Produksi')

@section('content')
<style>
    .cpd-input { width: 100%; height: 40px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .cpd-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .cpd-label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .cpd-select { width: 100%; height: 40px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; appearance: none; cursor: pointer; }
    .cpd-select:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
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
    <form method="POST" action="{{ route('manager.operational.produksi.store') }}" class="space-y-5">
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
                <div>
                    <label class="cpd-label">PIC (Penanggung Jawab)</label>
                    <select name="pic_id" class="cpd-select" required>
                        <option value="">— Pilih PIC —</option>
                        @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('pic_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="cpd-label">Produk + Varian</label>
                <select name="product_variants_id" class="cpd-select" required>
                    <option value="">— Pilih Produk Varian —</option>
                    @foreach ($productVariants as $variant)
                    <option value="{{ $variant->id }}" {{ old('product_variants_id') == $variant->id ? 'selected' : '' }}>
                        {{ $variant->product->name }} — {{ $variant->options->pluck('name')->join(', ') }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="cpd-label">Jumlah Diproduksi</label>
                    <input type="number" name="quantity_produced" value="{{ old('quantity_produced') }}" min="1" class="cpd-input" placeholder="0" required>
                </div>
                <div>
                    <label class="cpd-label">Pengurangan Stok</label>
                    <select name="use_bom" class="cpd-select" id="use-bom-select" required>
                        <option value="yes" {{ old('use_bom', 'yes') === 'yes' ? 'selected' : '' }}>Otomatis (Resep/BOM)</option>
                        <option value="no" {{ old('use_bom') === 'no' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Card: Manual Bahan --}}
        <div id="manual-stock-section" class="hidden bg-white rounded-xl border border-gray-100 shadow-sm p-5">
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
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="h-10 px-6 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Simpan Produksi
            </button>
            <a href="{{ route('manager.operational.produksi') }}" class="h-10 px-5 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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

  document.addEventListener('DOMContentLoaded', function () {
    const bomSelect = document.getElementById('use-bom-select');
    const manualSection = document.getElementById('manual-stock-section');

    function toggleManual() {
        if (bomSelect.value === 'no') {
            manualSection.classList.remove('hidden');
        } else {
            manualSection.classList.add('hidden');
        }
    }
    toggleManual();
    bomSelect.addEventListener('change', toggleManual);
  });

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

