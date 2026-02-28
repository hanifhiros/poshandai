<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function print($orderId)
    {
        $order = Order::with('customer')->findOrFail($orderId);

        $items = Invoice::with(['product', 'variant'])
            ->where('order_id', $orderId)
            ->get()
            ->map(function ($item) {
                return [
                    'invoice_id'      => $item->id,
                    'product_name'    => $item->product->name ?? '-',
                    'variant_price'   => $item->variant->price ?? 0,
                    'discount'        => $item->variant->price_discount ?? 0,
                    'is_promo'        => $item->variant->is_promo ?? 'no',
                    'quantity_bought' => $item->quantity_bought,
                    'variant_summary' => $item->variant?->variantSummary() ?? '-',
                ];
            });

        $subtotal = $items->sum(fn($i) => (($i['is_promo'] === 'yes') ? ($i['variant_price'] - $i['discount']) : $i['variant_price']) * $i['quantity_bought']);
        $discount = $items->sum(fn($i) => $i['is_promo'] === 'yes' ? $i['discount'] * $i['quantity_bought'] : 0);

        return view('handai-pos.invoice.print', [
            'order' => $order,
            'items' => $items,
            'totals' => [
                'subtotal'    => $subtotal,
                'discount'    => $discount,
                'ppn_percent' => 0,
                'ppn'         => 0,
            ],
        ]);
    }
}
