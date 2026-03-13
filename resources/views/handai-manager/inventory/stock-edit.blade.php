@extends('handai-manager.layouts.master')

@section('title', 'Edit Bahan — ' . $stock->name)

@section('content')
<div class="min-h-screen bg-slate-50/60 py-6 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="max-w-2xl mx-auto mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.inventory.stock') }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:border-slate-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Edit Bahan</h1>
                <p class="text-sm text-slate-500">Ubah informasi bahan baku <strong>{{ $stock->name }}</strong></p>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="max-w-2xl mx-auto mb-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="max-w-2xl mx-auto mb-4">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ route('manager.inventory.stock.update', $stock->id) }}"
          class="max-w-2xl mx-auto">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Informasi Bahan
                </h2>
            </div>
            <div class="p-6 space-y-5">
                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-xs font-medium text-slate-600 mb-1.5">
                        Nama Bahan <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $stock->name) }}"
                           required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Satuan --}}
                    <div>
                        <label for="unit_id" class="block text-xs font-medium text-slate-600 mb-1.5">
                            Satuan Dasar <span class="text-red-400">*</span>
                        </label>
                        <select id="unit_id" name="unit_id" required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                            <option value="">Pilih satuan...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id', $stock->unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->symbol }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Satuan default untuk stok ini (kg, liter, pcs, dll)</p>
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label for="stock_category_id" class="block text-xs font-medium text-slate-600 mb-1.5">
                            Kategori Stok <span class="text-red-400">*</span>
                        </label>
                        <select id="stock_category_id" name="stock_category_id" required
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                            <option value="">Pilih kategori...</option>
                            @foreach($stockCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('stock_category_id', $stock->stock_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->stock_category_name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Kelompok bahan: Bahan Baku, Kemasan, dll</p>
                    </div>
                </div>

                {{-- Expired Duration --}}
                <div>
                    <label for="expired_duration" class="block text-xs font-medium text-slate-600 mb-1.5">
                        Masa Expired (hari)
                    </label>
                    <input type="number" id="expired_duration" name="expired_duration"
                           value="{{ old('expired_duration', $stock->expired_duration) }}"
                           min="0" placeholder="30"
                           class="w-full sm:w-48 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                    <p class="text-[11px] text-slate-400 mt-1">Berapa hari bahan ini bisa disimpan sebelum kadaluarsa</p>
                </div>

                {{-- ── ERP Settings ── --}}
                <div class="pt-5 border-t border-slate-100">
                    <h3 class="text-xs font-medium text-slate-500 mb-4 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Pengaturan Inventory
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Min Stock --}}
                        <div>
                            <label for="min_stock" class="block text-xs font-medium text-slate-600 mb-1.5">
                                Stok Minimum
                            </label>
                            <input type="number" id="min_stock" name="min_stock"
                                   value="{{ old('min_stock', $stock->min_stock ?? 0) }}"
                                   min="0" step="0.001" placeholder="0"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                            <p class="text-[11px] text-slate-400 mt-1">Batas bawah sebelum dianggap "Low Stock"</p>
                        </div>

                        {{-- Reorder Point --}}
                        <div>
                            <label for="reorder_point" class="block text-xs font-medium text-slate-600 mb-1.5">
                                Reorder Point
                            </label>
                            <input type="number" id="reorder_point" name="reorder_point"
                                   value="{{ old('reorder_point', $stock->reorder_point ?? 0) }}"
                                   min="0" step="0.001" placeholder="0"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                            <p class="text-[11px] text-slate-400 mt-1">Titik dimana bahan perlu di-restock/pesan ulang</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5">
                        {{-- Default Supplier --}}
                        <div>
                            <label for="default_supplier_id" class="block text-xs font-medium text-slate-600 mb-1.5">
                                Supplier Default
                            </label>
                            <select id="default_supplier_id" name="default_supplier_id"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition">
                                <option value="">— Tidak ada —</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('default_supplier_id', $stock->default_supplier_id) == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Supplier utama untuk bahan ini</p>
                        </div>

                        {{-- Active Toggle --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Status Aktif</label>
                            <div class="flex items-center gap-3 mt-2" x-data="{ active: {{ old('is_active', $stock->is_active ?? true) ? 'true' : 'false' }} }">
                                <button type="button" @click="active = !active"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="active ? 'bg-emerald-500' : 'bg-slate-200'"
                                        role="switch" :aria-checked="active">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                          :class="active ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                                <span class="text-sm" :class="active ? 'text-emerald-600 font-medium' : 'text-slate-400'" x-text="active ? 'Aktif' : 'Nonaktif'"></span>
                                <input type="hidden" name="is_active" :value="active ? '1' : '0'">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5">Nonaktifkan untuk menyembunyikan dari daftar stok utama</p>
                        </div>
                    </div>
                </div>

                {{-- Read-only info --}}
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-xs font-medium text-slate-500 mb-3 uppercase tracking-wider">Info Stok Saat Ini</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[11px] text-slate-400 mb-0.5">Stok</p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ number_format($stock->unit_qty, $stock->unit_qty == intval($stock->unit_qty) ? 0 : 2) }}
                                {{ $stock->unit->symbol ?? '' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[11px] text-slate-400 mb-0.5">HPP/Unit</p>
                            <p class="text-sm font-semibold text-slate-700">
                                Rp{{ number_format($stock->price_per_unit ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[11px] text-slate-400 mb-0.5">Nilai Inv.</p>
                            <p class="text-sm font-semibold text-slate-700">
                                Rp{{ number_format(($stock->unit_qty ?? 0) * ($stock->price_per_unit ?? 0), 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[11px] text-slate-400 mb-0.5">Total Batch</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $stock->batches()->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="mt-5 flex items-center justify-between">
            <a href="{{ route('manager.inventory.stock') }}"
               class="text-sm text-slate-500 hover:text-slate-700 hover:bg-gray-100 transition">← Batal</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
