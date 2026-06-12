<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerProductController;
use App\Http\Controllers\Api\DashboardMobileController;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobileStockBatchController;
use App\Http\Controllers\Api\MobileStockController;
use App\Http\Controllers\Api\MobileUnitController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreMobileController;
use App\Http\Controllers\Api\StoreProductController;
use App\Http\Controllers\Api\VariantAttributeController;
use App\Http\Controllers\Login;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/test', function (Request $request) {
    return response()->json(['message' => 'This is a public endpoint.']);
});

Route::get('/variant-attributes', [VariantAttributeController::class, 'index']);

// ── Public read-only endpoints ──
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/nearby', [StoreMobileController::class, 'getNearbyStores']);
Route::get('/stores/{id}', [StoreController::class, 'show']);
Route::get('/stores/{store}/products', [StoreProductController::class, 'index']);
Route::get('/products', [CustomerProductController::class, 'getProductsByStore']);
Route::get('/product-variants', [CustomerProductController::class, 'getVariantsByProduct']);
Route::get('/product-variants1', [ProductVariantController::class, 'apiIndex']);
Route::get('/units', [MobileUnitController::class, 'index']);
Route::get('/stock-categories', [MobileStockController::class, 'getStockCategories']);

// ── Rate-limited auth endpoints ──
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [Login::class, 'exemobile']);
    Route::post('/register', [SignupController::class, 'register']);
});

Route::middleware('auth:sanctum')->get('/user/profile', [UserController::class, 'profile']);
Route::get('/image-proxy', function (Request $request) {
    $file = $request->query('file');
    if (!$file) {
        abort(400, 'Missing file parameter');
    }

    // ── Security: Prevent path traversal ──
    $file = ltrim($file, '/');
    if (str_contains($file, '..') || str_contains($file, "\0")) {
        abort(403, 'Invalid file path.');
    }

    // Only allow known image extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'];
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        abort(403, 'File type not allowed.');
    }

    $publicPath = public_path($file);
    $stripped = preg_replace('#^storage/#', '', $file);
    $storagePath = storage_path('app/public/' . $stripped);

    if (File::exists($publicPath)) {
        $path = realpath($publicPath);
    } elseif (File::exists($storagePath)) {
        $path = realpath($storagePath);
    } else {
        abort(404, 'File not found.');
    }

    // Ensure resolved path is within allowed directories
    $allowedBases = [realpath(public_path()), realpath(storage_path('app/public'))];
    $isAllowed = false;
    foreach ($allowedBases as $base) {
        if ($base && str_starts_with($path, $base)) {
            $isAllowed = true;
            break;
        }
    }
    if (!$isAllowed) {
        abort(403, 'Access denied.');
    }

    return response()->file($path, [
        'Content-Type' => File::mimeType($path),
        'Access-Control-Allow-Origin' => '*',
    ]);
});

Route::middleware('auth:sanctum')->get('/user/profile', [UserController::class, 'profile']);

// ── Protected endpoints (require authentication) ──
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);

    // Dashboard & reports
    Route::get('/customer', [MobileDashboardController::class, 'customers']);
    Route::get('/stocks/{stock_category_id}', [MobileDashboardController::class, 'stockByCategory']);
    Route::get('/finance', [MobileDashboardController::class, 'finance']);
    Route::get('/product-count-by-size', [MobileDashboardController::class, 'countBySize']);
    Route::get('/sales-today', [MobileDashboardController::class, 'salesToday']);
    Route::get('/today-production', [MobileDashboardController::class, 'todayProduction']);
    Route::get('/production-standard', [MobileDashboardController::class, 'productStandard']);
    Route::get('/dashboard-manager', [DashboardMobileController::class, 'summary']);

    // Stock management
    Route::post('/stocks/add', [MobileDashboardController::class, 'storeStock']);
    Route::get('/stocks', [MobileStockController::class, 'apiIndex']);
    Route::post('/stocks', [MobileStockController::class, 'apiStore']);
    Route::get('/stock-batches', [MobileStockBatchController::class, 'index']);
    Route::post('/stock-batches/{stock_id}', [MobileStockBatchController::class, 'store']);

    // Product management
    Route::post('/product/store', [ProductVariantController::class, 'store']);

    // Production
    Route::get('/produksi-form', [ProductionController::class, 'apiProduksiForm']);
    Route::get('/productions', [ProductionController::class, 'apiProductions']);
    Route::post('/produksi-store', [ProductionController::class, 'produksiStore']);
    Route::get('/production-filters', [ProductionController::class, 'apiProductionFilters']);

    // Orders
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::post('/cart', [CheckoutController::class, 'sessionCart']);
    Route::get('/orders', [MobileStockController::class, 'OrderData']);
    Route::post('/orders/{id}/update-status', [MobileStockController::class, 'updateStatus']);

    // Customer management
    Route::get('/customer/mobile', [CustomerController::class, 'custMobile']);
    Route::post('/customer', [CustomerController::class, 'store']);
    Route::put('/customer/{id}', [CustomerController::class, 'update']);
    Route::delete('/customer/{id}', [CustomerController::class, 'destroy']);
});