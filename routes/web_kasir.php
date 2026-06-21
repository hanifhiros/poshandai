<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\POS\DashboardPOS;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POS\KasirController;
use App\Http\Controllers\POS\CartController;
use App\Http\Controllers\POS\CheckoutController;
Route::middleware(['web', 'auth', 'cekrole:Kasir'])->group(function () {
    Route::get('select-store', [KasirController::class, 'index'])->name('kasir.store');

    Route::post('set-store', [KasirController::class, 'setStore'])->name('kasir.setstore');
    // route::get('start-order', 'StartOrder@index')->name('pos.startorder');
    Route::get('dashboard', [DashboardPOS::class, 'index'])->name('kasir.dashboard');
    // Card related routes
    Route::get('/cart', [CartController::class, 'index'])->name('kasir.cart.index');
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('kasir.cart.add');

    // Order related routes
   
    Route::post('cart/update', 'CartController@updateCartQuantity')->name('cart.update');
    Route::post('cart/checkout', 'CartController@checkoutWithCustomer')->name('kasir.cart.checkout');
    Route::post('cart/apply-promo', 'CartController@applyPromo')->name('kasir.cart.applyPromo');
    Route::get('cart/get-promos', 'CartController@getPromos')->name('kasir.cart.getPromos');
    Route::post('cart/clear', 'CartController@clearCart')->name('kasir.cart.clear');
    Route::post('cart/remove-promo', 'CartController@removePromo')->name('kasir.cart.removePromo');
    // Checkout related routes
    Route::get('/checkout', [CartController::class, 'index'])->name('kasir.checkout');
    Route::get('/invoice/{id}', [KasirController::class, 'showInvoice'])->name('kasir.invoice');
    Route::post('/cart/remove-item', [CartController::class, 'removeItem'])->name('kasir.cart.removeItem');
    Route::get('/invoice/print/{order}', [\App\Http\Controllers\POS\InvoiceController::class, 'show'])->name('kasir.invoice.print');
    Route::get('/invoice/pdf/{orderId}', [\App\Http\Controllers\POS\InvoiceController::class, 'downloadPdf'])->name('kasir.invoice.pdf');

});