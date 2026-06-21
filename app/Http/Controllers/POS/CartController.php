<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ProductVariants;
use App\Models\ProductionHistory;
use App\Models\Promo;
use App\Models\Customer;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\AccountingService;
use App\Services\CartService;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = session('cart', []);
        $cartDetails = [];
        $cartTotalItems = 0;
        $subTotal = 0;
        $cartTotalPrice = 0;
        $discountTotal = 0;

        $variantIds = array_column($cart, 'variant_id');
        $variants = ProductVariants::with(['product', 'options.attribute'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($cart as $item) {
            $variant = $variants->get($item['variant_id']);
            if ($variant) {
                $normalPrice = $variant->price;
                $promoPrice = ($variant->is_promo === ProductVariants::PROMO_YES)
                    ? ($variant->price - $variant->price_discount)
                    : $variant->price;

                $qty = $item['quantity'];

                $producedQty = ProductionHistory::where('product_variants_id', $variant->id)
                    ->sum('quantity_produced');

                $cartDetails[] = [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_summary' => $variant->variantSummary(),
                    'quantity' => $qty,
                    'available_qty' => $producedQty,
                    'min_stock' => $variant->min_stock ?? 0,
                    'price' => $promoPrice,
                    'normal_price' => $normalPrice,
                    'note' => $item['note'] ?? '',
                ];
                $subTotal += $normalPrice * $qty;
                $cartTotalPrice += $promoPrice * $qty;
                $discountTotal += ($normalPrice - $promoPrice) * $qty;
            }
        }

        $cartTotalItems = array_sum(array_column($cart, 'quantity'));
        $ppn = $cartTotalPrice * 0.0;
        $grandTotal = $cartTotalPrice + $ppn;

        $customers = Customer::where('store_id', session('selected_store'))->orderBy('name')->get();

        return view('handai-pos.checkout.checkout', compact(
            'cartDetails',
            'cartTotalItems',
            'subTotal',
            'discountTotal',
            'cartTotalPrice',
            'ppn',
            'grandTotal',
            'customers'
        ));
    }

    public function getPromos()
    {
        $promos = Promo::where('is_active', Promo::STATUS_ACTIVE)->get();
        return response()->json([
            'success' => true,
            'promos' => $promos
        ]);
    }

    public function updateCartQuantity(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $data['product_id'];
        $variantId = $data['variant_id'];
        $newQty = $data['quantity'];

        $variant = ProductVariants::find($variantId);
        if (!$variant) {
            return response()->json(['error' => 'Produk tidak ditemukan.'], 404);
        }

        // 1. Ambil data keranjang saat ini untuk tahu apakah user menekan [+] atau [-]
        $cart = session('cart', []);
        $oldQty = 0;
        $foundIndex = -1;

        foreach ($cart as $index => $item) {
            if ($item['product_id'] == $productId && $item['variant_id'] == $variantId) {
                $oldQty = $item['quantity'];
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === -1) {
            return response()->json(['error' => 'Item tidak ditemukan di cart.'], 404);
        }

        // 2. Cek stok di database
        $producedQty = ProductionHistory::where('product_variants_id', $variantId)
            ->sum('quantity_produced');
            
        // 3. Logika Pintar Penyelamat "Keranjang Nyangkut"
        if ($newQty > $producedQty) {
            if ($newQty >= $oldQty) {
                // Jika user menekan [+] dan melebihi stok -> TOLAK
                return response()->json([
                    'success' => false, 
                    'message' => 'Stok tidak mencukupi! Sisa stok di gudang hanya ' . $producedQty . ' item.'
                ], 400);
            } else {
                // Jika user menekan [-] tapi angka barunya masih di atas stok asli
                // (Kasus nyangkut dari 5 mau ke 4, padahal stok 1)
                // PAKSA angkanya langsung turun ke batas maksimal stok.
                $newQty = $producedQty > 0 ? $producedQty : 1; 
            }
        }

        $finalPrice = ($variant->is_promo === ProductVariants::PROMO_YES)
            ? ($variant->price - $variant->price_discount)
            : $variant->price;

        // Update nilai di array keranjang
        $cart[$foundIndex]['quantity'] = $newQty;
        $cart[$foundIndex]['price'] = $finalPrice;

        if (!isset($cart[$foundIndex]['normal_price'])) {
            $cart[$foundIndex]['normal_price'] = $variant->price;
        }

        session(['cart' => $cart]);

        $itemTotal = $newQty * $finalPrice;
        $cartTotalItems = array_sum(array_column($cart, 'quantity'));
        $totals = $this->cartService->calculateTotals($cart);

        $promoDiscount = 0;
        $promoCode = session('promo_code');

        if ($promoCode) {
            $promo = $this->cartService->getPromoByCode($promoCode);
            if ($promo && $promo->is_active === Promo::STATUS_ACTIVE) {
                $promoDiscount = $this->cartService->calculatePromoDiscount($promo, $totals['cartTotalPrice']);
            }
        }

        $grandTotal = $totals['grandTotal'] - $promoDiscount;

        return response()->json([
            'success' => true,
            'quantity' => $newQty,
            'itemTotal' => number_format($itemTotal, 0, ',', '.'),
            'cartTotalItems' => $cartTotalItems,
            'cartTotalPrice' => number_format($totals['cartTotalPrice'], 0, ',', '.'),
            'subTotal' => number_format($totals['subTotal'], 0, ',', '.'),
            'discountTotal' => number_format($totals['discountTotal'], 0, ',', '.'),
            'promo_discount' => number_format($promoDiscount, 0, ',', '.'),
            'promo_code' => $promoCode ?? '',
            'ppn' => number_format($totals['ppn'], 0, ',', '.'),
            'grandTotal' => number_format($grandTotal, 0, ',', '.'),
        ]);
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

        if ($promo->is_active !== Promo::STATUS_ACTIVE) {
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

    public function removeItem(Request $request)
    {
        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');

        $cart = session('cart', []);
        $cart = array_filter($cart, function ($item) use ($productId, $variantId) {
            return !($item['product_id'] == $productId && $item['variant_id'] == $variantId);
        });

        session(['cart' => array_values($cart)]);

        return response()->json(['success' => true, 'message' => 'Item berhasil dihapus']);
    }

    public function updateItemNote(Request $request)
    {
        $variantId = $request->input('variant_id');
        $note = $request->input('note', '');

        $cart = session('cart', []);
        foreach ($cart as &$item) {
            if ($item['variant_id'] == $variantId) {
                $item['note'] = $note;
                break;
            }
        }
        session(['cart' => $cart]);

        return response()->json(['success' => true]);
    }

    public function checkoutWithCustomer(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong.'], 400);
        }

        foreach ($cart as $item) {
            $variant = ProductVariants::find($item['variant_id']);
            if (!$variant) continue;
            
            $producedQty = ProductionHistory::where('product_variants_id', $variant->id)
                ->sum('quantity_produced');
            $availableQty = max($producedQty, $variant->quantity ?? 0);
            if (($item['quantity'] ?? 0) > $availableQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok produk {$variant->product->name} telah habis / tidak mencukupi. Sisa stok: {$availableQty}.",
                    'variant_id' => $variant->id,
                    'available' => $availableQty,
                    'requested' => $item['quantity'] ?? 0,
                ], 400);
            }
        }

        $request->validate([
            'payment_method' => 'required|in:tunai,non_tunai,campuran',
        ]);

        $customerId = null;
        $customerType = $request->input('customer_type', 'none');

        if ($customerType === 'existing' && $request->customer_id) {
            $customerId = $request->customer_id;
        } elseif ($customerType === 'new' && $request->customer_name) {
            $customer = Customer::create([
                'name' => $request->customer_name,
                'nickname' => $request->new_nickname ?? '',
                'contact_number' => $request->new_contact ?? '',
                'store_id' => session('selected_store'),
            ]);
            $customerId = $customer->id;
        }

        $totals = $this->cartService->calculateTotals($cart);
        $additionalCharges = $request->input('additional_charges', []);
        $pajak = (int)($additionalCharges['pajak'] ?? 0);
        $ongkosKirim = (int)($additionalCharges['ongkos_kirim'] ?? 0);
        $kemasan = (int)($additionalCharges['kemasan'] ?? 0);
        $totalAdditional = $pajak + $ongkosKirim + $kemasan;
        $discountAmount = (int)($request->input('discount_amount', 0));
        $grossAmount = max($totals['cartTotalPrice'] - $discountAmount + $totalAdditional, 0);
        $itemNotes = $request->input('item_notes', []);

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
                'PROMO_ID' => null,
                'order_status' => 'terkirim',
                'note' => $request->input('note', ''),
                'description' => 'Order via POS',
                'gross_amount' => $grossAmount,
                'payment_type' => $request->payment_method,
                'store_id' => session('selected_store'),
                'seller_id' => auth()->id(), 
                'pajak' => $pajak,
                'ongkos_kirim' => $ongkosKirim,
                'kemasan' => $kemasan,
            ]);

            $orderedQty = 0;

            foreach ($cart as $item) {
                $price = $item['price'] ?? 0;
                $quantity = $item['quantity'];
                $totalPrice = $price * $quantity;
                $note = $itemNotes[$item['variant_id']] ?? ($item['note'] ?? '');

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

            $totalHpp = InventoryService::processSaleDeduction(
                $cart, $order->id, session('selected_store')
            );

            $order->update(['total_hpp_orders' => $totalHpp]);

            try {
                AccountingService::journalSale(
                    session('selected_store'), $grossAmount, $totalHpp, $order->id, 'POS'
                );
            } catch (\Exception $e) {
                Log::warning('POS Accounting journal failed: ' . $e->getMessage());
            }

            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    $totalQty = ($customer->qty_ordered ?? 0) + $orderedQty;
                    $totalOrders = Order::where('customer_id', $customer->id)->count();
                    $customer->qty_ordered = $totalQty;
                    $customer->qty_ordered_avg = $totalOrders > 0 ? round($totalQty / $totalOrders) : $totalQty;
                    $customer->has_ordered = 1;
                    $customer->save();
                }
            }

            DB::commit();
            session()->forget(['cart', 'promo_code', 'promo_discount']);
            $order->load(['customer']);

            $displayName = 'Customer Umum';
            if ($customerId && $order->customer) {
                $displayName = $order->customer->name;
            } elseif ($request->customer_name) {
                $displayName = $request->customer_name;
            }

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'gross_amount' => $grossAmount,
                'created_at' => optional($order->created_at)->toIso8601String(),
                'customer_name' => $displayName,
                'cashier_name' => optional(auth()->user())->name,
                'payment_method' => $order->payment_type,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS Checkout error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan order: ' . $e->getMessage()
            ], 500);
        }
    }
}