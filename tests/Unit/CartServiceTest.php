<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CartService;
use App\Models\Promo;

class CartServiceTest extends TestCase
{
    public function testCalculateTotals(): void
    {
        $service = new CartService();
        $cart = [
            ['price' => 8000, 'normal_price' => 10000, 'quantity' => 2],
            ['price' => 5000, 'normal_price' => 5000, 'quantity' => 1],
        ];

        $totals = $service->calculateTotals($cart);

        $this->assertEquals(25000, $totals['subTotal']);
        $this->assertEquals(21000, $totals['cartTotalPrice']);
        $this->assertEquals(4000, $totals['discountTotal']);
        $this->assertEquals(0, $totals['ppn']);
        $this->assertEquals(21000, $totals['grandTotal']);
    }

    public function testCalculatePromoDiscountCapsAtMax(): void
    {
        $service = new CartService();
        $promo = new Promo();
        $promo->discount_rate = 10;
        $promo->max_discount_price = 15000;

        $discount = $service->calculatePromoDiscount($promo, 200000);

        $this->assertEquals(15000, $discount);
    }
}
