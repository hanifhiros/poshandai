<?php

use App\Http\Controllers\POS\CartController;
use App\Http\Controllers\POS\DashboardPOS;
use App\Http\Controllers\POS\KasirController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth', 'role:POS']], function () {
    // Store selection — no ensure.store needed
    Route::get('select-store', 'StoreController@index')->name('pos.store');
    Route::post('set-store', 'StoreController@setStore')->name('pos.setstore');

    // All other routes require a selected store
    Route::middleware(['ensure.store'])->group(function () {
        route::get('start-order', 'StartOrder@index')->name('pos.startorder');

        Route::get('dashboard', 'DashboardPOS@index')->name('pos.dashboard');
        Route::get('products', 'DashboardPOS@index')->name('products.index');

        Route::get('cart', 'CartController@index')->name('pos.checkout');
        Route::post('cart/update', 'CartController@updateCartQuantity')->name('pos.cart.update');
        Route::post('cart/apply-promo', 'CartController@applyPromo')->name('pos.cart.applyPromo');
        Route::get('cart/get-promos', 'CartController@getPromos')->name('cart.getPromos');
        Route::post('cart/remove-promo', 'CartController@removePromo')->name('pos.cart.removePromo');
        Route::post('cart/add', 'DashboardPOS@addToCart')->name('cart.add');
        Route::post('cart/checkout', 'CartController@checkoutSnap')->name('cart.checkout');
        Route::post('cart/clear', 'CartController@clearCart')->name('cart.clear');
        Route::post('cart/remove', 'CartController@removeItem')->name('pos.cart.remove');
        Route::post('cart/item-note', 'CartController@updateItemNote')->name('pos.cart.itemNote');
        Route::post('cart/checkout-customer', 'CartController@checkoutWithCustomer')->name('pos.cart.checkoutCustomer');

        Route::get('invoice/print/{order}', 'InvoiceController@show')->name('pos.invoice.print');

        // Transaction History
        Route::get('history', 'HistoryController@index')->name('pos.history');
    });
});

