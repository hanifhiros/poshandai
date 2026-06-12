@extends('layouts.layoutBlank')

@section('title', 'POS - Cetak Struk')

@section('content')
<div class="min-h-screen flex justify-center items-center bg-gray-100 print:bg-white">
    <div class="w-full max-w-2xl bg-white shadow-md rounded-md p-8 text-sm font-sans leading-relaxed print:shadow-none print:p-0 print:border-0">

        {{-- Action Buttons --}}
        <div class="flex justify-between mb-6 print:hidden">
            <button onclick="window.print()"
                class="px-5 py-2 rounded-md font-semibold bg-[#0C9044] text-white hover:bg-[#0a7a3a] shadow-md transition cursor-pointer">
                <i class="ti ti-printer mr-1"></i> Print
            </button>
            <a href="{{ route('pos.dashboard') }}"
                class="px-5 py-2 rounded-md font-semibold border border-[#0C9044] text-[#0C9044] hover:bg-green-50 shadow-md transition">
                Kembali ke POS
            </a>
        </div>

        {{-- Logo & Company Info --}}
        <div class="text-center mb-6">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-16 mx-auto mb-2">
            <h1 class="text-2xl font-extrabold text-gray-800">Handai Coffee</h1>
            <p class="text-gray-600">Jl. Contoh Alamat No.123, Bandung</p>
        </div>

        {{-- Invoice Info --}}
        <div class="grid grid-cols-2 gap-6 mb-6 text-gray-800">
            <div>
                <h2 class="font-semibold mb-1">Pelanggan:</h2>
                <p><strong>Nama:</strong> {{ $order->customer->name ?? 'Walk-in' }}</p>
                @if($order->customer && $order->customer->address)
                    <p><strong>Alamat:</strong> {{ $order->customer->address }}</p>
                @endif
            </div>
            <div class="text-right">
                <h2 class="font-semibold mb-1">Info Struk:</h2>
                <p><strong>Order #:</strong> {{ $order->id }}</p>
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</p>
                @if($order->payment_type)
                    <p><strong>Pembayaran:</strong> {{ ucfirst($order->payment_type) }}</p>
                @endif
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
                            {{ $item['product_name'] }}
                            @if(!empty($item['variant_summary']) && $item['variant_summary'] !== '-')
                                <br><small class="text-gray-500">{{ $item['variant_summary'] }}</small>
                            @endif
                        </td>
                        <td class="border p-2 text-center">{{ $item['quantity_bought'] }}</td>
                        <td class="border p-2 text-right">
                            Rp{{ number_format($finalPrice, 0, ',', '.') }}
                            @if($isPromo)
                                <br><small class="text-red-400 line-through">Rp{{ number_format($price, 0, ',', '.') }}</small>
                            @endif
                        </td>
                        <td class="border p-2 text-right">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="flex justify-end text-sm text-gray-800">
            <table class="w-1/2 text-right">
                <tr>
                    <td class="py-1">Subtotal:</td>
                    <td class="py-1 pl-4">Rp{{ number_format($totals['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @if($totals['discount'] > 0)
                <tr>
                    <td class="py-1">Diskon:</td>
                    <td class="py-1 pl-4">- Rp{{ number_format($totals['discount'], 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($order->pajak ?? 0) > 0)
                <tr>
                    <td class="py-1">Pajak:</td>
                    <td class="py-1 pl-4">Rp{{ number_format($order->pajak, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($order->ongkos_kirim ?? 0) > 0)
                <tr>
                    <td class="py-1">Ongkos Kirim:</td>
                    <td class="py-1 pl-4">Rp{{ number_format($order->ongkos_kirim, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($order->kemasan ?? 0) > 0)
                <tr>
                    <td class="py-1">Kemasan:</td>
                    <td class="py-1 pl-4">Rp{{ number_format($order->kemasan, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($totals['ppn'] > 0)
                <tr>
                    <td class="py-1">PPN ({{ $totals['ppn_percent'] }}%):</td>
                    <td class="py-1 pl-4">Rp{{ number_format($totals['ppn'], 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="font-bold text-lg border-t border-gray-300">
                    <td class="py-2">Total:</td>
                    <td class="py-2 pl-4">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-center text-gray-600">
            <p class="font-semibold">Terima kasih atas kunjungan Anda!</p>
            <p class="text-xs text-gray-400 mt-2">Handai Coffee &mdash; Dicetak {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
@endsection
