@extends('layouts.master')

@section('title', 'Produksi Setengah Jadi: ' . $sfp->name)

@push('styles')
<style>
    /* only cloak needed here */
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[900px] mx-auto" x-data="sfpProduce()" x-cloak>

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Produksi: {{ $sfp->name }}</h1>
            <p class="text-[12px] text-gray-400 mt-0.5">Catat produksi produk setengah jadi dan konsumsi bahan mentah</p>
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

    {{-- Product Info Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-[11px] text-gray-500 font-medium">Output per Batch</p>
                <p class="font-semibold text-gray-800">{{ number_format($sfp->output_qty, 1) }} {{ $sfp->unit?->symbol }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-medium">HPP per Unit</p>
                <p class="font-semibold text-gray-800">Rp {{ number_format($sfp->price_per_unit, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-medium">Default Upah/Batch</p>
                <p class="font-semibold text-gray-800">Rp {{ number_format($sfp->labor_cost, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-medium">Stok Saat Ini</p>
                <p class="font-semibold text-gray-800">{{ number_format($sfp->current_qty, 1) }} {{ $sfp->unit?->symbol }}</p>
            </div>
        </div>

        {{-- Recipe Materials --}}
        <div class="mt-4 pt-4 border-t border-amber-200">
            <p class="text-[11px] text-amber-600 font-medium mb-2">Resep per Batch:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($sfp->materials as $mat)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white border border-amber-100 text-xs text-gray-700">
                        <i class="ti ti-package text-amber-500 mr-1.5"></i>
                        {{ $mat->stock?->name ?? '?' }}:
                        <span class="font-mono font-semibold ml-1">{{ number_format($mat->quantity_required, 2) }}</span>
                        <span class="text-gray-400 ml-0.5">{{ $mat->unit?->symbol ?? '' }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <form action="{{ route('manager.inventory.semi-finished.produce.store', $sfp->id) }}" method="POST">
        @csrf

        {{-- Production Form --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-[11px] font-bold"><i class="ti ti-player-play text-xs"></i></div>
                <h2 class="text-lg font-semibold text-gray-800">Data Produksi</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Produksi <span class="text-red-500">*</span></label>
                    <input type="date" name="production_date" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">PIC / Pekerja <span class="text-red-500">*</span></label>
                    <select name="pic_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" required>
                        <option value="">-- Pilih PIC --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kelipatan Batch <span class="text-red-500">*</span></label>
                    <input type="number" name="batch_multiplier" x-model="multiplier" step="0.1" min="0.1" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" placeholder="1" required>
                    <p class="text-[10px] text-gray-400 mt-1">
                        Output: <span class="font-semibold" x-text="(parseFloat(multiplier) * {{ $sfp->output_qty }}).toFixed(1)"></span>
                        {{ $sfp->unit?->symbol }}
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Upah / Biaya Proses <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                        <input type="number" name="labor_cost" x-model="laborCost" step="1" min="0" class="w-full px-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none" required>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:outline-none !h-auto !py-2" placeholder="Catatan opsional..."></textarea>
                </div>
            </div>
        </div>

        {{-- Material Consumption Preview --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 mb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="ti ti-package text-gray-400"></i>
                Perkiraan Bahan yang Digunakan
            </h3>
            <div class="overflow-x-auto -mx-5 px-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">
                            <th class="text-left pb-2">Bahan</th>
                            <th class="text-right pb-2">Per Batch</th>
                            <th class="text-center pb-2">x</th>
                            <th class="text-right pb-2">Total Dipakai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($sfp->materials as $mat)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $mat->stock?->name ?? '?' }}</td>
                                <td class="py-2 text-right font-mono text-gray-500">{{ number_format($mat->quantity_required, 2) }} {{ $mat->unit?->symbol }}</td>
                                <td class="py-2 text-center text-gray-400" x-text="multiplier + 'x'"></td>
                                <td class="py-2 text-right font-mono font-semibold text-gray-700">
                                    <span x-text="(parseFloat(multiplier) * {{ $mat->quantity_required }}).toFixed(2)"></span>
                                    {{ $mat->unit?->symbol }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('manager.inventory.products', ['tab' => 'setengah_jadi']) }}"
                class="h-10 px-5 inline-flex items-center text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                class="h-10 px-6 inline-flex items-center gap-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <i class="ti ti-player-play"></i>
                Proses Produksi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function sfpProduce() {
    return {
        multiplier: 1,
        laborCost: {{ $sfp->labor_cost }},
    };
}
</script>
@endpush

