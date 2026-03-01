@extends('handai-manager.layouts.master')

@section('title', 'Buat Stock Opname')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto"
     x-data="opnameForm()">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.stock-opname.index') }}"
           class="p-2 rounded-lg hover:bg-gray-100 transition text-gray-500">
            <i class="ti ti-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Buat Stock Opname</h1>
            <p class="text-sm text-gray-500">Bandingkan stok fisik dengan stok sistem</p>
        </div>
    </div>

    <form action="{{ route('manager.operational.stock-opname.store') }}" method="POST"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Opname <span class="text-red-500">*</span></label>
                <input type="date" name="adjustment_date" value="{{ date('Y-m-d') }}" required
                       class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- PIC --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">PIC</label>
                <select name="pic_id"
                        class="w-full h-10 px-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih PIC --</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Notes --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan opname..."
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Daftar Bahan — Masukkan stok aktual</h3>
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                            <th class="px-4 py-3">Bahan</th>
                            <th class="px-4 py-3">Satuan</th>
                            <th class="px-4 py-3 text-right">Stok Sistem</th>
                            <th class="px-4 py-3 text-right">Stok Aktual</th>
                            <th class="px-4 py-3">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($stocks as $i => $stock)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2">
                                <input type="hidden" name="items[{{ $i }}][stock_id]" value="{{ $stock->id }}">
                                <p class="font-medium text-gray-900 text-sm">{{ $stock->name }}</p>
                            </td>
                            <td class="px-4 py-2 text-gray-500 text-xs">{{ $stock->unit->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-right text-gray-600 font-mono text-xs">
                                {{ number_format($stock->stockBatches->where('store_id', session('selected_store'))->sum('unit_qty'), 2) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                <input type="number" name="items[{{ $i }}][actual_qty]"
                                       value="{{ $stock->stockBatches->where('store_id', session('selected_store'))->sum('unit_qty') }}"
                                       step="0.01" min="0"
                                       class="w-24 h-8 px-2 text-sm text-right border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" name="items[{{ $i }}][reason]" placeholder="Opsional"
                                       class="w-full h-8 px-2 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-amber-400">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.stock-opname.index') }}"
               class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition">Batal</a>
            <button type="submit"
                    class="px-6 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-medium hover:bg-amber-700 transition shadow-sm">
                <i class="ti ti-check"></i> Simpan Opname
            </button>
        </div>
    </form>
</div>

<script>
function opnameForm() {
    return {};
}
</script>
@endsection
