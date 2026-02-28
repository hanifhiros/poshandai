@extends('handai-manager.layouts.master')

@section('title', 'Edit Product')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Product</h2>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded shadow-sm text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="input input-bordered w-full" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="input input-bordered w-full" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expired Duration (in days)</label>
                    <input type="number" name="expired_duration" value="{{ old('expired_duration', $product->expired_duration) }}" class="input input-bordered w-full" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <input type="file" name="image" class="file-input file-input-bordered w-full">
                    @if($product->image_url)
                        <img src="{{ asset('storage/' . $product->image_url) }}" class="w-24 mt-2 border rounded shadow" />
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Product Variants</h3>
                <div id="variant-container" class="space-y-6">
                    @foreach ($product->variants as $index => $variant)
                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                        <div class="grid sm:grid-cols-4 md:grid-cols-5 gap-4 items-end bg-gray-50 p-4 rounded shadow-sm variant-row">
                            @php
                                $sizeAttribute = $variantAttributes->where('name', 'Size')->first();
                            @endphp
                            
                            @if($sizeAttribute)
                            <div>
                                <label class="text-sm font-medium block mb-1">{{ $sizeAttribute->name }}</label>
                                <select name="variants[{{ $index }}][options][{{ $sizeAttribute->id }}]" class="border p-2 rounded w-full">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($sizeAttribute->options as $option)
                                        <option value="{{ $option->id }}" @if($variant->options->contains('id', $option->id)) selected @endif>
                                            {{ $option->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                
                            <div>
                                <label class="text-sm font-medium block mb-1">Harga</label>
                                <input type="number" name="variants[{{ $index }}][price]" value="{{ $variant->price }}" class="border p-2 rounded w-full" placeholder="Rp">
                            </div>
                
                            <div>
                                <label class="text-sm font-medium block mb-1">Qty</label>
                                <input type="number" name="variants[{{ $index }}][quantity]" value="{{ $variant->quantity }}" class="border p-2 rounded w-full" placeholder="Qty">
                            </div>
                
                            <div>
                                <label class="text-sm font-medium block mb-1">HPP</label>
                                <input type="number" step="0.01" name="variants[{{ $index }}][hpp]" value="{{ $variant->hpp }}" class="border p-2 rounded w-full" placeholder="Rp">
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <button type="button" id="add-variant" class="mt-3 bg-blue-500 text-white px-3 py-1 rounded">
                    + Tambah Variant
                </button>
                
                {{-- <div id="variant-container" class="space-y-6">
                    @foreach ($product->variants as $index => $variant)
                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                        <div class="grid sm:grid-cols-3 md:grid-cols-4 gap-4 items-end bg-gray-50 p-4 rounded shadow-sm">
                            @foreach ($variantAttributes as $attribute)
                                <div>
                                    <label class="text-sm font-medium block mb-1">{{ $attribute->name }}</label>
                                    <select name="variants[{{ $index }}][options][{{ $attribute->id }}]" class="border p-2 rounded w-full">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($attribute->options as $option)
                                            <option value="{{ $option->id }}" @if($variant->options->contains('id', $option->id)) selected @endif>
                                                {{ $option->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach

                            <div>
                                <label class="text-sm font-medium block mb-1">Harga</label>
                                <input type="number" name="variants[{{ $index }}][price]" value="{{ $variant->price }}" class="border p-2 rounded w-full" placeholder="Rp">
                            </div>

                            <div>
                                <label class="text-sm font-medium block mb-1">Qty</label>
                                <input type="number" name="variants[{{ $index }}][quantity]" value="{{ $variant->quantity }}" class="border p-2 rounded w-full" placeholder="Qty">
                            </div>
                        </div>
                    @endforeach
                </div> --}}
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-success px-6">Update Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    let newVariantIndex = {{ count($product->variants) }};
    const attributes = @json($variantAttributes);
    const sizeAttribute = attributes.find(attr => attr.name === 'Size');

    document.getElementById('add-variant').addEventListener('click', () => {
        const container = document.getElementById('variant-container');

        let html = `<div class="grid sm:grid-cols-4 md:grid-cols-5 gap-4 items-end bg-gray-50 p-4 rounded shadow-sm">`;
        
        if (sizeAttribute) {
            html += `
                <div>
                    <label class="text-sm font-medium block mb-1">${sizeAttribute.name}</label>
                    <select name="variants[${newVariantIndex}][options][${sizeAttribute.id}]" class="border p-2 rounded w-full">
                        <option value="">-- Pilih --</option>
                        ${sizeAttribute.options.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('')}
                    </select>
                </div>`;
        }

        html += `
            <div>
                <label class="text-sm font-medium block mb-1">Harga</label>
                <input type="number" name="variants[${newVariantIndex}][price]" class="border p-2 rounded w-full" placeholder="Rp">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1">Qty</label>
                <input type="number" name="variants[${newVariantIndex}][quantity]" class="border p-2 rounded w-full" placeholder="Qty">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1">HPP</label>
                <input type="number" step="0.01" name="variants[${newVariantIndex}][hpp]" class="border p-2 rounded w-full" placeholder="Rp">
            </div>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
        newVariantIndex++;
    });
</script>

@endsection
