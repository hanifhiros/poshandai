<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariants;

class CustomerProductController extends Controller
{
    // Ambil daftar produk berdasarkan store_id
    public function getProductsByStore(Request $request)
    {
        $storeId = $request->input('store_id');

        if (!$storeId) {
            return response()->json(['status' => 'error', 'message' => 'store_id is required'], 422);
        }

        $products = Product::with(['category', 'variants']) // tambahkan relasi 'variants'
        ->where('store_id', $storeId)
        ->get()
        ->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category->name ?? null,
                'image' => $p->image_url,
                'image_mobile' => $p->image_url_mobile,
                'is_promo' => $p->is_promo,
                'price' => optional($p->variants->first())->price ?? 0,
                'price_discount' => optional($p->variants->first())->price_discount ?? null,
    
                // Tambahkan semua varian untuk tampilkan lengkap
                'variants' => $p->variants->map(fn($v) => [
                    'id' => $v->id,
                    'price' => $v->price,
                    'discount' => $v->price_discount,
                    'quantity' => $v->quantity,
                ])
            ];
        });
    

        return response()->json([
            'status' => 'success',
            'products' => $products,
        ]);
    }

    // Ambil daftar varian berdasarkan product_id
    public function getVariantsByProduct(Request $request)
    {
        $productId = $request->product_id;

        if (!$productId) {
            return response()->json([
                'status' => 'error',
                'message' => 'product_id is required',
                'variants' => [],
            ], 422);
        }

        $variants = ProductVariants::with(['variantOptions.attribute'])
            ->where('product_id', $productId)
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'price' => $variant->price,
                    'price_discount' => $variant->price_discount,
                    'is_promo' => $variant->is_promo,
                    'quantity' => $variant->quantity,
                    'options' => $variant->variantOptions->map(function ($opt) {
                        return [
                            'attribute' => $opt->attribute?->name ?? '-',
                            'option' => $opt->name
                        ];
                    })->values()
                ];
            });

        return response()->json([
            'status' => 'success',
            'variants' => $variants,
        ]);
    }


}
