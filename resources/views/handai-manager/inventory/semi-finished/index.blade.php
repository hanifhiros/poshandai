@extends('handai-manager.layouts.master')

@section('title', 'Produk Setengah Jadi')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans" x-data="{ search: '' }">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-lg text-sm flex items-center gap-3 shadow-sm" x-data x-init="setTimeout(() => $el.remove(), 4000)">
        <div class="bg-emerald-100 rounded-full p-1">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-lg text-sm flex items-center gap-3 shadow-sm">
        <div class="bg-red-100 rounded-full p-1">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <span class="font-medium">{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-8">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-layers-intersect text-amber-600"></i>
                Produk Setengah Jadi
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola produk setengah jadi (semi-finished), resep & stok</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            {{-- Search --}}
            <div class="relative w-full md:w-2/5">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" x-model="search" placeholder="Cari produk setengah jadi..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 sm:text-sm transition-shadow shadow-sm">
            </div>

            <a href="{{ route('manager.inventory.semi-finished.production-history') }}"
                class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition shadow-sm whitespace-nowrap">
                <i class="ti ti-history"></i> Riwayat Produksi
            </a>

            <a href="{{ route('manager.inventory.semi-finished.create') }}"
                class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Baru
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Total Produk</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $semiFinishedProducts->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Stok Tersedia</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $semiFinishedProducts->where('current_qty', '>', 0)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Stok Habis</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $semiFinishedProducts->where('current_qty', '<=', 0)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[11px] text-gray-400 uppercase tracking-wider font-medium">Hampir Habis</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $semiFinishedProducts->filter(fn($s) => $s->min_stock > 0 && $s->current_qty > 0 && $s->current_qty <= $s->min_stock)->count() }}</p>
        </div>
    </div>

    {{-- Table --}}
    @if($semiFinishedProducts->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-8 my-8 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-4">
                <i class="ti ti-layers-intersect text-amber-400 text-2xl"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-700">Belum ada produk setengah jadi</h3>
            <p class="text-xs text-gray-400 mt-1">
                Untuk membuat produk setengah jadi, tekan tombol "Tambah Baru" di atas.
            </p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="text-left px-5 py-3">Nama</th>
                            <th class="text-left px-3 py-3">Satuan Output</th>
                            <th class="text-right px-3 py-3">Output/Batch</th>
                            <th class="text-right px-3 py-3">Upah/Batch</th>
                            <th class="text-right px-3 py-3">HPP/Unit</th>
                            <th class="text-right px-3 py-3">Stok Saat Ini</th>
                            <th class="text-center px-3 py-3">Status</th>
                            <th class="text-center px-3 py-3">Bahan</th>
                            <th class="text-center px-3 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($semiFinishedProducts as $sfp)
                        <tr class="hover:bg-gray-50/50 transition"
                            x-show="!search || '{{ strtolower($sfp->name) }}'.includes(search.toLowerCase())">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-gray-800">{{ $sfp->name }}</div>
                                @if($sfp->description)
                                    <p class="text-[11px] text-gray-400 mt-0.5 truncate max-w-[250px]">{{ $sfp->description }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-gray-600">{{ $sfp->unit?->symbol ?? '-' }}</td>
                            <td class="px-3 py-3.5 text-right font-mono text-gray-700">{{ number_format($sfp->output_qty, 1) }}</td>
                            <td class="px-3 py-3.5 text-right font-mono text-gray-700">Rp {{ number_format($sfp->labor_cost, 0, ',', '.') }}</td>
                            <td class="px-3 py-3.5 text-right font-mono font-semibold text-emerald-700">Rp {{ number_format($sfp->price_per_unit, 0, ',', '.') }}</td>
                            <td class="px-3 py-3.5 text-right font-mono text-gray-700">{{ number_format($sfp->current_qty, 1) }} {{ $sfp->unit?->symbol ?? '' }}</td>
                            <td class="px-3 py-3.5 text-center">
                                @php $status = $sfp->stock_status; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold
                                    {{ $status === 'Tersedia' ? 'bg-emerald-100 text-emerald-700' : ($status === 'Hampir Habis' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                    <i class="ti ti-package text-gray-400"></i>
                                    {{ $sfp->materials->count() }}
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Produce --}}
                                    <a href="{{ route('manager.inventory.semi-finished.produce', $sfp->id) }}"
                                        class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-amber-600 hover:bg-amber-50 transition" title="Produksi">
                                        <i class="ti ti-player-play text-base"></i>
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('manager.inventory.semi-finished.edit', $sfp->id) }}"
                                        class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                        <i class="ti ti-edit text-base"></i>
                                    </a>
                                    {{-- Delete --}}
                                    <form action="{{ route('manager.inventory.semi-finished.destroy', $sfp->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus produk setengah jadi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition" title="Hapus">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
