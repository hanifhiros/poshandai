<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\Superadmin\AccountController;
use App\Http\Controllers\Superadmin\StoreController;
use App\Http\Controllers\Superadmin\SimulateController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Login;
use App\Http\Controllers\LogoutController;

Route::group(['middleware' => ['web'], 'prefix' => '/', 'namespace' => 'App\Http\Controllers'], function () {
    Route::GET('/', [HomeController::class, 'index'])->name('home');
    Route::GET('login', 'Login@index')->name('login');
    Route::POST('/login', [Login::class, 'exe'])->name('login.post');
    Route::POST('logout', 'Login@logout')->name('logout');
});

Route::namespace('App\Http\Controllers\Customer')->group(base_path('routes/web_customer.php'));

Route::middleware(['auth', 'role:Superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperadminController::class, 'index'])->name('dashboard');

    // Manajemen Akun
    Route::get('/accounts', [AccountController::class, 'index'])->name('account.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{id}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    // Kelola Toko
    Route::get('/stores', [StoreController::class, 'index'])->name('store.index');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::get('/stores/{id}/edit', [StoreController::class, 'edit'])->name('stores.edit');
    Route::put('/stores/{id}', [StoreController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{id}', [StoreController::class, 'destroy'])->name('stores.destroy');

    // Simulasi Role (SUDAH DIBERSIHKAN)
    Route::get('/simulate', [SimulateController::class, 'index'])->name('simulate.index');
    Route::post('/simulate/login', [SimulateController::class, 'login'])->name('simulate.login');
});

Route::post('/logout-universal', [LogoutController::class, 'logout'])->name('universal.logout');

// PENGALIHAN Route File Eksternal
Route::prefix('pos')->middleware(['web', 'auth'])->namespace('App\Http\Controllers\POS')->group(__DIR__ . '/web_pos.php');
Route::prefix('manager')->middleware(['web', 'auth'])->group(__DIR__ . '/web_manager.php');