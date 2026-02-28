<?php

namespace App\Http\Controllers\POS;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\Store;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class DashboardPOS extends Controller
{
    public function index(Request $request)
    {
        $cart = session('cart', []);
        $cartDetails = [];
        $cartTotalItems = 0;
        $cartTotalPrice = 0;

        // Batch load all variants at once to avoid N+1  
        $variantIds = array_filter(array_column($cart, 'variant_id'));
        $variantsMap = !empty($variantIds)
            ? ProductVariants::with(['product', 'options.attribute'])
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id')
            : collect();

        foreach ($cart as $item) {
            $variant = $variantsMap->get($item['variant_id']);

            if ($variant) {
                $finalPrice = ($variant->is_promo === 'yes')
                    ? ($variant->price - $variant->price_discount)
                    : $variant->price;

                $cartDetails[] = [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product->name ?? 'Unknown',
                    'variant_summary' => $variant->variantSummary(),
                    'quantity' => $item['quantity'] ?? 0,
                    'price' => $finalPrice,
                    'normal_price' => $variant->price,
                ];

                $cartTotalItems += $item['quantity'] ?? 0;
                $cartTotalPrice += $finalPrice * ($item['quantity'] ?? 0);
            }
        }

        // Data toko dan kategori
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $categories = ProductCategory::all();
        $categoryName = $request->get('category', 'All Products');
        $searchTerm = $request->get('search', '');

        $productsQuery = Product::where('store_id', $selected_store_id)
            ->with(['sizePrices.options.attribute', 'category']);

        if ($searchTerm) {
            $productsQuery->where('name', 'LIKE', "%{$searchTerm}%");
        }

        if ($categoryName === 'Promo') {
            $promoIDs = ProductVariants::where('is_promo', 'yes')
                ->pluck('product_id')->unique()->toArray();
            $products = $productsQuery->whereIn('id', $promoIDs)->get();
        } elseif ($categoryName !== 'All Products') {
            $category = ProductCategory::where('category_name', $categoryName)->first();
            if ($category) {
                $products = $productsQuery->where('category_id', $category->id)->get();
            } else {
                $products = collect();
            }
        } else {
            $products = $productsQuery->get();
        }

        $productsWithDetails = $products->map(function ($product) {
            $variants = $product->sizePrices->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'price' => $sp->price,
                    'stock' => intval($sp->quantity),
                    'isSoldOut' => intval($sp->quantity) <= 0,
                    'quantity' => intval($sp->quantity),
                    'isPromo' => $sp->is_promo === 'yes',
                    'price_discount' => $sp->price_discount,
                    'final_price' => $sp->is_promo === 'yes'
                        ? $sp->price - $sp->price_discount
                        : $sp->price,
                    'variant_options' => $sp->options->map(function ($opt) {
                        return $opt->attribute->name . ': ' . $opt->name;
                    })->toArray(),
                ];
            });

            $isSoldOut = $variants->every(fn($v) => $v['isSoldOut']);

            $promoVariants = $variants->filter(fn($v) => $v['isPromo']);
            if ($promoVariants->isNotEmpty()) {
                $cheapestPromo = $promoVariants->sortBy('final_price')->first();
                $finalPrice = $cheapestPromo['final_price'];
                $normalPrice = $cheapestPromo['price'];
                $isPromo = 'yes';
            } else {
                $finalPrice = $variants->min('price');
                $normalPrice = null;
                $isPromo = 'no';
            }

            return [
                'product' => $product,
                'isSoldOut' => $isSoldOut,
                'isPromo' => $isPromo,
                'price' => $finalPrice,
                'normal_price' => $normalPrice,
                'variants' => $variants,
            ];
        });

        return view('handai-pos.dashboard.index', compact(
            'selected_store',
            'categories',
            'categoryName',
            'searchTerm',
            'cartDetails',
            'cartTotalItems',
            'cartTotalPrice',
            'productsWithDetails'
        ));
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
            $variant = ProductVariants::with(['product', 'options.attribute'])->find($variantId);
            if (!$variant) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Variant tidak ditemukan.'], 404);
                }
                return redirect()->back()->with('error', 'Variant tidak ditemukan.');
            }

            $finalPrice = ($variant->is_promo === 'yes')
                ? ($variant->price - $variant->price_discount)
                : $variant->price;

            $cart[] = [
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'product_name' => $variant->product->name ?? 'Unknown',
                'variant_summary' => $variant->variantSummary(),
                'quantity' => $quantity,
                'price' => $finalPrice,
                'normal_price' => $variant->price,
            ];
        }

        session(['cart' => $cart]);

        // Return JSON for AJAX requests, redirect for standard form submissions
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Produk berhasil ditambahkan ke cart!']);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke cart!');
    }


    public function getProductsByStore($storeId)
    {
        $products = Product::with([
            'variantsById' => function ($query) {
                $query->select('id', 'product_id', 'price', 'price_discount', 'is_promo')
                    ->orderBy('price');
            }
        ])
            ->where('store_id', $storeId)
            ->get()
            ->map(function ($product) {
                $product->variants = $product->variantsById->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'price' => (int) $variant->price,
                        'price_discount' => (int) $variant->price_discount,
                        'is_promo' => $variant->is_promo
                    ];
                });

                unset($product->variantsById);

                return $product;
            });

        return response()->json($products);
    }

}