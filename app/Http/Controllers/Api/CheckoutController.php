<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{Customer, Order, Promo, ProductVariants, CustomerStore};

class CheckoutController extends Controller
{
    public function sessionCart(Request $request){
        
            session(['cart' => $request->input('items', [])]);
            return response()->json([
                'success' => true,
                'message' => 'Keranjang disimpan ke session.',
                'cart' => session('cart')
            ]);
    
    }
    public function store(Request $request)
    {
        $cart = $request->filled('cart') ? json_decode($request->input('cart'), true) : session('cart', []);
  
        session()->put('cart', $cart);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong.'], 400);
        }
       

        $request->validate([
            'cart' => 'nullable|string', // JSON string of items
            'payment_method' => 'required|in:cash,transfer,qris',
            'delivery_fee' => 'required|numeric|min:0',
            'delivery_address' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'note' => 'nullable|string',
            'payment_proof' => 'nullable|image|max:10240', // for file upload
            'payment_proof_base64' => 'nullable|string',   // alternative for base64 (optional)
            'customer_id' => 'nullable|numeric|exists:customer,id',
            
            // Jika customer_id tidak dikirim (atau = 'new'), maka validasi tambahan
            'new_name' => 'required_if:customer_id,new|string|max:255',
            'new_contact' => 'required_if:customer_id,new|string|max:20',
            'new_gender' => 'required_if:customer_id,new|in:Laki-laki,Perempuan',
            'new_email' => 'nullable|email|max:255',
            'new_address' => 'required_if:customer_id,new|string|max:255',
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
                'address' => $request->input('new_address'),
                'store_id' => session('selected_store'),
            ]);
            $customerId = $customer->id;
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer tidak ditemukan.'], 404);
        }
        $totals = $this->calculateCartTotals();
        $promoCode = session('promo_code');
        $promoDiscount = session('promo_discount') ?? 0;
        $deliveryFee = floatval($request->input('delivery_fee', 0));
        $grossAmount = max($totals['grandTotal'] - $promoDiscount + $deliveryFee, 0);        
        $orderedQty = 0;
        $storeId = session('selected_store') ?? $request->input('store_id');

        // return response()->json(['success' => false, 'message' => $totals, $cart,  session('cart', [])], 400);
        $proofPath = null;

        if ($request->has('payment_proof_base64')) {
            try {
                $base64String = $request->input('payment_proof_base64');
                $imageData = base64_decode($base64String);
                $filename = 'proof_' . time() . '.jpg';
                $path = storage_path('app/public/payment_proofs/' . $filename);
                file_put_contents($path, $imageData);
                $proofPath = 'payment_proofs/' . $filename;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan bukti pembayaran base64: ' . $e->getMessage()
                ], 500);
            }
        } elseif ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }
      
        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $customerId,
                'total_item_price' => $totals['cartTotalPrice'],
                'PROMO_ID' => $promoCode ? Promo::where('Promo_Code', $promoCode)->value('id') : null,
                'order_status' => 'belum terkirim',
                'note' => $request->note,
                'description' => 'Order via Aplikasi',
                'gross_amount' => $grossAmount,
                'payment_type' => $request->payment_method,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'store_id' => $storeId,
                'delivery_address' => $request->input('shipping_address'),
                'pdf_url' => $proofPath,
                'delivery_fee' => $deliveryFee,

            ]);

            $totalHpp = 0;
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
                    'quantity_bought' => $item['quantity'],
                    'price' => $price,
                    'total_price' => $totalPrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $orderedQty += $quantity;
            }

            $order->update(['total_hpp_orders' => $totalHpp]);

            $customer->qty_ordered += $orderedQty;
            $totalOrders = Order::where('customer_id', $customerId)->count();
            $customer->qty_ordered_avg = $totalOrders > 0 ? round($customer->qty_ordered / $totalOrders) : $orderedQty;
            $customer->has_ordered = 1;
            $customer->save();

            // Update or insert into customer_store

            $customerStore = CustomerStore::where('customer_id', $customerId)
                    ->where('store_id', $storeId)
                    ->first();

                if ($customerStore) {
                    // Kalau sudah pernah order di store ini
                    $customerStore->qty_ordered += $orderedQty;
                    $customerStore->orders += 1;
                    $customerStore->qty_avg = round($customerStore->qty_ordered / $customerStore->orders);
                    $customerStore->last_ordered_at = now();
                    $customerStore->save();
                } else {
                    // Kalau pertama kali order di store ini
                    CustomerStore::create([
                        'customer_id' => $customerId,
                        'store_id' => $storeId,
                        'qty_ordered' => $orderedQty,
                        'orders' => 1,
                        'qty_avg' => $orderedQty,
                        'first_ordered_at' => now(),
                        'last_ordered_at' => now(),
                    ]);
                }
            // CustomerStore::updateOrCreate(
            //     [
            //         'customer_id' => $customerId,
            //         'store_id' => $storeId,
            //     ],
            //     [
            //         'qty_ordered' => DB::raw("qty_ordered + $orderedQty"),
            //         'qty_avg' => DB::raw("ROUND((qty_ordered + $orderedQty) / (orders + 1))"),
            //         'orders' => DB::raw("orders + 1"),
            //         'last_ordered_at' => now(),
            //         'first_ordered_at' => DB::raw("COALESCE(first_ordered_at, NOW())"),
            //         'updated_at' => now(),
            //     ]
            // );

            DB::commit();
            session()->forget(['cart', 'promo_code', 'promo_discount']);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'gross_amount' => $order->gross_amount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan order: ' . $e->getMessage()
            ], 500);
        }
    }
    private function calculateCartTotals()
    {
        $cart = session('cart', []);
        $subTotal = 0;
        $cartTotalPrice = 0;
        $discountTotal = 0;

        foreach ($cart as $item) {
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 0;

            $normal = $item['normal_price'] ?? $price;

            $subTotal += $normal * $quantity;
            $cartTotalPrice += $price * $quantity;
            $discountTotal += ($normal - $price) * $quantity;
        }

        $ppn = $cartTotalPrice * 0; // Bisa diganti jika ada PPN
        $grandTotal = $cartTotalPrice + $ppn;

        return [
            'subTotal' => $subTotal,
            'cartTotalPrice' => $cartTotalPrice,
            'discountTotal' => $discountTotal,
            'ppn' => $ppn,
            'grandTotal' => $grandTotal,
        ];
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
                'error' => 'Kode promo tidak ditemukan.'
            ], 404);
        }

        if ($promo->is_active !== 'Ya') {
            return response()->json([
                'success' => false,
                'error' => 'Promo tidak aktif.'
            ], 400);
        }

        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'error' => 'Keranjang kosong.'
            ], 400);
        }

        $subTotal = 0;
        foreach ($cart as $item) {
            $price = $item['price'] ?? 0;
            $normal = $item['normal_price'] ?? $price;
            $qty = $item['quantity'] ?? 0;
            $subTotal += $normal * $qty;
        }

        $calculatedDiscount = $subTotal * ($promo->discount_rate / 100);
        if ($calculatedDiscount > $promo->max_discount_price) {
            $calculatedDiscount = $promo->max_discount_price;
        }

        session([
            'promo_code' => $promo->Promo_Code,
            'promo_discount' => $calculatedDiscount,
        ]);

        $totals = $this->calculateCartTotals();
        $grandTotalAfterPromo = max($totals['grandTotal'] - $calculatedDiscount, 0);

        return response()->json([
            'success' => true,
            'promo_code' => $promo->Promo_Code,
            'promo_discount' => $calculatedDiscount,
            'subTotal' => $totals['subTotal'],
            'cartTotalPrice' => $totals['cartTotalPrice'],
            'discountTotal' => $totals['discountTotal'],
            'ppn' => $totals['ppn'],
            'grandTotal' => $totals['grandTotal'],
            'grandTotalAfterPromo' => $grandTotalAfterPromo,
        ]);
    }

}
