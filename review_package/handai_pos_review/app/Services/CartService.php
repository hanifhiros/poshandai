<?php

namespace App\Services;

use App\Models\Promo;

class CartService
{
    public function calculateTotals(array $cart): array
    {
        $subTotal = 0;
        $cartTotalPrice = 0;
        $discountTotal = 0;

        foreach ($cart as $item) {
            $price = $item['price'] ?? 0;
            $normal = $item['normal_price'] ?? $price;
            $qty = $item['quantity'] ?? 0;

            $subTotal += $normal * $qty;
            $cartTotalPrice += $price * $qty;
            $discountTotal += ($normal - $price) * $qty;
        }

        $ppn = $cartTotalPrice * 0.0;
        $grandTotal = $cartTotalPrice + $ppn;

        return [
            'subTotal' => $subTotal,
            'cartTotalPrice' => $cartTotalPrice,
            'discountTotal' => $discountTotal,
            'ppn' => $ppn,
            'grandTotal' => $grandTotal,
        ];
    }

    public function calculatePromoDiscount(Promo $promo, float $baseAmount): float
    {
        $calculated = $baseAmount * ($promo->discount_rate / 100);
        return min($calculated, $promo->max_discount_price);
    }

    public function getPromoByCode(string $promoCode): ?Promo
    {
        return Promo::where('Promo_Code', $promoCode)->first();
    }
}
