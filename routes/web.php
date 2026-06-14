<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\Superadmin\AccountController;
use App\Http\Controllers\Superadmin\SimulationController;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Http\Request;
use App\Http\Controllers\Superadmin\StoreController;

Route::group(['middleware' => ['web'], 'prefix' => '/', 'namespace' => 'App\Http\Controllers'], function () {
    Route::GET('/', [HomeController::class, 'index'])->name('home');

    Route::GET('login', 'Login@index')->name('login');
    Route::POST('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
    Route::POST('logout', 'Login@logout')->name('logout');
    // Route::GET('register', 'Login@register')->name('register');
    // Route::POST('register', 'Login@store')->middleware('throttle:5,1');

});

Route::namespace('App\Http\Controllers\Customer')->group(base_path('routes/web_customer.php'));


Route::middleware(['auth', 'role:Superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperadminController::class, 'index'])->name('dashboard');

    // Manajemen Akun
    // Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts', [AccountController::class, 'index'])->name('account.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::delete('accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts/{id}/edit', [AccountController::class, 'edit'])->name('accounts.edit'); // 
    Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update'); // 



    // Kelola Toko
    Route::get('/stores', [StoreController::class, 'index'])->name('store.index');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store');
    Route::delete('/stores/{id}', [App\Http\Controllers\Superadmin\StoreController::class, 'destroy'])->name('stores.destroy');

    // Simulasi Role
    Route::get('/simulate', [SimulationController::class, 'index'])->name('simulate.index');
    Route::post('/simulate', [SimulationController::class, 'simulate'])->name('simulate.run');
    Route::get('/simulate/{store}/{role}', [SimulationController::class, 'simulate'])->name('simulate');

});

Route::post('/logout-universal', [\App\Http\Controllers\LogoutController::class, 'logout'])->name('universal.logout');


//PENGALIHAN Route
Route::prefix('pos')->middleware(['web', 'auth'])->namespace('App\Http\Controllers\POS')->group(__DIR__ . '/web_pos.php');
// manager routes use fully-qualified controller classes, no namespace prefix
Route::prefix('manager')->middleware(['web', 'auth'])->group(__DIR__ . '/web_manager.php');