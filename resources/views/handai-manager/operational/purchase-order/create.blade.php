@extends('layouts.master')

@section('title', 'Buat Purchase Order')

@section('content')
<style>
    .po-input { width: 100%; height: 38px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .po-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
    .po-textarea { width: 100%; padding: 8px 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; min-height: 80px; }
    .po-textarea:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1200px] mx-auto" x-data="poForm()">

    {{-- Error Alerts --}}
    @if($errors->any())
    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-[13px]">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('manager.operational.po.index') }}" class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 transition">
            <i class="ti ti-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Buat Purchase Order Baru</h1>
            <p class="text-sm text-gray-500">Buat rancangan PO untuk diajukan dan dimasukkan ke stok setelah disetujui</p>
        </div>
    </div>

    <form action="{{ route('manager.operational.po.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left column: PO Details --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm space-y-4">
                    <h2 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Informasi PO</h2>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Supplier Pemasok <span class="text-red-500">*</span></label>
                        <select name="supplier_id" class="po-input" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Catatan / Keterangan</label>
                        <textarea name="notes" class="po-textarea" placeholder="Tulis catatan opsional mengenai PO ini...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Right column: Items list --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                    <h2 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2 mb-4">Item Pembelian</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                    <th class="pb-2 min-w-[200px]">Nama Stok Barang</th>
                                    <th class="pb-2 w-32">Satuan</th>
                                    <th class="pb-2 w-24">Jumlah</th>
                                    <th class="pb-2 w-36">Harga Satuan</th>
                                    <th class="pb-2 w-32 text-right">Subtotal</th>
                                    <th class="pb-2 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="align-middle">
                                        <td class="py-3 pr-2">
                                            <select :name="'items[' + index + '][stock_id]'" 
                                                    x-model="item.stock_id"
                                                    @change="onStockChange(index)"
                                                    class="po-input" required>
                                                <option value="">-- Pilih Barang --</option>
                                                <template x-for="stk in stocks" :key="stk.id">
                                                    <option :value="stk.id" x-text="stk.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-3 pr-2">
                                            <select :name="'items[' + index + '][unit_id]'" 
                                                    x-model="item.unit_id"
                                                    class="po-input" required>
                                                <option value="">-- Satuan --</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-3 pr-2">
                                            <input type="number" 
                                                   step="0.001" 
                                                   :name="'items[' + index + '][quantity]'" 
                                                   x-model.number="item.quantity"
                                                   class="po-input text-center" 
                                                   min="0.001" required>
                                        </td>
                                        <td class="py-3 pr-2">
                                            <div class="relative flex items-center">
                                                <span class="absolute left-3 text-gray-400 text-xs">Rp</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       :name="'items[' + index + '][unit_price]'" 
                                                       x-model.number="item.unit_price"
                                                       class="po-input pl-8" 
                                                       min="0.01" required>
                                            </div>
                                        </td>
                                        <td class="py-3 pr-2 text-right font-semibold text-gray-900" 
                                            x-text="formatRupiah(item.quantity * item.unit_price)">
                                        </td>
                                        <td class="py-3 text-center">
                                            <button type="button" 
                                                    @click="removeItem(index)" 
                                                    :disabled="items.length === 1"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 disabled:opacity-50 transition">
                                                <i class="ti ti-trash text-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-4 mt-2">
                        <button type="button" 
                                @click="addItem()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 border border-gray-200 text-gray-600 hover:text-gray-900 rounded-lg text-xs font-semibold hover:bg-gray-100 transition">
                            <i class="ti ti-plus"></i> Tambah Item
                        </button>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 uppercase tracking-wider block">Total Pembelian</span>
                            <span class="text-xl font-bold text-green-700 block" x-text="formatRupiah(grandTotal)"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('manager.operational.po.index') }}" 
                       class="px-4 py-2 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-semibold transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        Simpan Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('poForm', () => ({
            items: [
                { stock_id: '', unit_id: '', quantity: 1, unit_price: 0 }
            ],
            stocks: @json($stocks),
            onStockChange(index) {
                const stockId = this.items[index].stock_id;
                const selectedStock = this.stocks.find(s => s.id == stockId);
                if (selectedStock) {
                    this.items[index].unit_id = selectedStock.unit_id;
                }
            },
            addItem() {
                this.items.push({ stock_id: '', unit_id: '', quantity: 1, unit_price: 0 });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            get grandTotal() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)), 0);
            },
            formatRupiah(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
            }
        }));
    });
</script>
@endsection
