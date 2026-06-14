@extends('layouts.master')

@section('title', 'Inspeksi QC Baru')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[900px] mx-auto" x-data="inspectionForm()">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.qc.inspections.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Buat Inspeksi QC</h1>
    </div>

    <form method="POST" action="{{ route('manager.operational.qc.inspections.store') }}"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tipe Inspeksi <span class="text-red-400">*</span></label>
                <select name="inspection_type" required x-model="inspType"
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="production">Produksi</option>
                    <option value="incoming">Bahan Masuk</option>
                    <option value="outgoing">Produk Keluar</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Standar QC (opsional)</label>
                <select name="qc_standard_id" x-model="selectedStandard" @change="loadChecklist()"
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">â€” Tanpa standar â€”</option>
                    @foreach($standards as $std)
                    <option value="{{ $std->id }}" data-checklist="{{ json_encode($std->checklist_items) }}">{{ $std->name }} ({{ \App\Models\QcStandard::CATEGORIES[$std->category] ?? $std->category }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Nama Item <span class="text-red-400">*</span></label>
            <input type="text" name="item_name" value="{{ old('item_name') }}" required
                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500"
                   placeholder="cth: Roti Tawar Batch #123, Tepung Terigu PO-xxx">
            @error('item_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Qty Diperiksa <span class="text-red-400">*</span></label>
                <input type="number" name="quantity_inspected" value="{{ old('quantity_inspected') }}" min="0.001" step="0.001" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Qty Pass <span class="text-red-400">*</span></label>
                <input type="number" name="quantity_passed" value="{{ old('quantity_passed') }}" min="0" step="0.001" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Qty Fail <span class="text-red-400">*</span></label>
                <input type="number" name="quantity_failed" value="{{ old('quantity_failed', 0) }}" min="0" step="0.001" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tanggal <span class="text-red-400">*</span></label>
                <input type="date" name="inspection_date" value="{{ old('inspection_date', now()->format('Y-m-d')) }}" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Inspektor</label>
                <select name="inspector_id"
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">Pilih...</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Checklist --}}
        <div x-show="checklist.length > 0" x-cloak>
            <label class="text-xs text-gray-500 block mb-2">Checklist</label>
            <div class="space-y-2 bg-gray-50 rounded-lg p-3">
                <template x-for="(item, idx) in checklist" :key="idx">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" :name="`checklist_results[${idx}][passed]`" value="1"
                               class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                        <span x-text="item"></span>
                        <input type="hidden" :name="`checklist_results[${idx}][item]`" :value="item">
                    </label>
                </template>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Catatan</label>
            <textarea name="notes" rows="2"
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.qc.inspections.index') }}"
               class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-cyan-600 text-white rounded-xl text-sm font-medium hover:bg-cyan-700 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Simpan Inspeksi
            </button>
        </div>
    </form>
</div>

<script>
function inspectionForm() {
    const standardData = {};
    @foreach($standards as $std)
    standardData['{{ $std->id }}'] = @json($std->checklist_items);
    @endforeach

    return {
        inspType: 'production',
        selectedStandard: '',
        checklist: [],
        loadChecklist() {
            this.checklist = this.selectedStandard ? (standardData[this.selectedStandard] || []) : [];
        }
    };
}
</script>
@endsection

