@extends('layouts.master')

@section('title', 'Daftar Purchase Order')

@section('content')
<style>
    .po-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 500; line-height: 1.4; }
    .po-input { width: 100%; height: 36px; padding: 0 12px; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; outline: none; transition: all 0.15s; }
    .po-input:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.08); }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1360px] mx-auto">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-[13px] flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="ti ti-file-invoice text-green-600"></i> Daftar Purchase Order (PO)
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola pemesanan barang ke supplier & penerimaan batch stok</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.operational.po.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition shadow-sm">
                <i class="ti ti-plus text-base"></i> Buat PO Baru
            </a>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 flex gap-2">
                    <select name="status" class="po-input sm:w-48">
                        <option value="">Semua Status PO</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Draft)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received (Selesai)</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        <i class="ti ti-search"></i> Filter
                    </button>
                    @if(request('status'))
                        <a href="{{ route('manager.operational.po.index') }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">No. PO</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Tanggal Dibuat</th>
                        <th class="px-4 py-3">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-right">Total Amount</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            {{ $po->po_number }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $po->supplier->name }}</p>
                            @if($po->notes)
                            <p class="text-xs text-gray-400 truncate max-w-[200px]">{{ $po->notes }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $po->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $po->creator->name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                            Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($po->status === 'pending')
                                <span class="po-badge bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                            @elseif($po->status === 'approved')
                                <span class="po-badge bg-blue-50 text-blue-700 border border-blue-200">Approved</span>
                            @elseif($po->status === 'received')
                                <span class="po-badge bg-emerald-50 text-emerald-700 border border-emerald-200">Received</span>
                            @elseif($po->status === 'cancelled')
                                <span class="po-badge bg-rose-50 text-rose-700 border border-rose-200">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('manager.operational.po.show', $po->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition">
                                <i class="ti ti-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="ti ti-file-invoice text-4xl block mb-2 text-gray-300"></i>
                            Belum ada data Purchase Order.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $purchaseOrders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
