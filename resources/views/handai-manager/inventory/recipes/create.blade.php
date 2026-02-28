@extends('handai-manager.layouts.master')

@section('title', 'Tambah Resep')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Tambah Resep (Bill of Materials)</h1>
    @if ($errors->any())
    <div class="text-red-600 mb-4">
        <ul class="text-sm list-disc ml-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('manager.inventory.recipes.store') }}" method="POST">
        @csrf

        <!-- Produk -->
        <div class="mb-4">
            <label class="block font-semibold mb-1">Pilih Produk</label>
            <select name="product_id" id="product_id" class="w-full border p-2 rounded" required>
                <option value="">-- Pilih Produk --</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Ukuran -->
        <div class="mb-4">
            <label class="block font-semibold mb-1">Varian Produk</label>
            <select name="product_variant_id" id="product_variant_id" class="w-full border p-2 rounded" required>
                <option value="">-- Pilih Varian --</option>
                {{-- Akan diisi lewat JS berdasarkan produk --}}
            </select>
        </div>


        <!-- Bahan -->
        <div class="mb-4">
            <label class="block font-semibold mb-2">Bahan</label>
            <div id="ingredients-wrapper">
                <div class="flex gap-2 mb-2 ingredient-row">
                    <select class="stock-dropdown border p-2 rounded w-1/2" name="ingredients[0][stock_id]" data-index="0" required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach ($stocks as $stock)
                            <option 
                                value="{{ $stock->id }}" 
                                data-unit-type="{{ $stock->unit->unit_type ?? '' }}"
                            >
                                {{ $stock->name }}
                            </option>
                        @endforeach
                    </select>
                    
                    <input type="number" name="ingredients[0][quantity]" placeholder="Jumlah" class="border p-2 rounded w-1/3" step="0.01" required>
                    <select class="unit-dropdown border p-2 rounded w-1/3" name="ingredients[0][unit_id]">
                        <option value="">-- Pilih Satuan --</option>
                        @foreach ($units as $unit)
                            <option 
                                value="{{ $unit->id }}" 
                                data-unit-type="{{ $unit->unit_type }}"
                            >
                                {{ $unit->symbol }} ({{ $unit->name }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="remove-ingredient text-red-500 font-bold">×</button>
                </div>
            </div>
            <button type="button" id="add-ingredient" class="mt-2 text-blue-600 text-sm hover:underline">+ Tambah Bahan</button>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Simpan Resep
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const sizePriceOptions = @json($sizePricesByProduct); // ex: {1: [{id: 1, size: 'Small'}, ...]}

    document.getElementById('product_id').addEventListener('change', function () {
        const productId = this.value;
        const sizeSelect = document.getElementById('size_price_id');
        sizeSelect.innerHTML = '<option value="">-- Pilih Ukuran --</option>';

        if (sizePriceOptions[productId]) {
            sizePriceOptions[productId].forEach(function (size) {
                sizeSelect.innerHTML += `<option value="${size.id}">${size.size}</option>`;
            });
        }
    });

    // Tambah baris bahan
    let ingredientIndex = 1;
    document.getElementById('add-ingredient').addEventListener('click', function () {
        const wrapper = document.getElementById('ingredients-wrapper');
        const row = document.createElement('div');
        row.classList.add('flex', 'gap-2', 'mb-2', 'ingredient-row');
        row.innerHTML = `
    <select class="stock-dropdown border p-2 rounded w-1/2" name="ingredients[${ingredientIndex}][stock_id]" data-index="${ingredientIndex}" required>
        <option value="">-- Pilih Bahan --</option>
        @foreach ($stocks as $stock)
            <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type ?? '' }}">{{ $stock->name }}</option>
        @endforeach
    </select>
    <input type="number" name="ingredients[${ingredientIndex}][quantity]" placeholder="Jumlah" class="border p-2 rounded w-1/3" step="0.01" required>
    <select class="unit-dropdown border p-2 rounded w-1/3" name="ingredients[${ingredientIndex}][unit_id]">
        <option value="">-- Pilih Satuan --</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }} ({{ $unit->name }})</option>
        @endforeach
    </select>
    <button type="button" class="remove-ingredient text-red-500 font-bold">×</button>
`;

        wrapper.appendChild(row);
        ingredientIndex++;
    });

    // Hapus baris bahan
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-ingredient')) {
            e.target.closest('.ingredient-row').remove();
        }
    });
    function filterUnitsByType(selectEl, type) {
        const allUnits = selectEl.querySelectorAll('option');
        allUnits.forEach(opt => {
            const optType = opt.getAttribute('data-unit-type');
            if (!optType || optType === type) {
                opt.hidden = false;
            } else {
                opt.hidden = true;
            }
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
    const variantOptions = @json($sizePricesByProduct); // ex: {1: [{id: 1, size: 'Sedang'}, ...]}

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

</script>
@endpush
