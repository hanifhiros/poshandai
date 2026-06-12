@extends('handai-manager.layouts.master')

@section('title', 'Edit ' . $equipment->name)

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[900px] mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('manager.operational.maintenance.equipment.show', $equipment) }}"
           class="p-2 hover:bg-gray-100 rounded-lg transition">
            <i class="ti ti-arrow-left text-gray-500"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Peralatan</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $equipment->code }} &middot; {{ $equipment->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('manager.operational.maintenance.equipment.update', $equipment) }}"
          class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Nama Peralatan <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $equipment->name) }}" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Kode <span class="text-red-400">*</span></label>
                <input type="text" name="code" value="{{ old('code', $equipment->code) }}" required
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Kategori <span class="text-red-400">*</span></label>
                <select name="category" required
                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                    @foreach(\App\Models\Equipment::CATEGORIES as $k => $v)
                    <option value="{{ $k }}" {{ old('category', $equipment->category) == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                @error('category')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Brand</label>
                <input type="text" name="brand" value="{{ old('brand', $equipment->brand) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Model</label>
                <input type="text" name="model_number" value="{{ old('model_number', $equipment->model_number) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">No. Seri</label>
                <input type="text" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Lokasi</label>
                <input type="text" name="location" value="{{ old('location', $equipment->location) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tgl Pembelian</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', $equipment->purchase_date?->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Harga Beli (Rp)</label>
                <input type="number" name="purchase_cost" value="{{ old('purchase_cost', $equipment->purchase_cost) }}" step="0.01" min="0"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Garansi s/d</label>
                <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry', $equipment->warranty_expiry?->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Status</label>
            <select name="status"
                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">
                <option value="operational" {{ old('status', $equipment->status) == 'operational' ? 'selected' : '' }}>Operasional</option>
                <option value="under_maintenance" {{ old('status', $equipment->status) == 'under_maintenance' ? 'selected' : '' }}>Dalam Maintenance</option>
                <option value="broken" {{ old('status', $equipment->status) == 'broken' ? 'selected' : '' }}>Rusak</option>
                <option value="retired" {{ old('status', $equipment->status) == 'retired' ? 'selected' : '' }}>Retired</option>
            </select>
        </div>

        <div>
            <label class="text-xs text-gray-500 block mb-1">Catatan</label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-teal-500 focus:border-teal-500">{{ old('notes', $equipment->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <form method="POST" action="{{ route('manager.operational.maintenance.equipment.destroy', $equipment) }}"
                  onsubmit="return confirm('Hapus peralatan ini? Semua jadwal & log akan ikut terhapus.')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm transition">
                    <i class="ti ti-trash"></i> Hapus
                </button>
            </form>
            <div class="flex gap-3">
                <a href="{{ route('manager.operational.maintenance.equipment.show', $equipment) }}"
                   class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-medium hover:bg-teal-700 transition shadow-sm">
                    <i class="ti ti-device-floppy"></i> Update
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
