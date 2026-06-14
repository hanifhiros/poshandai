@extends('layouts.master')

@section('title', 'Buat Production Plan')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1100px] mx-auto" x-data="productionPlanForm()">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.production-plans.index') }}"
           class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Buat Production Plan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Rencana produksi & kalkulasi kebutuhan material (MRP)</p>
        </div>
    </div>

    <form method="POST" action="{{ route('manager.operational.production-plans.store') }}"
          class="space-y-6">
        @csrf

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-bold text-gray-700">Detail Plan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-3">
                    <label class="text-xs text-gray-500 block mb-1">Nama Plan <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="cth: Produksi Minggu 1 Maret 2026">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Tanggal Mulai <span class="text-red-400">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Tanggal Selesai <span class="text-red-400">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date', now()->addDays(7)->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Opsional...">
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-700">Item Produksi</h2>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-100 transition">
                    <i class="ti ti-plus"></i> Tambah Item
                </button>
            </div>

            @error('items')<p class="text-xs text-red-500 mb-2">{{ $message }}</p>@enderror

            <div class="space-y-3">
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="flex flex-wrap gap-3 items-start p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="flex-1 min-w-[200px]">
                            <label class="text-xs text-gray-500 block mb-1">Produk</label>
                            <select :name="`items[${idx}][product_id]`" x-model="item.product_id" required
                                    @change="updateType(idx)"
                                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih produk...</option>
                                @foreach($products as $p)
                                <option value="{{ $p['id'] }}" data-type="{{ $p['type'] }}">
                                    {{ $p['type'] === 'semi_finished' ? '[SF] ' : '' }}{{ $p['name'] }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" :name="`items[${idx}][type]`" :value="item.type">
                        </div>
                        <div class="w-28">
                            <label class="text-xs text-gray-500 block mb-1">Jumlah</label>
                            <input type="number" :name="`items[${idx}][quantity]`" x-model="item.quantity"
                                   min="0.001" step="0.001" required
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-36">
                            <label class="text-xs text-gray-500 block mb-1">Target Tanggal</label>
                            <input type="date" :name="`items[${idx}][target_date]`" x-model="item.target_date" required
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="w-40">
                            <label class="text-xs text-gray-500 block mb-1">PIC</label>
                            <select :name="`items[${idx}][assigned_to]`" x-model="item.assigned_to"
                                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="removeItem(idx)" x-show="items.length > 1"
                                    class="p-1.5 hover:bg-red-50 rounded-lg transition">
                                <i class="ti ti-trash text-red-400"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('manager.operational.production-plans.index') }}"
               class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Simpan & Hitung MRP
            </button>
        </div>
    </form>
</div>

<script>
function productionPlanForm() {
    // Build product type map from server data
    const productTypeMap = {};
    @foreach($products as $p)
    productTypeMap['{{ $p["id"] }}'] = '{{ $p["type"] }}';
    @endforeach

    return {
        items: [{ product_id: '', type: 'variant', quantity: 1, target_date: '{{ now()->addDays(3)->format("Y-m-d") }}', assigned_to: '' }],
        addItem() {
            this.items.push({ product_id: '', type: 'variant', quantity: 1, target_date: '{{ now()->addDays(3)->format("Y-m-d") }}', assigned_to: '' });
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
        },
        updateType(idx) {
            const pid = this.items[idx].product_id;
            this.items[idx].type = productTypeMap[pid] || 'variant';
        }
    };
}
</script>
@endsection

