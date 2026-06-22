@extends('layouts.master')

@section('title', 'Detail Purchase Order')

@section('content')
<style>
    .po-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; line-height: 1.4; }
</style>

<div class="py-5 px-4 sm:px-6 lg:px-8 max-w-[1000px] mx-auto">

    {{-- Success and Error Flash Messages --}}
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

    @if($errors->any())
    <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-[13px]">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.operational.po.index') }}" class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-gray-900 transition">
                <i class="ti ti-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    Detail Purchase Order: {{ $po->po_number }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">Dibuat pada {{ $po->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>
        <div>
            @if($po->status === 'pending')
                <span class="po-badge bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span> Pending Approval
                </span>
            @elseif($po->status === 'approved')
                <span class="po-badge bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span> Approved (Menunggu Penerimaan)
                </span>
            @elseif($po->status === 'received')
                <span class="po-badge bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Received (Barang Diterima)
                </span>
            @elseif($po->status === 'cancelled')
                <span class="po-badge bg-rose-50 text-rose-700 border border-rose-200">
                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> Cancelled
                </span>
            @endif
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pemasok / Supplier</h3>
            <div>
                <p class="font-bold text-gray-900">{{ $po->supplier->name }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $po->supplier->contact_person ?? 'Tidak ada kontak person' }}</p>
                @if($po->supplier->phone)
                    <p class="text-xs text-gray-500 mt-0.5"><i class="ti ti-phone text-xs"></i> {{ $po->supplier->phone }}</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pembuat PO</h3>
            <div>
                <p class="font-bold text-gray-900">{{ $po->creator->name ?? 'System / Anonymous' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $po->creator->email ?? '-' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Role: {{ $po->creator->role ?? 'Manager' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Catatan / Memo</h3>
            <p class="text-sm text-gray-700 italic">
                {{ $po->notes ?? 'Tidak ada catatan khusus.' }}
            </p>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900 text-sm">Item Pembelian</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3 w-10">No</th>
                        <th class="px-4 py-3">Nama Stok</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Harga Satuan</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($po->items as $index => $item)
                    <tr>
                        <td class="px-4 py-3 text-gray-500 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->stock->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->unit->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ number_format($item->quantity, 3, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50/50">
                        <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total Pembelian:</td>
                        <td class="px-4 py-3 text-right font-bold text-green-700 text-base">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Workflow Action Pane --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-semibold text-gray-900 text-sm">Alur Persetujuan PO</h3>
            <p class="text-xs text-gray-500 mt-0.5">Lakukan persetujuan, penerimaan stok batch, atau pembatalan transaksi</p>
        </div>
        <div class="flex items-center gap-2">
            @if($po->status === 'pending')
                {{-- Approve form --}}
                <form action="{{ route('manager.operational.po.approve', $po->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        <i class="ti ti-check"></i> Setujui PO
                    </button>
                </form>

                {{-- Cancel form --}}
                <form action="{{ route('manager.operational.po.cancel', $po->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan PO ini?')">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-sm font-semibold transition">
                        <i class="ti ti-x"></i> Batalkan PO
                    </button>
                </form>
            @elseif($po->status === 'approved')
                {{-- Receive form --}}
                <form action="{{ route('manager.operational.po.receive', $po->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        <i class="ti ti-package-import"></i> Terima Barang (Masuk Stok)
                    </button>
                </form>

                {{-- Cancel form --}}
                <form action="{{ route('manager.operational.po.cancel', $po->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan PO ini?')">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-sm font-semibold transition">
                        <i class="ti ti-x"></i> Batalkan PO
                    </button>
                </form>
            @else
                <span class="text-sm text-gray-400 italic">
                    <i class="ti ti-lock"></i> Transaksi telah diselesaikan / dibatalkan.
                </span>
            @endif
        </div>
    </div>
</div>
@endsection
