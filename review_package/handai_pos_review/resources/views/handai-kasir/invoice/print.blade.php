@extends('layouts.layoutBlank')

@section('title', 'Invoice Print')

@section('content')
<div class="min-h-screen flex justify-center items-center bg-gray-100 print:bg-white">
    
    
    <div class="w-full max-w-2xl bg-white shadow-md rounded-md p-8 text-sm font-sans leading-relaxed print:shadow-none print:p-0 print:border-0">
        <!-- Tombol Aksi -->
<!-- Tombol Aksi -->
<div class="flex justify-between mb-6 print:hidden">
    <a href="{{ route('kasir.invoice.pdf', $order->id) }}" target="_blank"
       class="px-5 py-2 rounded-md font-semibold bg-[#4CAF50] text-white hover:bg-[#43A047] shadow-md transition">
        Unduh PDF
    </a>
    <a href="{{ route('kasir.dashboard') }}"
       class="px-5 py-2 rounded-md font-semibold border border-[#4CAF50] text-[#4CAF50] hover:bg-[#E8F5E9] shadow-md transition">
        Buat Order Baru
    </a>
</div>


        <!-- Logo & Company Info -->
        <div class="text-center mb-6">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-16 mx-auto mb-2">
            <h1 class="text-2xl font-extrabold text-gray-800">Handai Coffee</h1>
            <p class="text-gray-600">Jl. Contoh Alamat No.123, Bandung</p>
        </div>

        <!-- Invoice Info -->
        <div class="grid grid-cols-2 gap-6 mb-6 text-gray-800">
            <div>
                <h2 class="font-semibold mb-1">Tujuan Tagihan:</h2>
                <p><strong>Nama:</strong> {{ $order->customer->name ?? '-' }}</p>
                <p><strong>Alamat:</strong> {{ $order->customer->address ?? '-' }}</p>
            </div>
            <div class="text-right">
                <h2 class="font-semibold mb-1">Info Invoice:</h2>
                <p><strong>Invoice #:</strong> {{ $order->id }}</p>
                <p><strong>Tanggal Keluar:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</p>
                <p><strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($order->created_at)->addDays(7)->format('d/m/Y') }}</p>
            </div>
        </div>

        <hr class="border-gray-300 mb-4">

        <!-- Item Table -->
        <table class="w-full text-sm border border-collapse mb-6">
            <thead class="bg-gray-100 text-gray-800 font-semibold">
                <tr>
                    <th class="border p-2 text-left">Deskripsi</th>
                    <th class="border p-2 text-center">Jumlah</th>
                    <th class="border p-2 text-right">Harga Satuan</th>
                    <th class="border p-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @php
                        $price = $item['variant_price'];
                        $discount = $item['discount'] ?? 0;
                        $isPromo = $item['is_promo'] === 'yes';
                        $finalPrice = $isPromo ? ($price - $discount) : $price;
                        $subtotal = $finalPrice * $item['quantity_bought'];
                    @endphp
                    <tr class="text-gray-700">
                        <td class="border p-2">
                            {{ $item['product_name'] }}<br>
                            <small class="text-gray-500">{{ $item['variant_summary'] ?? '' }}</small>
                        </td>
                        <td class="border p-2 text-center">{{ $item['quantity_bought'] }}</td>
                        <td class="border p-2 text-right">Rp{{ number_format($finalPrice, 0, ',', '.') }}</td>
                        <td class="border p-2 text-right">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end text-sm text-gray-800">
            <table class="w-1/2 text-right">
                <tr>
                    <td class="py-1">Subtotal:</td>
                    <td class="py-1">Rp{{ number_format($totals['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @if($totals['discount'] > 0)
                <tr>
                    <td class="py-1">Diskon:</td>
                    <td class="py-1">- Rp{{ number_format($totals['discount'], 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($totals['ppn'] > 0)
                <tr>
                    <td class="py-1">PPN ({{ $totals['ppn_percent'] }}%):</td>
                    <td class="py-1">Rp{{ number_format($totals['ppn'], 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="font-bold text-lg">
                    <td class="py-2">Total:</td>
                    <td class="py-2">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-600">
            <p class="font-semibold">Terima kasih telah menggunakan produk/jasa kami!</p>
            <p class="mt-4 italic text-right">Hormat Kami, <br><strong class="not-italic">Handai Coffee</strong></p>
        </div>

    </div>
</div>
@endsection
