@extends('handai-manager.layouts.master')

@section('title', 'Add New Product')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Add New Product</h1>
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form method="POST" action="{{ route('manager.products.store') }}" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf

        <div>
            <label for="name" class="block font-medium">Product Name</label>
            <input type="text" name="name" id="name" class="border p-2 rounded w-full" required>
        </div>

        <div>
            <label for="category_id" class="block font-medium">Category</label>
            <select name="category_id" id="category_id" class="border p-2 rounded w-full" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="image" class="block font-medium">Product Image</label>
            <input type="file" name="image" id="image" class="border p-2 rounded w-full">
        </div>
        <div class="flex gap-4">
            <div class="w-2/3">
                <label for="expired_duration_value" class="block font-medium">Expired Duration</label>
                <input type="number" name="expired_duration_value" class="border p-2 rounded w-full" placeholder="Masukkan jumlah (contoh: 7)">
            </div>
            <div class="w-1/3">
                <label for="expired_duration_unit" class="block font-medium">&nbsp;</label>
                <select name="expired_duration_unit" class="border p-2 rounded w-full">
                    <option value="days">Days</option>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
                </select>
            </div>
        </div>
        
        {{-- <div id="variant-wrapper" class="space-y-4">
            @foreach ($variantAttributes as $attribute)
                <div class="variant-group space-y-2">
                    <h4 class="font-semibold text-gray-600">{{ $attribute->name }}</h4>
                    <div class="flex gap-4">
                        <div class="w-1/3">
                            <input type="hidden" name="variants[{{ $attribute->id }}][attribute_id]" value="{{ $attribute->id }}">
                            <select name="variants[{{ $attribute->id }}][value]" class="variant-dropdown border p-2 rounded w-full">
                                @foreach ($attribute->options as $option)
                                    <option value="{{ $option->name }}">{{ $option->name }}</option>
                                @endforeach
                                <option value="__new__">+ Tambah Opsi Baru</option>
                            </select>
                            <input type="text" name="variants[{{ $attribute->id }}][new_value]" class="new-variant-input border mt-2 p-2 rounded w-full hidden" placeholder="Masukkan opsi baru">
                        </div>
        
                        <div class="w-1/3">
                            <input type="number" name="variants[{{ $attribute->id }}][price]" placeholder="Harga" class="border p-2 rounded w-full">
                        </div>
        
                        <div class="w-1/3">
                            <input type="number" name="variants[{{ $attribute->id }}][quantity]" placeholder="Kuantitas" class="border p-2 rounded w-full">
                        </div>
                    </div>
                </div>
            @endforeach
        </div> --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Kombinasi Varian & Harga</h3>
            <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-4">
                <p class="text-sm text-gray-600">💡 <strong>Contoh:</strong> Untuk produk coffee dengan varian ukuran, Anda bisa menambahkan:
                <br>- Size: 250ml, Harga: 15.000
                <br>- Size: 500ml, Harga: 28.000  
                <br>- Size: 1000ml, Harga: 54.000
                </p>
            </div>

            <div id="variant-combinations" class="space-y-4">
                <div class="variant-row bg-white border border-gray-200 p-4 rounded">
                    <div class="grid grid-cols-12 gap-3 items-end">
                        @php
                            $sizeAttribute = $variantAttributes->where('name', 'Size')->first();
                        @endphp
                        
                        @if($sizeAttribute)
                        <div class="col-span-5">
                            <label class="text-sm font-medium block mb-1">Size <span class="text-red-500">*</span></label>
                            <select name="combinations[0][variants][{{ $sizeAttribute->id }}]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">-- Pilih Size --</option>
                                @foreach ($sizeAttribute->options as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
            
                        <div class="col-span-5">
                            <label class="text-sm font-medium block mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="combinations[0][price]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="15000" min="0" required>
                        </div>
            
                        <div class="col-span-2 flex justify-end">
                            <button type="button" class="remove-combination bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm hidden">✕</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-3 mt-3">
                        <div class="col-span-7">
                            <label class="text-sm font-medium block mb-1">Qty Awal (Stok Initial)</label>
                            <input type="number" name="combinations[0][quantity]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0" min="0">
                        </div>
                    </div>
                </div>
            </div>
        
            <button type="button" id="add-combination" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-medium">+ Tambah Varian Lain</button>
        </div>
        
        
        
        
       
        

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Add Product</button>
    </form>
</div>
<script>
let variantIndex = 1;
const attributes = @json($variantAttributes);
const sizeAttribute = attributes.find(attr => attr.name === 'Size');

document.getElementById('add-combination').addEventListener('click', () => {
    const wrapper = document.getElementById('variant-combinations');

    let html = `<div class="variant-row bg-white border border-gray-200 p-4 rounded">
        <div class="grid grid-cols-12 gap-3 items-end">`;

    if (sizeAttribute) {
        html += `
            <div class="col-span-5">
                <label class="text-sm font-medium block mb-1">Size <span class="text-red-500">*</span></label>
                <select name="combinations[${variantIndex}][variants][${sizeAttribute.id}]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Pilih Size --</option>
                    ${sizeAttribute.options.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('')}
                </select>
            </div>
        `;
    }

    html += `
        <div class="col-span-5">
            <label class="text-sm font-medium block mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="combinations[${variantIndex}][price]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="28000" min="0" required>
        </div>

        <div class="col-span-2 flex justify-end">
            <button type="button" class="remove-combination bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm">✕</button>
        </div>
        </div>

        <div class="grid grid-cols-12 gap-3 mt-3">
            <div class="col-span-7">
                <label class="text-sm font-medium block mb-1">Qty Awal (Stok Initial)</label>
                <input type="number" name="combinations[${variantIndex}][quantity]" class="border border-gray-300 p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0" min="0">
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


