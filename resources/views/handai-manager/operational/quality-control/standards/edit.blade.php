@extends('layouts.master')

@section('title', 'Edit Standar QC')

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[800px] mx-auto" x-data="standardForm()">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.qc.standards.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Edit Standar QC</h1>
    </div>

    <form method="POST" action="{{ route('manager.operational.qc.standards.update', $standard) }}"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Nama Standar <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $standard->name) }}" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Kategori <span class="text-red-400">*</span></label>
                <select name="category" required
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                    @foreach(\App\Models\QcStandard::CATEGORIES as $k => $v)
                    <option value="{{ $k }}" {{ old('category', $standard->category) == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Deskripsi</label>
            <textarea name="description" rows="2"
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">{{ old('description', $standard->description) }}</textarea>
        </div>

        <div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $standard->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                <span class="text-sm text-gray-700">Aktif</span>
            </label>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-2">Item Checklist <span class="text-red-400">*</span></label>
            <div class="space-y-2">
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 w-6 text-right" x-text="idx + 1"></span>
                        <input type="text" :name="`checklist_items[${idx}]`" x-model="items[idx]" required
                               class="flex-1 rounded-lg border-gray-200 text-sm focus:ring-cyan-500 focus:border-cyan-500">
                        <button type="button" @click="removeItem(idx)" x-show="items.length > 1"
                                class="p-1.5 hover:bg-red-50 rounded-lg transition">
                            <i class="ti ti-x text-red-400"></i>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="addItem()"
                    class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-cyan-50 text-cyan-700 rounded-lg text-xs font-medium hover:bg-cyan-100 transition">
                <i class="ti ti-plus"></i> Tambah Item
            </button>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('manager.operational.qc.standards.index') }}"
               class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-cyan-600 text-white rounded-xl text-sm font-medium hover:bg-cyan-700 transition shadow-sm">
                <i class="ti ti-device-floppy"></i> Update
            </button>
        </div>
    </form>
</div>

<script>
function standardForm() {
    return {
        items: @json($standard->checklist_items ?? ['']),
        addItem() { this.items.push(''); },
        removeItem(idx) { this.items.splice(idx, 1); }
    };
}
</script>
@endsection

