@extends('handai-manager.layouts.master')

@section('title', 'Catat Waste')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto"
     x-data="wasteForm()">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.waste.index') }}"
           class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
            <i class="ti ti-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Catat Waste / Basi</h1>
            <p class="text-sm text-gray-500">Catat bahan atau produk yang harus dibuang</p>
        </div>
    </div>

    <form action="{{ route('manager.operational.waste.store') }}" method="POST"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="waste_date" value="{{ old('waste_date', date('Y-m-d')) }}" required
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Tipe Item --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Item <span class="text-red-500">*</span></label>
                <select name="item_type" x-model="itemType" required
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="stock">Bahan Baku</option>
                    <option value="product">Produk Jadi</option>
                </select>
            </div>

            {{-- Stock (bahan baku) --}}
            <div x-show="itemType === 'stock'" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bahan <span class="text-red-500">*</span></label>
                <select name="stock_id"
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih Bahan --</option>
                    @foreach($stocks as $stock)
                    <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Product Variant (produk jadi) --}}
            <div x-show="itemType === 'product'" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk <span class="text-red-500">*</span></label>
                <select name="product_variant_id"
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($variants as $v)
                    <option value="{{ $v->id }}">{{ $v->product->name ?? '' }} {{ $v->variantLabel ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Quantity --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" value="{{ old('quantity') }}" step="0.001" min="0.001" required
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Unit --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                <select name="unit_id"
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Otomatis --</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Reason --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label>
                <select name="reason" required
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @foreach($reasons as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- PIC --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
                <select name="pic_id"
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Notes --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Detail tambahan..."
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.waste.index') }}"
               class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">Batal</a>
            <button type="submit"
                    class="px-6 py-2.5 bg-red-600 text-white rounded-xl text-sm font-medium hover:bg-red-700 transition shadow-sm">
                <i class="ti ti-check"></i> Simpan Waste
            </button>
        </div>
    </form>
</div>

<script>
function wasteForm() {
    return { itemType: 'stock' };
}
</script>
@endsection
