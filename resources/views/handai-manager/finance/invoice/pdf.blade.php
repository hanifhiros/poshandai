<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            color: #1f2937;
            margin: 40px auto;
            max-width: 800px;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            height: 70px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
        }

        .company-address {
            color: #4b5563;
            font-size: 14px;
        }

        .info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-top: 30px;
}

.info-box {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info .info-left,
.info .info-right {
    width: 48%;
}

.info .info-box > div {
    line-height: 1.5;
}



        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 12px;
            vertical-align: top;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        td.center {
            text-align: center;
        }

        td.right {
            text-align: right;
        }

        .totals {
            margin-top: 30px;
            width: 100%;
        }

        .totals table {
            width: 280px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 13px;
            color: #1f2937;
        }

        .totals td {
            border: 1px solid #d1d5db;
            padding: 10px;
        }

        .totals tr.total-row td {
            font-weight: bold;
            font-size: 14px;
            background-color: #f9fafb;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 14px;
            color: #374151;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
            font-style: italic;
            color: #374151;
        }

        .signature strong {
            display: block;
            margin-top: 5px;
            font-style: normal;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="data:image/png;base64,{{ $logo }}" class="logo" alt="Logo Handai Coffee">
    <div class="company-name">Handai Coffee</div>
    <div class="company-address">Jl. Contoh Alamat No.123, Bandung</div>
</div>

<div class="info">
    <div class="info-left info-box">
        <div><strong>Tujuan Tagihan:</strong></div>
        <div><strong>Nama:</strong> {{ $order->customer->name }}</div>
        <div><strong>Alamat:</strong> {{ $order->customer->address ?? '-' }}</div>
    </div>
    <div class="info-right info-box" style="text-align: right;">
        <div><strong>Info Invoice:</strong></div>
        <div><strong>Invoice #:</strong> {{ $order->id }}</div>
        <div><strong>Tanggal Keluar:</strong> {{ $order->created_at->format('d/m/Y') }}</div>
        <div><strong>Jatuh Tempo:</strong> {{ $order->created_at->addDays(7)->format('d/m/Y') }}</div>
    </div>
</div>



<table>
    <thead>
    <tr>
        <th>Deskripsi</th>
        <th class="center" style="width: 10%;">Jumlah</th>
        <th class="right" style="width: 20%;">Harga Satuan</th>
        <th class="right" style="width: 20%;">Total</th>
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
        <tr>
            <td>
                {{ $item['product_name'] }}<br>
                <small style="color:#6b7280;">Ukuran: {{ $item['variant_summary'] }}</small>
            </td>
            <td class="center">{{ $item['quantity_bought'] }}</td>
            <td class="right">Rp{{ number_format($finalPrice, 0, ',', '.') }}</td>
            <td class="right">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="totals">
    <table>
        <tr>
            <td>Subtotal:</td>
            <td style="text-align: right;">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>Total:</td>
            <td style="text-align: right;">Rp{{ number_format($order->gross_amount, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

<div class="footer">
    Terima kasih telah menggunakan produk/jasa kami!
</div>

<div class="signature">
    Hormat Kami,<br>
    <strong>Handai Coffee</strong>
</div>

</body>
</html>
