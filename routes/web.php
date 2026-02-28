<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\Superadmin\AccountController;
use App\Http\Controllers\Superadmin\SimulationController;
use Illuminate\Http\Request;
use App\Http\Controllers\Public\CustomerStoreSelectorController;
use App\Http\Controllers\Superadmin\StoreController;
// Route::get('/', function () {
//     return view('handai-pos.checkout.checkout-pos');

// });

Route::group(['middleware' => ['web'], 'prefix' => '/', 'namespace' => 'App\Http\Controllers'], function () {
    Route::GET('/', 'Landingpage\HomeController@index')->name('home');

    Route::GET('login', 'Login@index')->name('login');
    Route::POST('login/exe', 'Login@exe');
    Route::GET('logout', 'Login@logout')->name('logout');
    Route::GET('register', 'Login@register')->name('register');
    Route::POST('register', 'Login@store');

});

Route::namespace('App\Http\Controllers\Customer')->group(base_path('routes/web_customer.php'));

Route::prefix('reseller')->middleware(['web', 'auth', 'role:Reseller'])->namespace('App\Http\Controllers\Reseller')->group(__DIR__.'/web_reseller.php');


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


Route::prefix('reseller')->middleware(['web', 'auth', 'cekrole:Reseller'])->namespace('App\Http\Controllers\Reseller')->group(base_path('routes/web_reseller.php'));

Route::get('/reseller/register', [\App\Http\Controllers\Public\ResellerRegisterController::class, 'showForm'])->name('reseller.register.form');
Route::post('/reseller/register', [\App\Http\Controllers\Public\ResellerRegisterController::class, 'submitForm'])->name('reseller.register.submit');
// Route::get('/reseller/login', [Login::class, 'resellerLoginForm'])->name('reseller.login');
// Route::post('/reseller/login', [Login::class, 'resellerLoginExe'])->name('reseller.login.exe');


//PENGALIHAN Route
Route::prefix('pos')->middleware(['web', 'auth'])->namespace('App\Http\Controllers\POS')->group(__DIR__ . '/web_pos.php');
Route::prefix('manager')->middleware(['web', 'auth'])->namespace('App\Http\Controllers\Manager')->group(__DIR__ . '/web_manager.php');
Route::prefix('kasir')->middleware(['web', 'auth'])->namespace('App\Http\Controllers\Kasir')->group(__DIR__ . '/web_kasir.php');

