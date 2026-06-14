<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
class InvoiceController extends Controller
{
    public function show($orderId)
    {
        $order = Order::with('customer')->findOrFail($orderId);

      

        $items = Invoice::with(['product', 'variant'])
            ->where('order_id', $orderId)
            ->get()
            ->map(function ($item) {
                return [
                    'invoice_id'     => $item->id,
                    'product_name'   => $item->product->name ?? '-',
                    'variant_price'  => $item->variant->price ?? 0,
                    'discount'       => $item->variant->price_discount ?? 0,
                    'is_promo'       => $item->variant->is_promo ?? 'no',
                    'quantity_bought'=> $item->quantity_bought,
                    'variant_summary' => $item->variant?->variantSummary() ?? '-',

                ];
            });
            return view('handai-pos.invoice.print', [
                'order' => $order,
                'items' => $items,
                'totals' => [
                    'subtotal' => $items->sum(fn($i) => ($i['variant_price'] - ($i['is_promo'] === 'yes' ? $i['discount'] : 0)) * $i['quantity_bought']),
                    'discount' => $items->sum(fn($i) => $i['is_promo'] === 'yes' ? $i['discount'] * $i['quantity_bought'] : 0),
                    'ppn_percent' => 0,
                    'ppn' => 0,
                ]
            ]);
    

    }
   // Panggil facade PDF (otomatis tersedia setelah install dompdf)


    public function downloadPdf($id)
    {
        $logo = base64_encode(file_get_contents(public_path('assets/logo.png')));
        $order = Order::with('customer')->findOrFail($id);
        $items = Invoice::with(['product', 'variant.options.attribute'])
            ->where('order_id', $id)
            ->get()
            ->map(function ($item) {
                $summary = $item->variant?->options?->map(fn($opt) => ($opt->attribute?->name ?? '?') . ': ' . $opt->name)->implode(', ') ?? 'Tanpa Varian';
                return [
                    'product_name' => $item->product->name ?? '-',
                    'variant_summary' => $summary,
                    'variant_price' => $item->price,
                    'discount' => $item->variant?->price_discount ?? 0,
                    'is_promo' => $item->variant?->is_promo ?? 'no',
                    'quantity_bought' => $item->quantity_bought,
                ];
            });

        $totals = $this->calculateTotals($items);

        $pdf = Pdf::loadView('handai-manager.finance.invoice.pdf', compact('order', 'items', 'totals', 'logo'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("invoice-{$order->id}.pdf");
    }
    private function calculateTotals($items)
    {
        $subtotal = 0;
        $discount = 0;
        $ppnPercent = 11;

        foreach ($items as $item) {
            $price = $item['variant_price'];
            $disc = $item['discount'];
            $isPromo = $item['is_promo'] === 'yes';
            $finalPrice = $isPromo ? ($price - $disc) : $price;
            $subtotal += $finalPrice * $item['quantity_bought'];
            $discount += $isPromo ? $disc * $item['quantity_bought'] : 0;
        }

        $ppn = floor($subtotal * ($ppnPercent / 100));
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'ppn' => $ppn,
            'ppn_percent' => $ppnPercent
        ];
    }

}

