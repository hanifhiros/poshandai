<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariants;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Store;

class DashboardController extends Controller
{
    private const CATEGORY_PROMO = 'Promo';
    private const CATEGORY_ALL = 'All Products';

    public function index(Request $request)
    {
        // Ambil data cart dari session
        $cart = session('cart', []);
        $cartTotalItems = array_sum(array_column($cart, 'quantity'));
        $cartTotalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + (($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        }, 0);

        // Data toko dan kategori
        $selected_store_id = session('selected_store');
        $selected_store = $selected_store_id ? Store::find($selected_store_id) : null;
        $categories = ProductCategory::all();
        $categoryName = $request->get('category', self::CATEGORY_ALL);
        $searchTerm = $request->get('search', '');

        // Query produk sesuai toko dan pencarian
        $productsQuery = Product::where('store_id', $selected_store_id)
            ->with(['sizePrices.options.attribute']);

        if ($searchTerm) {
            $productsQuery->where('name', 'LIKE', "%{$searchTerm}%");
        }

        if ($categoryName === self::CATEGORY_PROMO) {
            $promoIDs = ProductVariants::where('is_promo', ProductVariants::PROMO_YES)
                ->pluck('product_id')->unique()->toArray();
            $products = $productsQuery->whereIn('id', $promoIDs)->get();
        } elseif ($categoryName !== self::CATEGORY_ALL) {
            $category = ProductCategory::where('category_name', $categoryName)->first();
            if ($category) {
                $products = $productsQuery->where('category_id', $category->id)->get();
            } else {
                $products = collect();
            }
        } else {
            $products = $productsQuery->get();
        }

        // Proses detail produk
        $productsWithDetails = $products->map(function ($product) {
            $variants = $product->sizePrices->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'price' => $sp->price,
                    'stock' => $sp->stock,
                    'isSoldOut' => $sp->stock <= 0,
                    'quantity' => intval($sp->quantity),
                    'isPromo' => $sp->is_promo === ProductVariants::PROMO_YES,
                    'price_discount' => $sp->price_discount,
                    'final_price' => $sp->is_promo === ProductVariants::PROMO_YES
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
                $isPromo = ProductVariants::PROMO_YES;
            } else {
                $finalPrice = $variants->min('price');
                $normalPrice = null;
                $isPromo = ProductVariants::PROMO_NO;
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

        return view('handai-kasir.dashboard.index', compact(
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
}
