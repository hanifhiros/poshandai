@extends('layouts.master')

@section('title', 'Edit Product')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1100px] mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Edit Product</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui detail produk dan varian. UI dirancang agar lebih ringkas, fokus data, dan terasa modern.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.inventory.products') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition">
                <i class="ti ti-arrow-left"></i>
                Kembali ke Produk
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Kategori</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $product->category->category_name ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Variants</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $product->variants->count() }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Total Stock</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ number_format($product->variants->sum('quantity')) }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Harga Terendah</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">Rp {{ number_format($product->variants->min('price') ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Gambar Produk</p>
            @if($product->image_url)
                <img src="{{ asset('storage/' . $product->image_url) }}" class="w-full h-44 object-cover rounded-lg mt-3 border border-gray-100" />
            @else
                <div class="w-full h-44 bg-gray-50 border border-dashed border-gray-200 rounded-lg mt-3 flex items-center justify-center text-sm text-gray-400">
                    Belum ada gambar
                </div>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manager.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expired Duration (hari)</label>
                    <input type="number" name="expired_duration" value="{{ old('expired_duration', $product->expired_duration) }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                    <input type="file" name="image" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-700" />
                    @if($product->image_url)
                        <img src="{{ asset('storage/' . $product->image_url) }}" class="w-24 mt-3 border rounded-lg shadow-sm" />
                    @endif
                </div>
            </div>

            <div class="space-y-5">
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Deskripsi Singkat</p>
                    <p class="text-xs text-gray-500">Gunakan catatan ini untuk informasi internal (tidak tampil ke pelanggan).</p>
                    <textarea name="description" rows="5" class="mt-2 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">{{ old('description', $product->description ?? '') }}</textarea>
                </div>

                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Metadata</p>
                    <div class="grid grid-cols-2 gap-3 text-sm text-gray-600">
                        <div>
                            <p class="font-medium text-gray-700">ID Produk</p>
                            <p class="mt-1">#{{ $product->id }}</p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700">Dibuat</p>
                            <p class="mt-1">{{ $product->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Variants</h3>
                <button type="button" id="add-variant" class="inline-flex items-center gap-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl px-4 py-2 transition">
                    <i class="ti ti-plus"></i>
                    Tambah Variant
                </button>
            </div>

            <div id="variant-container" class="space-y-4">
                @foreach ($product->variants as $index => $variant)
                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">
                    <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm variant-row">
                        <div class="flex items-start justify-between gap-3">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 w-full">
                                @php $sizeAttribute = $variantAttributes->where('name', 'Size')->first(); @endphp
                                @if($sizeAttribute)
                                    <div>
                                        <label class="text-sm font-medium text-gray-600">{{ $sizeAttribute->name }}</label>
                                        <select name="variants[{{ $index }}][options][{{ $sizeAttribute->id }}]" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($sizeAttribute->options as $option)
                                                <option value="{{ $option->id }}" @if($variant->options->contains('id', $option->id)) selected @endif>{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label class="text-sm font-medium text-gray-600">Harga</label>
                                    <input type="number" name="variants[{{ $index }}][price]" value="{{ $variant->price }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="Rp">
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-600">Qty</label>
                                    <input type="number" name="variants[{{ $index }}][quantity]" value="{{ $variant->quantity }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="Qty">
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-600">HPP</label>
                                    <input type="number" step="0.01" name="variants[{{ $index }}][hpp]" value="{{ $variant->hpp }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="Rp">
                                </div>
                            </div>

                            <button type="button" class="text-red-500 hover:text-red-700 mt-1" onclick="this.closest('.variant-row').remove()" title="Hapus variant">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                <i class="ti ti-check"></i>
                Update Product
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let newVariantIndex = {{ count($product->variants) }};
    const attributes = @json($variantAttributes);
    const sizeAttribute = attributes.find(attr => attr.name === 'Size');

    document.getElementById('add-variant').addEventListener('click', () => {
        const container = document.getElementById('variant-container');

        const row = document.createElement('div');
        row.className = 'bg-white rounded-xl border border-gray-100 p-4 shadow-sm variant-row';

        const layout = document.createElement('div');
        layout.className = 'flex items-start justify-between gap-3';

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 w-full';

        if (sizeAttribute) {
            const col = document.createElement('div');
            col.innerHTML = `
                <label class="text-sm font-medium text-gray-600">${sizeAttribute.name}</label>
                <select name="variants[${newVariantIndex}][options][${sizeAttribute.id}]" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">-- Pilih --</option>
                    ${sizeAttribute.options.map(opt => `<option value="${opt.id}">${opt.name}</option>`).join('')}
                </select>
            `;
            grid.appendChild(col);
        }

        const fields = [
            { label: 'Harga', name: 'price', placeholder: 'Rp' },
            { label: 'Qty', name: 'quantity', placeholder: 'Qty' },
            { label: 'HPP', name: 'hpp', placeholder: 'Rp', step: '0.01' }
        ];

        fields.forEach(field => {
            const col = document.createElement('div');
            col.innerHTML = `
                <label class="text-sm font-medium text-gray-600">${field.label}</label>
                <input type="number" name="variants[${newVariantIndex}][${field.name}]" step="${field.step ?? 1}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="${field.placeholder}">
            `;
            grid.appendChild(col);
        });

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-red-500 hover:text-red-700 mt-1';
        removeBtn.innerHTML = '<i class="ti ti-trash"></i>';
        removeBtn.addEventListener('click', () => row.remove());

        layout.appendChild(grid);
        layout.appendChild(removeBtn);
        row.appendChild(layout);
        container.appendChild(row);

        newVariantIndex++;
    });
</script>
@endpush

@endsection

