@extends('handai-manager.layouts.master')

@section('title', 'Edit Resep')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Resep untuk {{ $productVariant->product->name }} - {{ $variantLabel }}</h1>

    <form method="POST" action="{{ route('manager.inventory.recipes.update', $productVariant->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-2">Bahan</label>
            <div id="ingredients-wrapper">
            @if(isset($boms) && count($boms))
                @foreach ($boms as $index => $bom)
                <div class="flex gap-2 mb-2 ingredient-row">
                    <select name="ingredients[{{ $index }}][stock_id]" class="stock-dropdown border p-2 rounded w-1/3" data-index="{{ $index }}" required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach ($stocks as $stock)
                            <option value="{{ $stock->id }}"
                                data-unit-type="{{ $stock->unit->unit_type }}"
                                {{ $stock->id == $bom->stock_id ? 'selected' : '' }}>
                                {{ $stock->name }}
                            </option>
                        @endforeach
                    </select>

                    <input type="number" name="ingredients[{{ $index }}][quantity]" value="{{ $bom->quantity_required }}" placeholder="Jumlah" class="border p-2 rounded w-1/4" step="0.01" required>

                    <select name="ingredients[{{ $index }}][unit_id]" class="unit-dropdown border p-2 rounded w-1/3" required>
                        <option value="">-- Pilih Satuan --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}"
                                {{ $unit->id == $bom->unit_id ? 'selected' : '' }}>
                                {{ $unit->symbol }} ({{ $unit->name }})
                            </option>
                        @endforeach
                    </select>

                    <button type="button" class="remove-ingredient text-red-500 font-bold">×</button>
                </div>
                @endforeach
            @endif
            </div>

            <button type="button" id="add-ingredient" class="mt-2 text-blue-600 text-sm hover:underline">+ Tambah Bahan</button>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Simpan Perubahan</button>
            <a href="{{ route('manager.inventory.recipes.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 ml-2">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
    let ingredientIndex = {{ isset($boms) ? count($boms) : 0 }};

    function filterUnitsByType(unitDropdown, unitType) {
        Array.from(unitDropdown.options).forEach(option => {
            const optType = option.getAttribute('data-unit-type');
            if (!optType || optType === unitType) {
                option.hidden = false;
            } else {
                option.hidden = true;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Saat halaman pertama kali dibuka, filter semua satuan berdasarkan bahan yang sudah terisi
        document.querySelectorAll('.stock-dropdown').forEach(function (stockSelect) {
            const index = stockSelect.dataset.index;
            const selectedOption = stockSelect.options[stockSelect.selectedIndex];
            const unitType = selectedOption.getAttribute('data-unit-type');
            const unitDropdown = document.querySelector(`select[name="ingredients[${index}][unit_id]"]`);

            if (unitDropdown && unitType) {
                filterUnitsByType(unitDropdown, unitType);
            }
        });
    });

    document.getElementById('add-ingredient').addEventListener('click', function () {
        const wrapper = document.getElementById('ingredients-wrapper');
        const row = document.createElement('div');
        row.classList.add('flex', 'gap-2', 'mb-2', 'ingredient-row');

        row.innerHTML = `
            <select name="ingredients[${ingredientIndex}][stock_id]" class="stock-dropdown border p-2 rounded w-1/3" data-index="${ingredientIndex}" required>
                <option value="">-- Pilih Bahan --</option>
                @foreach ($stocks as $stock)
                    <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type }}">{{ $stock->name }}</option>
                @endforeach
            </select>

            <input type="number" name="ingredients[${ingredientIndex}][quantity]" placeholder="Jumlah" class="border p-2 rounded w-1/4" step="0.01" required>

            <select name="ingredients[${ingredientIndex}][unit_id]" class="unit-dropdown border p-2 rounded w-1/3" required>
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

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-ingredient')) {
            e.target.closest('.ingredient-row').remove();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.matches('.stock-dropdown')) {
            const index = e.target.dataset.index;
            const selected = e.target.options[e.target.selectedIndex];
            const unitType = selected.getAttribute('data-unit-type');
            const unitDropdown = document.querySelector(`select[name="ingredients[${index}][unit_id]"]`);

            if (unitDropdown && unitType) {
                filterUnitsByType(unitDropdown, unitType);
            }
        }
    });
</script>
@endpush
