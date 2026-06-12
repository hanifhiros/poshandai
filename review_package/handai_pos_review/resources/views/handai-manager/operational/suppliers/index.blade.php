@extends('handai-manager.layouts.master')

@section('title', 'Daftar Supplier')

@section('content')
<style>
    [x-cloak] { display: none !important; }
    .sup-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .sup-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .sup-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto" x-data="{ showFilter: false }">

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-building-store text-green-600"></i> Daftar Supplier
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data supplier & vendor bahan baku</p>
        </div>
        <div class="flex items-center gap-2">
            @include('handai-manager.partials.import-export-modal', ['type' => 'supplier', 'label' => 'Supplier'])
            <a href="{{ route('manager.operational.suppliers.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition shadow-sm">
                <i class="ti ti-plus text-base"></i> Tambah Supplier
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Supplier</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalSuppliers) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Aktif</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($activeSuppliers) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Pembelian</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Hutang Outstanding</p>
            <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($outstandingDebt, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari supplier..."
                           class="sup-input">
                </div>
                <select name="status" class="sup-input sm:w-40">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                    <i class="ti ti-search"></i> Cari
                </button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Kontak</th>
                        <th class="px-4 py-3">Kota</th>
                        <th class="px-4 py-3">Terms</th>
                        <th class="px-4 py-3 text-center">Pembelian</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $sup)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $sup->name }}</p>
                            @if($sup->contact_person)
                            <p class="text-xs text-gray-500">{{ $sup->contact_person }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($sup->phone) <span class="text-xs">{{ $sup->phone }}</span><br> @endif
                            @if($sup->email) <span class="text-xs text-gray-400">{{ $sup->email }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $sup->city ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="sup-badge bg-blue-50 text-blue-700">{{ $sup->payment_terms }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $sup->stock_batches_count }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($sup->is_active)
                                <span class="sup-badge bg-emerald-50 text-emerald-700">Aktif</span>
                            @else
                                <span class="sup-badge bg-gray-100 text-gray-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('manager.operational.suppliers.edit', $sup) }}"
                                   class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-blue-600 transition">
                                    <i class="ti ti-edit text-base"></i>
                                </a>
                                <form action="{{ route('manager.operational.suppliers.destroy', $sup) }}" method="POST"
                                      onsubmit="return confirm('Hapus supplier ini?')">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-red-600 transition">
                                        <i class="ti ti-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-building-store text-4xl block mb-2"></i>
                            Belum ada supplier. Tambahkan supplier pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
