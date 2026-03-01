@extends('handai-manager.layouts.master')

@section('title', 'Tambah Resep')

@push('styles')
<style>
    .rcp-input, .rcp-select {
        height: 36px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0 12px;
        font-size: 13px;
        color: #334155;
        transition: border-color .15s, box-shadow .15s;
        width: 100%;
    }
    .rcp-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19.5 8.25l-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 32px;
    }
    .rcp-input:focus, .rcp-select:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5,150,105,.1);
    }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[860px] mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('manager.inventory.recipes.index') }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Tambah Resep</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Buat Bill of Materials baru untuk produk</p>
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

    <form action="{{ route('manager.inventory.recipes.store') }}" method="POST">
        @csrf

        {{-- Product & Variant Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <h2 class="text-[13px] font-semibold text-gray-700 mb-4">Produk & Varian</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Pilih Produk</label>
                    <select name="product_id" id="product_id" class="rcp-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1.5">Varian Produk</label>
                    <select name="product_variant_id" id="product_variant_id" class="rcp-select" required>
                        <option value="">-- Pilih Varian --</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Ingredients Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-[13px] font-semibold text-gray-700">Daftar Bahan</h2>
                <button type="button" id="add-ingredient" class="h-7 px-3 inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-md transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Bahan
                </button>
            </div>

            {{-- Column Headers --}}
            <div class="hidden sm:grid sm:grid-cols-[1fr_100px_1fr_32px] gap-2 mb-2 px-1">
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Bahan</span>
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Jumlah</span>
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Satuan</span>
                <span></span>
            </div>

            <div id="ingredients-wrapper" class="space-y-2">
                <div class="grid grid-cols-1 sm:grid-cols-[1fr_100px_1fr_32px] gap-2 items-center ingredient-row">
                    <select class="stock-dropdown rcp-select" name="ingredients[0][stock_id]" data-index="0" required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach ($stocks as $stock)
                            <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}">{{ $stock->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="ingredients[0][quantity]" placeholder="0" class="rcp-input text-center" step="0.01" required>
                    <select class="unit-dropdown rcp-select" name="ingredients[0][unit_id]">
                        <option value="">-- Satuan --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }} ({{ $unit->name }})</option>
                        @endforeach
                    </select>
                    <button type="button" class="remove-ingredient h-8 w-8 inline-flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="h-9 px-5 inline-flex items-center gap-1.5 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Simpan Resep
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const variantOptions = @json($sizePricesByProduct);

    document.getElementById('product_id').addEventListener('change', function () {
        const productId = this.value;
        const variantSelect = document.getElementById('product_variant_id');
        variantSelect.innerHTML = '<option value="">-- Pilih Varian --</option>';

        if (variantOptions[productId]) {
            variantOptions[productId].forEach(function (variant) {
                variantSelect.innerHTML += `<option value="${variant.id}">${variant.size}</option>`;
            });
        }
    });

    // Add ingredient row
    let ingredientIndex = 1;
    document.getElementById('add-ingredient').addEventListener('click', function () {
        const wrapper = document.getElementById('ingredients-wrapper');
        const row = document.createElement('div');
        row.classList.add('grid', 'grid-cols-1', 'sm:grid-cols-[1fr_100px_1fr_32px]', 'gap-2', 'items-center', 'ingredient-row');
        row.innerHTML = `
    <select class="stock-dropdown rcp-select" name="ingredients[${ingredientIndex}][stock_id]" data-index="${ingredientIndex}" required>
        <option value="">-- Pilih Bahan --</option>
        @foreach ($stocks as $stock)
            <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}">{{ $stock->name }}</option>
        @endforeach
    </select>
    <input type="number" name="ingredients[${ingredientIndex}][quantity]" placeholder="0" class="rcp-input text-center" step="0.01" required>
    <select class="unit-dropdown rcp-select" name="ingredients[${ingredientIndex}][unit_id]">
        <option value="">-- Satuan --</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }} ({{ $unit->name }})</option>
        @endforeach
    </select>
    <button type="button" class="remove-ingredient h-8 w-8 inline-flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
`;
        wrapper.appendChild(row);
        ingredientIndex++;
    });

    // Remove ingredient row
    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-ingredient')) {
            e.target.closest('.ingredient-row').remove();
        }
    });

    // Filter units by type when stock changes
    function filterUnitsByType(selectEl, type) {
        const allUnits = selectEl.querySelectorAll('option');
        allUnits.forEach(opt => {
            const optType = opt.getAttribute('data-unit-type');
            opt.hidden = optType && optType !== type;
        });
    }

    document.addEventListener('change', function (e) {
        if (e.target.matches('.stock-dropdown')) {
            const index = e.target.dataset.index;
            const selectedOption = e.target.selectedOptions[0];
            const unitType = selectedOption.getAttribute('data-unit-type');

            const unitSelect = document.querySelector(`.unit-dropdown[name="ingredients[${index}][unit_id]"]`);
            if (unitSelect && unitType) {
                filterUnitsByType(unitSelect, unitType);
            }
        }
    });
</script>
@endpush
