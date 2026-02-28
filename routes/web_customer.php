<?php

use App\Http\Controllers\Public\CustomerOrderController;


use App\Http\Controllers\Public\CustomerStoreSelectorController;

use App\Http\Controllers\Public\CustomerAuthController;

Route::prefix('customer-order')->name('customerOrder.')->group(function () {
    // Auth
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('loginForm');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login');
    Route::get('/guest', [CustomerAuthController::class, 'guestLogin'])->name('guest');
    Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('registerForm');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('register');

    // Cart and Order Flow
    Route::get('/', [CustomerOrderController::class, 'form'])->name('form');
    Route::post('/add', [CustomerOrderController::class, 'addItem'])->name('cart.add');
    Route::post('/update', [CustomerOrderController::class, 'updateCartQuantity'])->name('cart.update');
    Route::post('/remove', [CustomerOrderController::class, 'removeItem'])->name('cart.removeItem');
    Route::post('/apply-promo', [CustomerOrderController::class, 'applyPromo'])->name('cart.applyPromo');
    Route::post('/promo/remove', [CustomerOrderController::class, 'removePromo'])->name('cart.removePromo');
    Route::post('/clear', [CustomerOrderController::class, 'clear'])->name('cart.clear');

    // DI DALAM grup Route::prefix('customer-order') {...}
    Route::get('/select-store', [CustomerStoreSelectorController::class, 'index'])->name('selectStore');
    Route::post('/set-store', [CustomerStoreSelectorController::class, 'setStore'])->name('setStore');

    // Checkout
    Route::get('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CustomerOrderController::class, 'checkoutWithCustomer'])->name('cart.checkout');
});

