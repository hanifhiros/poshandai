<?php
namespace App\Http\Controllers\Manager\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Store, Order, Invoice};
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;

        $orders = Order::with('customer')
            ->when($request->filled('search'), fn($q) => $q->where('id', 'like', '%' . $request->search . '%'))->where('store_id', $selected_store_id)
            ->when($request->filled('start') && $request->filled('end'), fn($q) => $q->whereBetween('created_at', [$request->start, $request->end]))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('handai-manager.finance.invoice.index', compact('orders', 'selected_store'));
    }

    public function destroy($id)
    {
        Invoice::where('order_id', $id)->delete();
        Order::where('id', $id)->delete();

        return redirect()->route('manager.finance.invoices.index')->with('success', 'Invoice berhasil dihapus.');
    }

    public function print($id)
    {
        $order = Order::with('customer')->findOrFail($id);
        $items = Invoice::with(['product', 'variant.options.attribute'])
            ->where('order_id', $id)
            ->get()
            ->map(function ($item) {
                $summary = $item->variant?->options->map(fn($opt) => $opt->attribute->name . ': ' . $opt->name)->implode(', ') ?? 'Tanpa Varian';
                return [
                    'product_name' => $item->product->name ?? '-',
                    'variant_summary' => $summary,
                    'variant_price' => $item->price,
                    'discount' => $item->discount ?? 0,
                    'is_promo' => $item->is_promo ?? 'no',
                    'quantity_bought' => $item->quantity_bought,
                ];
            });

        $totals = $this->calculateTotals($items);
        return view('handai-manager.finance.invoice.print', compact('order', 'items', 'totals'));
    }

    public function pdf($id)
    {
        $logo = base64_encode(file_get_contents(public_path('assets/logo.png')));
        $order = Order::with('customer')->findOrFail($id);
        $items = Invoice::with(['product', 'variant.options.attribute'])
            ->where('order_id', $id)
            ->get()
            ->map(function ($item) {
                $summary = $item->variant?->options->map(fn($opt) => $opt->attribute->name . ': ' . $opt->name)->implode(', ') ?? 'Tanpa Varian';
                return [
                    'product_name' => $item->product->name ?? '-',
                    'variant_summary' => $summary,
                    'variant_price' => $item->price,
                    'discount' => $item->discount ?? 0,
                    'is_promo' => $item->is_promo ?? 'no',
                    'quantity_bought' => $item->quantity_bought,
                ];
            });

        $totals = $this->calculateTotals($items);

        $pdf = Pdf::loadView('handai-manager.finance.invoice.pdf', compact('order', 'items', 'totals', 'logo'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("invoice-{$order->id}.pdf");
    }

    public function show($id)
    {
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;

        $order = Order::with('customer')->findOrFail($id);

        $items = Invoice::with(['product', 'variant.options.attribute'])
            ->where('order_id', $id)
            ->get()
            ->map(function ($item) {
                $summary = $item->variant?->options->map(fn($opt) => $opt->attribute->name . ': ' . $opt->name)->implode(', ') ?? 'Tanpa Varian';
                return [
                    'product_name' => $item->product->name ?? '-',
                    'variant_summary' => $summary,
                    'variant_price' => $item->price,
                    'discount' => $item->discount ?? 0,
                    'is_promo' => $item->is_promo ?? 'no',
                    'quantity_bought' => $item->quantity_bought,
                ];
            });
            // dd($order);

        return view('handai-manager.finance.invoice.show', compact('order', 'items', 'selected_store'));
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