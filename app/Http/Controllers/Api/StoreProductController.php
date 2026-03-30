<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariants;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    /**
     * GET /api/stores/{store}/products
     * Mengembalikan detail store dan daftar produk lengkap dengan variants
     */
    public function index(Store $store)
    {
        // 1. Ambil semua product beserta sizePrices dan opsi-opsinya
        $products = Product::where('store_id', $store->id)
            ->with(['sizePrices.options.attribute'])
            ->get();

        // 2. Siapkan data store
        $storeData = $store->only([
            'id',
            'store_name',
            'store_address',
            'is_open',
            'opening_time',
            'closing_time',
            'latitude',
            'longitude',
        ]);

        // 3. Mapping each product ke struktur yang Flutter butuhkan
        $productsData = $products->map(function ($product) {
            // Map setiap sizePrice ke array variant
            $variants = $product->sizePrices->map(function ($sp) {
                $isPromo = ($sp->is_promo === ProductVariants::PROMO_YES);
                $finalPrice = $isPromo
                    ? ($sp->price - $sp->price_discount)
                    : $sp->price;

                return [
                    'id' => $sp->id,
                    'price' => (int) $sp->price,
                    'price_discount' => (int) $sp->price_discount,
                    'isPromo' => $isPromo ? ProductVariants::PROMO_YES : ProductVariants::PROMO_NO,
                    'final_price' => (int) $finalPrice,
                    'stock' => (int) $sp->stock,
                    'isSoldOut' => $sp->stock <= 0,
                    'quantity' => (int) $sp->quantity,
                    'variant_options' => $sp->options->map(fn($opt) => [
                        'attribute' => $opt->attribute->name,
                        'value' => $opt->name,
                    ]),
                ];
            });

            // Hitung flags dan harga final di level product
            $isSoldOut = $variants->every(fn($v) => $v['isSoldOut']);
            $promoVariants = $variants->filter(fn($v) => $v['isPromo'] === ProductVariants::PROMO_YES);

            if ($promoVariants->isNotEmpty()) {
                // pilih promo termurah
                $cheapestPromo = $promoVariants->sortBy('final_price')->first();
                $finalPrice = $cheapestPromo['final_price'];
                $normalPrice = $cheapestPromo['price'];
                $isPromo = ProductVariants::PROMO_YES;
            } else {
                // pilih harga normal termurah
                $minVariant = $variants->sortBy('price')->first();
                $finalPrice = $minVariant['price'];
                $normalPrice = null;
                $isPromo = ProductVariants::PROMO_NO;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->image_url,
                'image_url_mobile' => $product->image_url_mobile,
                'expired_duration' => $product->expired_duration,
                'category_id' => $product->category_id,
                'store_id' => $product->store_id,
                'isSoldOut' => $isSoldOut,
                'isPromo' => $isPromo,
                'price' => (int) $finalPrice,
                'normal_price' => $normalPrice !== null ? (int) $normalPrice : null,
                'variants' => $variants->values(), // reindex array
            ];
        });

        // 4. Kembalikan JSON
        return response()->json([
            'store' => $storeData,
            'products' => $productsData,
        ]);
    }
}
