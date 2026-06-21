@extends('layouts.master')

@section('title', 'Add New Product')

@section('content')
<div class="min-h-screen bg-slate-50/60 py-6 px-4 sm:px-6 lg:px-8 pb-20">
    {{-- header --}}
    <div class="max-w-4xl mx-auto mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.inventory.products') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Tambah Produk Baru</h1>
                    <p class="text-sm text-slate-500">Isi informasi produk dan variannya</p>
                </div>
            </div>
    </div>

    <div class="max-w-4xl mx-auto">
        @if ($errors->any())
            <div class="mb-4">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="productForm" x-data="{ submitting: false }" method="POST" action="{{ route('manager.products.store') }}" enctype="multipart/form-data" class="space-y-5" @submit.prevent="submitting=true; $el.submit();">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Informasi Produk
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Nama Produk <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Kategori <span class="text-red-400">*</span></label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Gambar Produk</label>
                            <input type="file" name="image" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm">
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Durasi Kadaluarsa</label>
                                <input type="number" name="expired_duration_value" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" placeholder="contoh: 7">
                            </div>
                            <div class="w-1/3">
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Unit</label>
                                <select name="expired_duration_unit" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none">
                                    <option value="days">Days</option>
                                    <option value="weeks">Weeks</option>
                                    <option value="months">Months</option>
                                    <option value="years">Years</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- variants section --}}

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">Kombinasi Varian & Harga</h2>
                    <button type="button" id="add-combination" class="h-9 px-3.5 inline-flex items-center gap-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-transparent hover:border-blue-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Varian</span>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div id="variant-combinations" class="space-y-4">
                        <div class="variant-row bg-white border border-slate-200 p-4 rounded flex flex-wrap gap-3 items-end">
                                @php $sizeAttribute = $variantAttributes->where('name','Size')->first(); @endphp
                                @if($sizeAttribute)
                                <div class="flex-1 min-w-[150px]">
                                    <label class="text-sm font-medium block mb-1">Size <span class="text-red-500">*</span></label>
                                    <select name="combinations[0][variants][{{ $sizeAttribute->id }}]" class="border border-slate-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                        <option value="">-- Pilih Size --</option>
                                        @foreach ($sizeAttribute->options as $option)
                                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="flex-1 min-w-[120px]">
                                    <label class="text-sm font-medium block mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                                    <input type="number" name="combinations[0][price]" class="border border-slate-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="15000" min="0" required>
                                </div>
                                <div class="flex-1 min-w-[120px]">
                                    <label class="text-sm font-medium block mb-1">Qty Awal (Stok Initial)</label>
                                    <input type="number" name="combinations[0][quantity]" class="border border-slate-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="0" min="0">
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="remove-combination bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm hidden">âœ•</button>
                                </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
            <div class="bg-white rounded-2xl border border-slate-200/80 mt-4 shadow-sm p-6">
                <div class="flex items-center justify-between gap-3">

                     <a href="{{ route('manager.inventory.products') }}" class="h-9 px-4 inline-flex items-center text-[13px] font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:bg-gray-100 rounded-lg transition">Batal</a>

                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm">
                        Tambah Produk
                    </button>
                </div>
            </div>
        </form>
</div>


<script>
let variantIndex = 1;
const attributes = @json($variantAttributes);
const sizeAttribute = attributes.find(attr => attr.name === 'Size');

document.getElementById('add-combination').addEventListener('click', () => {
    const wrapper = document.getElementById('variant-combinations');

    let html = `<div class="variant-row bg-white border border-gray-200 p-4 rounded flex flex-wrap gap-3 items-end">`;

    html += `
        ${sizeAttribute ? `<div class="flex-1 min-w-[150px]">
            <label class="text-sm font-medium block mb-1">Size <span class="text-red-500">*</span></label>
            <select name="combinations[${variantIndex}][variants][${sizeAttribute.id}]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-green-500" required>
                <option value="">-- Pilih Size --</option>
                ${sizeAttribute.options.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('')}
            </select>
        </div>` : ''}
        <div class="flex-1 min-w-[120px]">
            <label class="text-sm font-medium block mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="combinations[${variantIndex}][price]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="28000" min="0" required>
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="text-sm font-medium block mb-1">Qty Awal (Stok Initial)</label>
            <input type="number" name="combinations[${variantIndex}][quantity]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="0" min="0">
        </div>
        <div class="flex-shrink-0">
            <button type="button" class="remove-combination bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm">âœ•</button>
        </div>
        </div>
    </div>`;

    wrapper.insertAdjacentHTML('beforeend', html);
    
    // Add event listener untuk remove button
    document.querySelectorAll('.remove-combination').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.closest('.variant-row').remove();
        });
    });
    
    variantIndex++;
});

// Add event listener untuk remove button pada variant pertama (hidden)
document.querySelectorAll('.remove-combination').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest('.variant-row').remove();
    });
});
</script>
    

    
@endsection



