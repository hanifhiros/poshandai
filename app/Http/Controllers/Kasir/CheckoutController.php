<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckoutController extends Controller
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

        $ppn = $cartTotalPrice * 0.11;
        $grandTotal = $cartTotalPrice + $ppn;
       
        return view('handai-kasir.checkout.checkout-kasir', compact(
            'cart',
            'cartTotalItems',
            'subTotal',
            'discountTotal',
            'cartTotalPrice',
            'ppn',
            'grandTotal'
        ));
        
    }

}
