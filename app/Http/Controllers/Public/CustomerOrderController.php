<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerStore;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariants;
use App\Models\Promo;
use App\Models\ResellerStore;
use App\Models\Store;
use App\Services\InventoryService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    public function checkout(Request $request)
    {
        if (!session('customer_id') && !session('customer_guest')) {
            $storeId = $request->get('store_id') ?? session('selected_store');
            return redirect()->route('customerOrder.loginForm', ['store_id' => $storeId]);
        }

        $cart = session('cart', []);
        $cartTotalItems = array_sum(array_column($cart, 'quantity'));
        $totals = $this->cartService->calculateTotals($cart);
        $subTotal = $totals['subTotal'];
        $cartTotalPrice = $totals['cartTotalPrice'];
        $discountTotal = $totals['discountTotal'];
        $ppn = $totals['ppn'];
        $grandTotal = $totals['grandTotal'];

        return view('customer-order.checkout', compact(
            'cart',
            'grandTotal',
            'cartTotalItems',
            'subTotal',
            'discountTotal',
            'cartTotalPrice',
            'ppn'
        ));
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

    public function clearCart()
    {
        session()->forget(['cart', 'promo_code', 'promo_discount']);
        return response()->json(['success' => true]);
    }


    public function removePromo(Request $request)
    {
        session()->forget(['promo_discount', 'promo_code']);
        $cart = session('cart', []);
        $totals = $this->cartService->calculateTotals($cart);

        return response()->json([
            'success' => true,
            'promoRemoved' => true,
            'promoDiscount' => 0,
            'grandTotal' => number_format($totals['grandTotal'], 0, ',', '.'),
        ]);
    }


    public function checkoutWithCustomer(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $request->validate([
            'customer_id' => 'nullable|exists:customer,id', 
            'new_name' => 'required_if:customer_id,new|string|max:255',
            'payment_method' => 'required|in:cash,transfer,qris',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required'
        ]);

        $customerId = $request->input('customer_id');
        if ($customerId === 'new' || !$customerId) {
            $validated = $request->validate([
                'new_name' => 'required|string|max:255',
                'new_contact' => 'required|string|max:20',
                'new_gender' => 'required|in:Laki-laki,Perempuan',
                'new_email' => 'nullable|email|max:255',
                'new_address' => 'required|string|max:255',
            ]);

            $customer = Customer::create([
                'name' => $validated['new_name'],
                'contact_number' => $validated['new_contact'],
                'gender' => $validated['new_gender'],
                'email' => $validated['new_email'] ?? null,
                'address' => $request->input('new_address'), // tambahkan baris ini
                'store_id' => session('selected_store'),
            ]);
            $customerId = $customer->id;
        }

        $totals = $this->cartService->calculateTotals($cart);
        $promoCode = session('promo_code');
        $promoDiscount = session('promo_discount') ?? 0;
        $grossAmount = max($totals['grandTotal'] - $promoDiscount, 0);
        $orderedQty = 0;
        $request->validate([
            'payment_proof' => 'nullable|image|max:10000', // max 2MB
        ]);

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
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('uploads/payment_proof', 'public');
                $paymentProofUrl = asset('storage/' . $path);
            } else {
                $paymentProofUrl = null;
            }
            $shippingAddress = $request->input('shipping_address');
            $resellerId = session('reseller_id');
            
            $order = Order::create([
                'customer_id' => $customerId,
                'reseller_id'    => $resellerId, 
                'total_item_price' => $totals['cartTotalPrice'],
                'PROMO_ID' => $promoCode ? Promo::where('Promo_Code', $promoCode)->value('id') : null,
                'order_status' => 'belum terkirim',
                'note' => $request->note,
                'description' => 'Order via kasir',
                'gross_amount' => $grossAmount,
                'payment_type' => $request->payment_method,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'store_id' => session('selected_store'),
                'delivery_address' => $shippingAddress,
                'pdf_url' => $paymentProofUrl,

            ]);

            $totalHpp = 0;
            $orderedQty = 0;

            foreach ($cart as $item) {
                $price = $item['price'] ?? 0;
                $quantity = $item['quantity'] ?? 0;
                $totalPrice = $price * $quantity;

                $variant = ProductVariants::find($item['variant_id']);
                $variantHpp = $variant ? $variant->hpp : 0;
                $totalHpp += $variantHpp * $quantity;

                DB::table('invoice')->insert([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity_bought' => $quantity,
                    'price' => $price,
                    'total_price' => $totalPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $orderedQty += $quantity;
            }

            $order->update(['total_hpp_orders' => $totalHpp]);

            // ✅ Update statistik di customer
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $totalQty = $customer->qty_ordered ?? 0;
                $newQtyTotal = $totalQty + $orderedQty;

                $totalOrders = Order::where('customer_id', $customer->id)->count();

                $customer->qty_ordered = $newQtyTotal;
                $customer->qty_ordered_avg = $totalOrders > 0 ? round($newQtyTotal / $totalOrders) : $newQtyTotal;
                $customer->has_ordered = 1;
                $customer->save();

                // ✅ Update statistik di customer_store
                $customerStore = CustomerStore::where('customer_id', $customer->id)
                    ->where('store_id', $order->store_id)
                    ->first();

                if ($customerStore) {
                    $customerStore->total_ordered_qty += $orderedQty;
                    $customerStore->total_orders += 1;
                    $customerStore->average_ordered_qty = $customerStore->total_orders > 0
                        ? round($customerStore->total_ordered_qty / $customerStore->total_orders)
                        : $customerStore->total_ordered_qty;

                    $customerStore->first_ordered_at = $customerStore->first_ordered_at ?? now();
                    $customerStore->last_ordered_at = now();
                    $customerStore->save();
                }
            }


            if (session()->has('reseller_id')) {
                $resellerId = session('reseller_id');
            
                $resellerStore = ResellerStore::where('reseller_id', $resellerId)
                    ->where('store_id', $order->store_id)
                    ->first();
            
                if ($resellerStore) {
                    $resellerStore->qty_sold += $orderedQty;
                    $resellerStore->total_sold += $order->gross_amount;
            
                    // Jika ada payment_rate, tambahkan total_commission
                    if ($resellerStore->payment_rate) {
                        $resellerStore->total_commission += $order->gross_amount * ($resellerStore->payment_rate / 100);
                    }
            
                    $resellerStore->save();
                }
            }
            

            DB::commit();

            $cart_items = session('cart');
            session()->forget(['cart', 'promo_code', 'promo_discount']);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'gross_amount' => $order->gross_amount,
                'cart_items' => $cart_items,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Customer checkout error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan order. Silakan coba lagi.'
            ], 500);
        }

    }




    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string'
        ]);

        $promoCode = $request->input('promo_code');

        $promo = $this->cartService->getPromoByCode($promoCode);

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

        $totals = $this->cartService->calculateTotals($cart);
        $calculatedDiscount = $this->cartService->calculatePromoDiscount($promo, $totals['subTotal']);

        session([
            'promo_code' => $promo->Promo_Code,
            'promo_discount' => $calculatedDiscount
        ]);

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

    public function form(Request $request)
    {

        if (!session('customer_id') && !session('customer_guest')) {
            return redirect()->route('customerOrder.loginForm', ['store_id' => $request->query('store_id')]);
        }

        $store_id = $request->query('store_id') ?? session('selected_store');
        if (!$store_id) {
            return abort(400, 'Store ID tidak ditemukan.');
        }

        session(['selected_store' => $store_id]);
        
        $selected_store = Store::where('id', $store_id)->where('is_open', 1)->first();
        if (!$selected_store) {
            return abort(404, 'Store tutup / tidak ditemukan.');
        }
        $cart = session('cart', []);
        $cartTotalItems = array_sum(array_column($cart, 'quantity'));
        $cartTotalPrice = array_reduce($cart, fn($carry, $item) => $carry + (($item['price'] ?? 0) * ($item['quantity'] ?? 0)), 0);

        $categories = ProductCategory::all();
        $categoryName = $request->get('category', 'All Products');
        $searchTerm = $request->get('search', '');

        $productsQuery = Product::where('store_id', $store_id)
            ->with(['sizePrices.options.attribute']);

        if ($searchTerm) {
            $productsQuery->where('name', 'LIKE', "%{$searchTerm}%");
        }

        if ($categoryName === 'Promo') {
            $promoIDs = ProductVariants::where('is_promo', 'yes')->pluck('product_id')->unique()->toArray();
            $products = $productsQuery->whereIn('id', $promoIDs)->get();
        } elseif ($categoryName !== 'All Products') {
            $category = ProductCategory::where('category_name', $categoryName)->first();
            $products = $category ? $productsQuery->where('category_id', $category->id)->get() : collect();
        } else {
            $products = $productsQuery->get();
        }
        // data:
        $productsWithDetails = $products->map(function ($product) {
            $variants = $product->sizePrices->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'price' => $sp->price,
                    'stock' => $sp->stock,
                    'isSoldOut' => $sp->stock <= 0,
                    'quantity' => intval($sp->quantity),
                    'isPromo' => $sp->is_promo === 'yes',
                    'price_discount' => $sp->price_discount,
                    'final_price' => $sp->is_promo === 'yes' ? $sp->price - $sp->price_discount : $sp->price,
                    'variant_options' => $sp->options->map(fn($opt) => $opt->attribute->name . ': ' . $opt->name)->toArray(),
                ];
            });

            $isSoldOut = $variants->every(fn($v) => $v['isSoldOut']);
            $promoVariants = $variants->filter(fn($v) => $v['isPromo']);

            if ($promoVariants->isNotEmpty()) {
                $cheapestPromo = $promoVariants->sortBy('final_price')->first();
                return [
                    'product' => $product,
                    'isSoldOut' => $isSoldOut,
                    'isPromo' => 'yes',
                    'price' => $cheapestPromo['final_price'],
                    'normal_price' => $cheapestPromo['price'],
                    'variants' => $variants,
                ];
            }

            return [
                'product' => $product,
                'isSoldOut' => $isSoldOut,
                'isPromo' => 'no',
                'price' => $variants->min('price'),
                'normal_price' => null,
                'variants' => $variants,
            ];
        });

        return view('customer-order.form', compact(
            'selected_store',
            'categories',
            'categoryName',
            'searchTerm',
            'cart',
            'cartTotalItems',
            'cartTotalPrice',
            'productsWithDetails'
        ));
    }

    public function addItem(Request $request)
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
            Log::error('Customer updateCartQuantity error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui keranjang.',
            ], 500);
        }
    }

}