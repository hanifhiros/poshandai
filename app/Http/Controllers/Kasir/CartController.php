<?php

namespace App\Http\Controllers\Kasir;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\ProductVariants;
use App\Models\ProductionHistory;
use App\Models\Promo;
use Midtrans\Config;
use App\Models\Customer;
use Midtrans\Snap;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\AccountingService;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $cartTotalItems = array_sum(array_column($cart, 'quantity'));

        $subTotal = 0;
        $cartTotalPrice = 0;
        $discountTotal = 0;

        foreach ($cart as $item) {
            $promo = $item['price'] ?? 0;
            $normal = $item['normal_price'] ?? $promo;
            $qty = $item['quantity'] ?? 0;

            $subTotal += $normal * $qty;
            $cartTotalPrice += $promo * $qty;
            $discountTotal += ($normal - $promo) * $qty;
        }

        $ppn = $cartTotalPrice * 0.0;
        $grandTotal = $cartTotalPrice + $ppn;

        $customers = Customer::where('store_id', session('selected_store'))->orderBy('name')->get();
        return view('handai-kasir.checkout.checkout-kasir', compact(
            'cart', 'cartTotalItems', 'subTotal', 'discountTotal', 'cartTotalPrice', 'ppn', 'grandTotal', 'customers'
        ));
    }

    public function getPromos()
    {
        $promos = Promo::where('is_active', 'Ya')->get();
        return response()->json([
            'success' => true,
            'promos' => $promos
        ]);
    }

    public function checkoutWithCustomer(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong.');
        }
    
        $request->validate([
            'payment_method' => 'required|in:cash,transfer,qris,tunai,non_tunai,campuran',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'additional_charges.pajak' => 'nullable|integer|min:0',
            'additional_charges.ongkos_kirim' => 'nullable|integer|min:0',
            'additional_charges.kemasan' => 'nullable|integer|min:0',
        ]);
    
        $customerId = $request->input('customer_id');
        if ($customerId === 'new' || !$customerId) {
            $validated = $request->validate([
                'new_name' => 'required|string|max:255',
                'new_contact' => 'required|string|max:20',
                'new_gender' => 'required|in:Laki-laki,Perempuan',
                'new_email' => 'nullable|email|max:255',
                'new_address'=>'required|string|max:255',
            ]);
    
            $customer = Customer::create([
                'name' => $validated['new_name'],
                'contact_number' => $validated['new_contact'],
                'gender' => $validated['new_gender'],
                'email' => $validated['new_email'] ?? null,
                'address' => $request->input('new_address'), // tambahkan baris ini
                'store_id'=>session('selected_store'),
            ]);
            $customerId = $customer->id;
        }
    
        $totals = $this->calculateCartTotals();
        $promoCode = session('promo_code');
        $promoDiscount = session('promo_discount') ?? 0;
        
        // Get additional charges
        $additionalCharges = $request->input('additional_charges', []);
        $pajak = (int)($additionalCharges['pajak'] ?? 0);
        $ongkosKirim = (int)($additionalCharges['ongkos_kirim'] ?? 0);
        $kemasan = (int)($additionalCharges['kemasan'] ?? 0);
        $totalAdditionalCharges = $pajak + $ongkosKirim + $kemasan;
        
        // Get metode pemesanan
        $metodePemesanan = $request->input('metode-pemesanan', 'bayar-langsung');
        
        $grossAmount = max($totals['grandTotal'] - $promoDiscount + $totalAdditionalCharges, 0);
        $orderedQty = 0;

        // ── Stock Validation (deduction happens on ship) ──
        $stockErrors = InventoryService::validateCartStock($cart);
        if (!empty($stockErrors)) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $stockErrors)
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $customerId,
                'total_item_price' => $totals['cartTotalPrice'],
                'PROMO_ID' => $promoCode ? Promo::where('Promo_Code', $promoCode)->value('id') : null,
                'order_status' => 'belum terkirim',
                'note' => $request->note, 
                'description' => 'Order via kasir - Metode: ' . $metodePemesanan,
                'gross_amount' => $grossAmount,
                'payment_type' => $request->payment_method,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'store_id'=>session('selected_store'),
                'pajak' => $pajak,
                'ongkos_kirim' => $ongkosKirim,
                'kemasan' => $kemasan,
            ]);

            $totalHpp = 0;

            // Batch load all variants for HPP calculation
            $cartVariantIds = array_column($cart, 'variant_id');
            $cartVariants = ProductVariants::whereIn('id', $cartVariantIds)->get()->keyBy('id');

            foreach ($cart as $item) {
                $price = $item['price'] ?? 0;
                $quantity = $item['quantity'] ?? 0;
                $totalPrice = $price * $quantity;

                $variant = $cartVariants->get($item['variant_id']);
                $variantHpp = $variant ? $variant->hpp : 0;
                $totalHpp += $variantHpp * $quantity;

                DB::table('invoice')->insert([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity_bought' => $item['quantity'],
                    'price' => $price,
                    'total_price' => $totalPrice,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $orderedQty += $item['quantity'];
            }
            $order->update([
                'total_hpp_orders' => $totalHpp
            ]);
            
            // Update customer stats inside transaction
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $totalQty = $customer->qty_ordered ?? 0;
                $newQtyTotal = $totalQty + $orderedQty;
            
                $totalOrders = Order::where('customer_id', $customer->id)->count();
            
                $customer->qty_ordered = $newQtyTotal;
                $customer->qty_ordered_avg = $totalOrders > 0 ? round($newQtyTotal / $totalOrders) : $newQtyTotal;
                $customer->has_ordered = 1;
                $customer->save();
            }

            DB::commit();
            $cart_items=session('cart');
            session()->forget(['cart', 'promo_code', 'promo_discount']);
    
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'gross_amount' => $order->gross_amount,
                'cart_items' => $cart_items, // <-- tambahkan ini
            ]);
            
            
        } catch (\Exception $e) {
            Log::error('Kasir checkout error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan order. Silakan coba lagi.'
            ], 500);
            
        }
    }
    


    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);
    
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $quantity = $request->quantity;
    
        $variant = ProductVariants::with(['product', 'options.attribute'])->findOrFail($variantId);
    
        $finalPrice = ($variant->is_promo === 'yes')
            ? ($variant->price - $variant->price_discount)
            : $variant->price;
    
        $cart = session('cart', []);
        
        $found = false;
        foreach ($cart as $index => $item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                $cart[$index]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
    
        if (!$found) {
            $cart[] = [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_summary' => $variant->variantSummary(), // ✅ penting untuk tampilan
                'quantity' => $quantity,
                'price' => $finalPrice,
                'normal_price' => $variant->price
            ];
            session(['cart' => $cart]);
            
        }
    
        session(['cart' => $cart]);
        
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke cart!');
    }


    

    public function updateCartQuantity(Request $request)
{
    try {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $data['product_id'];
        $variantId = $data['variant_id'];
        $newQty = $data['quantity'];

        $size = ProductVariants::find($variantId);
        if (!$size) {
            return response()->json(['error' => 'Ukuran produk tidak ditemukan.'], 404);
        }

        $producedQty = ProductVariants::where('product_id', $productId)
            ->where('id', $variantId)
            ->sum('quantity');

        if ($newQty > $producedQty) {
            return response()->json(['error' => 'Stok tidak mencukupi.'], 400);
        }

        $finalPrice = ($size->is_promo === 'yes')
            ? ($size->price - $size->price_discount)
            : $size->price;

        $cart = session('cart', []);
        $found = false;

        foreach ($cart as $index => $item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                $cart[$index]['quantity'] = $newQty;
                $cart[$index]['price'] = $finalPrice;

                if (!isset($cart[$index]['normal_price'])) {
                    $cart[$index]['normal_price'] = $size->price;
                }

                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json(['error' => 'Item tidak ditemukan di cart.'], 404);
        }

        session(['cart' => $cart]);

        $itemTotal = $newQty * $finalPrice;

        $cartTotalItems = array_sum(array_column($cart, 'quantity'));
        $cartTotalPrice = 0;
        $subTotal = 0;
        $discountTotal = 0;

        foreach ($cart as $itm) {
            $promo = $itm['price'] ?? 0;
            $normal = $itm['normal_price'] ?? $promo;
            $qty = $itm['quantity'] ?? 0;

            $subTotal += $normal * $qty;
            $cartTotalPrice += $promo * $qty;
            $discountTotal += ($normal - $promo) * $qty;
        }

        $promoDiscount = 0;
        $promoCode = session('promo_code');

        if ($promoCode) {
            $promo = Promo::where('Promo_Code', $promoCode)
                ->where('is_active', 'Ya')
                ->first();

            if ($promo) {
                $calculated = $cartTotalPrice * ($promo->discount_rate / 100);
                $promoDiscount = min($calculated, $promo->max_discount_price);
            }
        }

        $ppn = $cartTotalPrice * 0.0;
        $grandTotal = $cartTotalPrice + $ppn - $promoDiscount;

        return response()->json([
            'success' => true,
            'quantity' => $newQty,
            'itemTotal' => number_format($itemTotal, 0, ',', '.'),
            'cartTotalItems' => $cartTotalItems,
            'cartTotalPrice' => number_format($cartTotalPrice, 0, ',', '.'),
            'subTotal' => number_format($subTotal, 0, ',', '.'),
            'discountTotal' => number_format($discountTotal, 0, ',', '.'),
            'promo_discount' => number_format($promoDiscount, 0, ',', '.'),
            'promo_code' => $promoCode ?? '',
            'ppn' => number_format($ppn, 0, ',', '.'),
            'grandTotal' => number_format($grandTotal, 0, ',', '.'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat memperbarui keranjang.',
        ], 500);
    }
}
    public function removeItem(Request $request)
    {
    $productId = $request->input('product_id');
    $variantId = $request->input('variant_id');

    $cart = session('cart', []);

    // Filter dan buang item berdasarkan product + variant
    $cart = array_filter($cart, function ($item) use ($productId, $variantId) {
        return !($item['product_id'] == $productId && $item['variant_id'] == $variantId);
    });

    session(['cart' => array_values($cart)]); // reset ulang array index

    return response()->json([
        'success' => true,
        'message' => 'Item berhasil dihapus'
    ]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string'
        ]);

        $promoCode = $request->input('promo_code');

        $promo = Promo::where('Promo_Code', $promoCode)->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'error' => 'Promo code not found.'
            ], 404);
        }

        if ($promo->is_active !== 'Ya') {
            return response()->json([
                'success' => false,
                'error' => 'Promo is not active.'
            ], 400);
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'error' => 'Cart is empty.'
            ], 400);
        }

        $subTotal = 0;
        foreach ($cart as $item) {
            $price = $item['price'] ?? 0;
            $normal = $item['normal_price'] ?? $price;
            $qty = $item['quantity'] ?? 0;
            $subTotal += ($normal * $qty);
        }

        $calculatedDiscount = $subTotal * ($promo->discount_rate / 100);

        if ($calculatedDiscount > $promo->max_discount_price) {
            $calculatedDiscount = $promo->max_discount_price;
        }

        session([
            'promo_code' => $promo->Promo_Code,
            'promo_discount' => $calculatedDiscount
        ]);

        $totals = $this->calculateCartTotals();

        $grandTotalAfterPromo = $totals['grandTotal'] - $calculatedDiscount;
        if ($grandTotalAfterPromo < 0) {
            $grandTotalAfterPromo = 0;
        }

        return response()->json([
            'success' => true,
            'promo_code' => $promo->Promo_Code,
            'promo_discount' => number_format($calculatedDiscount, 0, ',', '.'),
            'subTotal' => number_format($totals['subTotal'], 0, ',', '.'),
            'cartTotalPrice' => number_format($totals['cartTotalPrice'], 0, ',', '.'),
            'discountTotal' => number_format($totals['discountTotal'], 0, ',', '.'),
            'ppn' => number_format($totals['ppn'], 0, ',', '.'),
            'grandTotal' => number_format($totals['grandTotal'], 0, ',', '.'),
            'grandTotalAfterPromo' => number_format($grandTotalAfterPromo, 0, ',', '.'),
        ]);
    }

    private function calculateCartTotals()
    {
        
        $cart = session('cart', []);
        $subTotal = 0;
        $cartTotalPrice = 0;
        $discountTotal = 0;

        foreach ($cart as $itm) {
            $promo = $itm['price'] ?? 0;
            $normal = $itm['normal_price'] ?? $promo;
            $qty = $itm['quantity'] ?? 0;

            $subTotal += $normal * $qty;
            $cartTotalPrice += $promo * $qty;
            $discountTotal += ($normal - $promo) * $qty;
        }

        $ppn = $cartTotalPrice * 0.0;
        $grandTotal = $cartTotalPrice + $ppn;

        return [
            'subTotal' => $subTotal,
            'cartTotalPrice' => $cartTotalPrice,
            'discountTotal' => $discountTotal,
            'ppn' => $ppn,
            'grandTotal' => $grandTotal
        ];
    }
    public function removePromo(Request $request)
    {
        session()->forget(['promo_discount', 'promo_code']);

        $cart = session('cart', []);
        $subTotal = 0;
        $cartTotalPrice = 0;
        $discountTotal = 0;

        foreach ($cart as $item) {
            $promo = $item['price'] ?? 0;
            $normal = $item['normal_price'] ?? $promo;
            $qty = $item['quantity'] ?? 0;

            $subTotal += $normal * $qty;
            $cartTotalPrice += $promo * $qty;
            $discountTotal += ($normal - $promo) * $qty;
        }

        $ppn = $cartTotalPrice * 0.0;
        $grandTotal = $cartTotalPrice + $ppn;

        return response()->json([
            'success' => true,
            'promoRemoved' => true,
            'promoDiscount' => 0,
            'grandTotal' => number_format($grandTotal, 0, ',', '.'),
        ]);
    }

    private function calculateGrossAmount($totalItemPrice, $ppn, $discount)
    {   
        $gross = $totalItemPrice + $ppn - $discount;
        return $gross < 0 ? 0 : $gross;
    }


    public function clearCart()
    {
        session()->forget(['cart', 'promo_code', 'promo_discount']);
        return response()->json(['success' => true]);
    }

}
