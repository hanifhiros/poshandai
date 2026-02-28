@extends('handai-manager.layouts.master')

@section('title', 'Buat R&D Baru')

@section('content')
<style>
    .crd-input { width: 100%; height: 40px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .crd-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .crd-label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
    .crd-select { width: 100%; height: 40px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; appearance: none; cursor: pointer; }
    .crd-select:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .crd-textarea { width: 100%; padding: 10px 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; resize: vertical; }
    .crd-textarea:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[720px] mx-auto">

    {{-- Back link --}}
    <a href="{{ route('manager.operational.rnd') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-400 hover:text-gray-600 mb-4 group">
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke R&D
    </a>

    {{-- Header --}}
    <div class="mb-5">
        <h1 class="text-[19px] font-bold text-gray-800 leading-tight">Buat R&D Baru</h1>
        <p class="text-[13px] text-gray-400 mt-0.5">Catat proyek riset & pengembangan baru</p>
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
    <form method="POST" action="{{ route('manager.operational.rnd.store') }}" class="space-y-5">
        @csrf

        {{-- Card: Info Proyek --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
            <h2 class="text-[13px] font-bold text-gray-700 flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-emerald-100 inline-flex items-center justify-center">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </span>
                Info Proyek
            </h2>

            <div>
                <label class="crd-label">Nama Proyek R&D</label>
                <input type="text" name="rnd_name" value="{{ old('rnd_name') }}" class="crd-input" placeholder="Mis: Formula Rasa Baru" required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="crd-label">Tanggal</label>
                    <input type="date" name="rnd_date" value="{{ old('rnd_date', now()->toDateString()) }}" class="crd-input" required>
                </div>
                <div>
                    <label class="crd-label">PIC (Penanggung Jawab)</label>
                    <select name="pic_id" class="crd-select" required>
                        <option value="">— Pilih PIC —</option>
                        @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('pic_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="crd-label">Deskripsi</label>
                <textarea name="description" class="crd-textarea" rows="3" placeholder="Deskripsikan tujuan R&D..." required>{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Card: Bahan --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="text-[13px] font-bold text-gray-700 flex items-center gap-2 mb-4">
                <span class="w-5 h-5 rounded-md bg-amber-100 inline-flex items-center justify-center">
                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </span>
                Bahan yang Dibutuhkan
            </h2>

            <div id="rnd-stock-list" class="space-y-3">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 items-end">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="crd-label">Bahan</label>
                            <select name="rnd_ingredients[0][stock_id]" class="crd-select" onchange="toggleManualInput(this, 0)" data-index="0">
                                <option value="">Pilih Bahan</option>
                                @foreach($stocks as $stock)
                                <option value="{{ $stock->id }}" data-unit-type="{{ $stock->unit->unit_type }}">{{ $stock->name }}</option>
                                @endforeach
                                <option value="manual">— Input Manual —</option>
                            </select>
                        </div>
                        <div class="hidden" id="manual-wrap-0">
                            <label class="crd-label">Nama Manual</label>
                            <input type="text" name="rnd_ingredients[0][manual_name]" id="manual-name-0" class="crd-input" placeholder="Nama bahan">
                        </div>
                        <div>
                            <label class="crd-label">Jumlah</label>
                            <input type="number" name="rnd_ingredients[0][quantity_used]" class="crd-input" placeholder="0" required>
                        </div>
                        <div>
                            <label class="crd-label">Satuan</label>
                            <select name="rnd_ingredients[0][unit_id]" id="unit-select-0" class="crd-select" required>
                                <option value="">Satuan</option>
                                @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" data-unit-type="{{ $unit->unit_type }}">{{ $unit->symbol }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="crd-label">Biaya (Rp)</label>
                            <input type="number" name="rnd_ingredients[0][cost]" class="crd-input" placeholder="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" onclick="addStockRow()"
                    class="mt-3 inline-flex items-center gap-1 text-[12px] font-semibold text-emerald-600 hover:text-emerald-700 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Bahan
            </button>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="h-10 px-6 text-[13px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm cursor-pointer inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Simpan R&D
            </button>
            <a href="{{ route('manager.operational.rnd') }}" class="h-10 px-5 text-[13px] font-medium text-gray-400 hover:text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-lg transition inline-flex items-center">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const stocks = @json($stocks);
const units = @json($units);
let rndIndex = 1;

function filterUnits(select, index) {
    const selectedOption = select.options[select.selectedIndex];
    const selectedType = selectedOption.getAttribute('data-unit-type');
    const unitSelect = document.getElementById(`unit-select-${index}`);
    if (!unitSelect) return;
    Array.from(unitSelect.options).forEach(opt => {
        const type = opt.getAttribute('data-unit-type');
        opt.style.display = (!type || type === selectedType) ? '' : 'none';
    });
    unitSelect.selectedIndex = 0;
}

function toggleManualInput(selectEl, index) {
    const manualWrap = document.getElementById(`manual-wrap-${index}`);
    const manualInput = document.getElementById(`manual-name-${index}`);
    const unitSelect = document.getElementById(`unit-select-${index}`);

    if (selectEl.value === 'manual') {
        if (manualWrap) manualWrap.classList.remove('hidden');
        Array.from(unitSelect.options).forEach(opt => opt.style.display = '');
        unitSelect.selectedIndex = 0;
    } else {
        if (manualWrap) manualWrap.classList.add('hidden');
        if (manualInput) manualInput.value = '';
        filterUnits(selectEl, index);
    }
}

function addStockRow() {
    const wrapper = document.getElementById('rnd-stock-list');

    let stockOptions = `<option value="">Pilih Bahan</option>`;
    stocks.forEach(s => {
        stockOptions += `<option value="${s.id}" data-unit-type="${s.unit?.unit_type ?? ''}">${s.name}</option>`;
    });
    stockOptions += `<option value="manual">— Input Manual —</option>`;

    let unitOptions = `<option value="">Satuan</option>`;
    units.forEach(u => {
        unitOptions += `<option value="${u.id}" data-unit-type="${u.unit_type}">${u.symbol}</option>`;
    });

    const html = `
    <div class="bg-gray-50 rounded-lg p-3 relative" id="rnd-row-${rndIndex}">
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 items-end">
            <div class="col-span-2 sm:col-span-1">
                <label class="crd-label">Bahan</label>
                <select name="rnd_ingredients[${rndIndex}][stock_id]" class="crd-select" onchange="toggleManualInput(this, ${rndIndex})" id="stock-select-${rndIndex}">
                    ${stockOptions}
                </select>
            </div>
            <div class="hidden" id="manual-wrap-${rndIndex}">
                <label class="crd-label">Nama Manual</label>
                <input type="text" name="rnd_ingredients[${rndIndex}][manual_name]" id="manual-name-${rndIndex}" class="crd-input" placeholder="Nama bahan">
            </div>
            <div>
                <label class="crd-label">Jumlah</label>
                <input type="number" name="rnd_ingredients[${rndIndex}][quantity_used]" class="crd-input" placeholder="0" required>
            </div>
            <div>
                <label class="crd-label">Satuan</label>
                <select name="rnd_ingredients[${rndIndex}][unit_id]" id="unit-select-${rndIndex}" class="crd-select" required>
                    ${unitOptions}
                </select>
            </div>
            <div>
                <label class="crd-label">Biaya (Rp)</label>
                <input type="number" name="rnd_ingredients[${rndIndex}][cost]" class="crd-input" placeholder="0" required>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-8 h-10 rounded-lg inline-flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 transition cursor-pointer" onclick="removeRndRow(${rndIndex})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </div>`;

    wrapper.insertAdjacentHTML('beforeend', html);
    rndIndex++;
}

function removeRndRow(index) {
    const row = document.getElementById(`rnd-row-${index}`);
    if (row) row.remove();
}
</script>
@endpush


