@extends('layouts.master')

@section('title', 'Tambah Produk Setengah Jadi')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
    /* only keep cloak style, inputs styled inline */
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[900px] mx-auto" x-data="semiFinishedCreate()" x-cloak>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Tambah Produk Setengah Jadi</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Definisikan produk setengah jadi beserta resep bahan dan biaya prosesnya</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <ul class="text-[12px] text-red-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li class="flex items-start gap-1.5">
                        <svg class="w-3 h-3 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manager.inventory.semi-finished.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf

        {{-- Step 1: Basic Info --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Produk Setengah Jadi</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" placeholder="Contoh: Kurma Potong, Creamer Cair, Espresso" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Satuan Output <span class="text-red-500">*</span></label>
                    <select name="unit_id" x-model="form.unit_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" required>
                        <option value="">-- Pilih Satuan --</option>
                        @foreach($units->sortBy('symbol') as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->symbol }} ({{ $unit->name }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Satuan yang digunakan untuk mengukur hasil produk ini (contoh: g, ml, pcs)</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Min. Stok Alert</label>
                    <input type="number" name="min_stock" x-model="form.min_stock" step="0.001" min="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" placeholder="0">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Masa Expired (hari)</label>
                    <input type="number" name="expired_duration" x-model="form.expired_duration" step="1" min="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" placeholder="Kosongkan jika tidak ada">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi / Catatan Proses</label>
                    <textarea name="description" x-model="form.description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none !h-auto !py-2" placeholder="Contoh: Kurma dibuang bijinya, dipotong kecil-kecil..."></textarea>
                </div>
            </div>
        </div>

        {{-- Resep & HPP tidak lagi diisi di sini, gunakan halaman Resep untuk mengatur komposisi dan harga pokok produksi. --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-300 p-4 mb-4">
            <p class="text-sm text-yellow-700">
                Untuk menentukan resep maupun menghitung HPP, silakan buka <a href="{{ route('manager.inventory.recipes.index') }}" class="underline font-semibold">halaman Resep</a>.
            </p>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}"
                class="h-10 px-5 inline-flex items-center text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition">
                Batal
            </a>
            <button type="submit"
                class="h-10 px-6 inline-flex items-center gap-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function semiFinishedCreate() {
    return {
        form: {
            name: '', unit_id: '', min_stock: 0,
            expired_duration: '', description: '',
        },
        submitForm() {
            // simple submit, no extra hidden inputs
            this.$el.closest('form').submit();
        }
    };
}
</script>
@endpush

