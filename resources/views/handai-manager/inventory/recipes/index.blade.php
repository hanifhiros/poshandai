@extends('handai-manager.layouts.master')

@section('title', 'Daftar Resep (BOM)')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Daftar Resep (Bill of Materials)</h1>

    <div class="flex justify-end mb-4">
        <a href="{{ route('manager.inventory.recipes.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            + Tambah Resep
        </a>
    </div>

    @forelse ($groupedBoms as $productId => $sizes)
        <div class="bg-white shadow rounded mb-6 p-4">
            <h2 class="text-xl font-semibold mb-2">{{ $products[$productId] ?? 'Produk Tidak Diketahui' }}</h2>

            @foreach ($sizes as $sizeId => $boms)
                <div class="mb-3 border-t pt-2">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold">Varian: {{ $sizesInfo[$sizeId] ?? 'Varian Tidak Diketahui' }}</h3>

                        <div class="flex gap-2">
                            <a href="{{ route('manager.inventory.recipes.edit', ['variant' => $sizeId]) }}"
                               class="px-3 py-1 bg-blue-500 text-white text-xs font-medium rounded hover:bg-blue-600 transition">
                                Edit Resep
                            </a>
                            <form action="{{ route('manager.inventory.recipes.destroy', ['variant' => $sizeId]) }}"
                                  method="POST" onsubmit="return confirmDelete(this)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="px-3 py-1 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 transition">
                                    Hapus Resep
                                </button>
                            </form>
                        </div>
                        
                        
                    </div>

                    <ul class="list-disc ml-6">
                        @foreach ($boms as $bom)
                            <li>
                                {{ $bom->stock->name ?? '-' }} - 
                                {{ $bom->quantity_required }} 
                                {{ $bom->unit->symbol ?? '' }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-gray-500">Belum ada resep yang dibuat.</p>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(form) {
        const confirmed = confirm('Yakin ingin menghapus resep ini?');
        if (confirmed) {
            window.dispatchEvent(new Event('loading-start'));
        } else {
            setTimeout(() => {
                window.dispatchEvent(new Event('loading-end'));
            }, 500);
        }
        return confirmed;
    }
</script>
@endpush
