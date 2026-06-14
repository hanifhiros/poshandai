@extends('layouts.master') 

@section('title', 'Detail Invoice')

@section('content')
<div class="container mx-auto px-4 sm:px-8 py-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Invoice #{{ $order->id }}</h2>
        <p class="text-gray-600">Tanggal: {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 mb-8">
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Informasi Pelanggan</h3>
            <div class="text-sm text-gray-800">
                <p><strong>Nama:</strong> {{ $order->customer->name ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $order->customer->address ?? '-' }}</p>
                <p><strong>Email:</strong> {{ $order->customer->email ?? '-' }}</p>
                <p><strong>No. HP:</strong> {{ $order->customer->contact_number ?? '-' }}</p>

            </div>
        </div>
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Status & Total</h3>
            <div class="text-sm text-gray-800">
                <p><strong>Status:</strong>
                    <span class="inline-block px-2 py-1 text-xs rounded {{ $order->is_ra === 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $order->is_ra === 0 ? 'Lunas' : 'Belum Lunas' }}
                    </span>
                </p>
                <p><strong>Total Bayar:</strong> Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2">Produk</th>
                    <th class="px-4 py-2 text-center">Jumlah</th>
                    <th class="px-4 py-2 text-right">Harga Satuan</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">
                            {{ $item['product_name'] }}<br>
                            <small class="text-gray-500">{{ $item['variant_summary'] }}</small>
                        </td>
                        <td class="px-4 py-2 text-center">{{ $item['quantity_bought'] }}</td>
                        <td class="px-4 py-2 text-right">Rp{{ number_format($item['variant_price'], 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">
                            @php
                                $final = ($item['is_promo'] === 'yes') ? ($item['variant_price'] - $item['discount']) : $item['variant_price'];
                                $total = $final * $item['quantity_bought'];
                            @endphp
                            Rp{{ number_format($total, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('manager.finance.invoice.print', $order->id) }}" target="_blank" class="btn btn-sm btn-primary">Cetak</a>
        <a href="{{ route('manager.finance.invoices.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
</div>
@endsection

