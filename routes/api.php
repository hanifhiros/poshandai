<?php

use App\Http\Controllers\Api\ProductionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Login;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreMobileController;
use App\Http\Controllers\Api\StoreProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\MobileStockController;
use App\Http\Controllers\Api\CustomerProductController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\MobileStockBatchController;
use App\Http\Controllers\POS\DashboardPOS;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\Api\DashboardMobileController;
use App\Http\Controllers\Api\MobileUnitController;
use App\Http\Controllers\Api\VariantAttributeController;
use App\Http\Controllers\Api\CustomerController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/test', function (Request $request) {
    return response()->json(['message' => 'This is a public endpoint.']);
});

Route::get('/variant-attributes', [VariantAttributeController::class, 'index']);
Route::post('/login', [Login::class, 'exemobile']);
Route::get('/customer', [Login::class, 'cust']);
Route::get('/units', [Login::class, 'unit']);

Route::get('/stocks/{stock_category_id}', [Login::class, 'StockByCategory']);
Route::post('/Addstocks', [Login::class, 'addStock']);
Route::get('/finance', [Login::class, 'finance']);

Route::get('/product-count-by-size', [Login::class, 'countBySize']);

Route::get('/sales-today', [Login::class, 'salesToday']);
Route::get('/products', [Login::class, 'getProducts']);
Route::get('/today-production', [Login::class, 'todayProduction']);
Route::get('/production-standard', [Login::class, 'productStandard']);

Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/nearby', [StoreController::class, 'nearby']);
Route::get('/stores/{store}/products', [StoreProductController::class, 'index']);
Route::get('/stores/{id}/products', [DashboardPOS::class, 'getProductsByStore']);
Route::get('/image-proxy', function (Request $request) {
    $file = $request->query('file');
    if (!$file) {
        abort(400, 'Missing file parameter');
    }
    $file = ltrim($file, '/');

    $publicPath = public_path($file);

    $stripped = preg_replace('#^storage/#', '', $file);
    $storagePath = storage_path('app/public/' . $stripped);

    if (File::exists($publicPath)) {
        $path = $publicPath;
    } elseif (File::exists($storagePath)) {
        $path = $storagePath;
    } else {
        abort(404, "File not found: $file");
    }

    return response()->file($path, [
        'Content-Type' => File::mimeType($path),
        // tambahkan header CORS
        'Access-Control-Allow-Origin' => '*',
    ]);
});

Route::middleware('auth:sanctum')->get('/user/profile', [UserController::class, 'profile']);

Route::post('/register', [SignupController::class, 'register']);
Route::get('/stores/{id}', [StoreController::class, 'show']);
// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
});
Route::get('/product-variants1', [ProductVariantController::class, 'apiIndex']);


Route::get('/dashboard-manager', [DashboardMobileController::class, 'summary']);
Route::post('/product/store', [ProductVariantController::class, 'store']);


Route::get('/stocks', [MobileStockController::class, 'apiIndex']);
Route::post('/stocks', [MobileStockController::class, 'apiStore']);


Route::get('/produksi-form', [ProductionController::class, 'apiProduksiForm']);
Route::get('/productions', [ProductionController::class, 'apiProductions']);
Route::post('/produksi-store', [ProductionController::class, 'produksiStore']);
Route::get('/production-filters', [ProductionController::class, 'apiProductionFilters']);

Route::get('/units', [MobileStockController::class, 'getUnits']);
Route::get('/stock-categories', [MobileStockController::class, 'getStockCategories']);
Route::get('/stock-batches', [MobileStockBatchController::class, 'index']);


Route::post('/stock-batches/{stock_id}', [MobileStockBatchController::class, 'store']);
Route::get('/units', [MobileUnitController::class, 'index']);


Route::get('/stores/nearby', [StoreMobileController::class, 'getNearbyStores']);
Route::get('/products', [CustomerProductController::class, 'getProductsByStore']);
Route::get('/product-variants', [CustomerProductController::class, 'getVariantsByProduct']);
Route::post('/checkout', [CheckoutController::class, 'store']);
Route::post('/cart', [CheckoutController::class, 'sessionCart']);
Route::get('/orders', [MobileStockController::class, 'OrderData']);
Route::post('/orders/{id}/update-status', [MobileStockController::class, 'updateStatus']);
Route::get('/customer/mobile', [CustomerController::class, 'custMobile']);
Route::post('/customer', [CustomerController::class, 'store']);
Route::put('/customer/{id}', [CustomerController::class, 'update']);
Route::delete('/customer/{id}', [CustomerController::class, 'destroy']);