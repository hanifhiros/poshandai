<?php
use App\Http\Controllers\Manager\InventoryController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\Manager\DashboardManager;
use App\Http\Controllers\Manager\Inventory\RecipeController;
use App\Http\Controllers\Manager\Inventory\StockBatchController;
use App\Http\Controllers\Manager\Operational\OperationalController;
use App\Http\Controllers\Manager\Operational\SupplierController;
use App\Http\Controllers\Manager\Operational\WasteController;
use App\Http\Controllers\Manager\Operational\StockMovementController;
use App\Http\Controllers\Manager\Operational\StockOpnameController;
use App\Http\Controllers\Reseller\ResellerController;
use App\Http\Controllers\Manager\Finance\FinanceController;
use App\Http\Controllers\Manager\Finance\rndRequestController;
use App\Http\Controllers\Manager\Finance\RNDLogController;
use App\Http\Controllers\Manager\Operational\rndController;
use App\Http\Controllers\Manager\Operational\OrderController;
use App\Http\Controllers\Manager\Finance\InvoiceController;
use App\Http\Controllers\Manager\Finance\StockBatchesController;
use App\Http\Controllers\Manager\Marketing\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Manager\Finance\EmployeeController;
use App\Http\Controllers\Manager\Finance\AccountingController;

Route::middleware(['web', 'auth', 'cekrole:Manager'])->group(function () {

    Route::get('/select-store', [ManagerController::class, 'index'])->name('manager.store');
    Route::post('/set-store', [ManagerController::class, 'setStore'])->name('manager.setstore');
    Route::get('/dashboard', [DashboardManager::class, 'index'])->name('manager.dashboard');

  
    Route::prefix('inventory')->group(function () {
          Route::delete('variants/{id}', [InventoryController::class, 'destroyVariant'])->name('manager.variants.destroy');
        // Products
        Route::get('/products', [InventoryController::class, 'products'])->name('manager.inventory.products');
        Route::get('/products/create', [InventoryController::class, 'create'])->name('manager.products.create');
        Route::post('/products', [InventoryController::class, 'store'])->name('manager.products.store');
        Route::delete('/products/{id}', [InventoryController::class, 'destroy'])->name('manager.products.destroy');
        Route::prefix('products')->name('manager.products.')->group(function () {
            Route::post('/variant/{history}/discard', [InventoryController::class, 'discardExpiredProduction'])->name('variant.discard');
            Route::post('/variant/{history}/ignore', [InventoryController::class, 'ignoreExpiredProduction'])->name('variant.ignore');
        });

        Route::get('stock/{stock}/batches/create', [StockBatchController::class, 'create'])->name('stock.batch.create');
        Route::post('stock/{stock}/batches', [StockBatchController::class, 'store'])->name('stock.batch.store');

        Route::get('/stock-batches', [StockBatchController::class, 'index'])->name('manager.inventory.stock-batches.index');
        Route::get('/stock-batches/create', [InventoryController::class, 'createStock'])->name('manager.inventory.stock-batches.create');
        Route::delete('/stock-batches/{id}', [StockBatchController::class, 'destroy'])->name('manager.inventory.stock-batches.destroy');

        // Stock
        Route::get('/stock', [InventoryController::class, 'stock'])->name('manager.inventory.stock');
        Route::get('/stock/create', [InventoryController::class, 'createStock'])->name('manager.inventory.stock.create');
        Route::post('/stock', [InventoryController::class, 'storeStock'])->name('manager.inventory.stock.store');
        Route::post('/stock/quick-create', [InventoryController::class, 'quickCreateStock'])->name('manager.inventory.stock.quick-create');
        Route::get('/stock/{stock}/edit', [InventoryController::class, 'editStock'])->name('manager.inventory.stock.edit');
        Route::put('/stock/{stock}', [InventoryController::class, 'updateStock'])->name('manager.inventory.stock.update');

        Route::get('/stock/{stock}/batch/create', [InventoryController::class, 'createStockBatch'])->name('manager.inventory.stock.batch.create');
        Route::post('/stock/{stock}/batch/store', [InventoryController::class, 'storeStockBatch'])->name('manager.inventory.stock.batch.store');
        Route::delete('/stock/{id}', [InventoryController::class, 'destroyStock'])->name('manager.inventory.stock.destroy');
        Route::get('/stock/from-rnd/{id}', [InventoryController::class, 'createBatchFromRnd'])->name('manager.inventory.stock.batch.createFromRnd');

        // Recipes (BOM)
        Route::get('/recipes', [RecipeController::class, 'index'])->name('manager.inventory.recipes.index');
        Route::get('/recipes/create', [RecipeController::class, 'create'])->name('manager.inventory.recipes.create');
        Route::post('/recipes', [RecipeController::class, 'store'])->name('manager.inventory.recipes.store');
        Route::get('/recipes/{variant}/edit', [RecipeController::class, 'edit'])->name('manager.inventory.recipes.edit'); // ✅ TAMBAHKAN INI
        Route::put('/recipes/{variant}', [RecipeController::class, 'update'])->name('manager.inventory.recipes.update'); // 

        // RND batch conversion
        Route::get('/stock/rnd/{rnd}/batch/create', [InventoryController::class, 'createBatchFromRnd'])->name('manager.inventory.stock.batch.createFromRnd.rnd');
        Route::post('/stock/rnd/{rnd}/batch/store', [InventoryController::class, 'storeBatchFromRnd'])->name('manager.inventory.stock.batch.storeFromRnd');
        Route::post('/stock/{id}/reduce-expired', [InventoryController::class, 'reduceExpiredStock'])->name('manager.inventory.stock.reduceExpiredStored');
            Route::get('/products/{id}/edit', [InventoryController::class, 'edit'])->name('manager.products.edit');
            Route::put('/products/{id}', [InventoryController::class, 'update'])->name('manager.products.update');
        Route::delete('/recipes/{variant}', [RecipeController::class, 'destroy'])
    ->name('manager.inventory.recipes.destroy');

            // Handle expired variant (by variant ID)
            Route::post('/products/variant/{id}/discard', [InventoryController::class, 'discardExpiredVariant'])->name('manager.products.variant.discard-variant');
            Route::post('/products/variant/{id}/ignore', [InventoryController::class, 'ignoreExpiredVariant'])->name('manager.products.variant.ignore-variant');

        });


    Route::prefix('operational')->group(function () {
        // ── Supplier Management ──
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('manager.operational.suppliers.index');
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('manager.operational.suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('manager.operational.suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('manager.operational.suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('manager.operational.suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('manager.operational.suppliers.destroy');

        // ── Stock Movement Log ──
        Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('manager.operational.stock-movements.index');

        // ── Stock Opname (Adjustment) ──
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('manager.operational.stock-opname.index');
        Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('manager.operational.stock-opname.create');
        Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('manager.operational.stock-opname.store');

        // ── Produksi ──
        Route::get('/produksi', [OperationalController::class, 'produksi'])->name('manager.operational.produksi');
        Route::get('/produksi/create', [OperationalController::class, 'createProduksi'])->name('manager.operational.produksi.create');
        Route::post('/produksi/store', [OperationalController::class, 'produksiStore'])->name('manager.operational.produksi.store');

        // ── Research & Development ──
        Route::get('/rnd', [rndController::class, 'index'])->name('manager.operational.rnd');
        Route::get('/rnd/create', [rndController::class, 'create'])->name('manager.operational.rnd.create');
        Route::post('/rnd/store', [rndController::class, 'store'])->name('manager.operational.rnd.store');
        Route::post('/rnd/{id}/finish', [rndRequestController::class, 'markAsFinished'])->name('manager.rnd.finish');
        Route::delete('/rnd/{id}/delete', [rndController::class, 'destroy'])->name('manager.rnd.delete');

        // ── Waste / Basi ──
        Route::get('/waste', [WasteController::class, 'index'])->name('manager.operational.waste.index');
        Route::get('/waste/create', [WasteController::class, 'create'])->name('manager.operational.waste.create');
        Route::post('/waste', [WasteController::class, 'store'])->name('manager.operational.waste.store');
        Route::delete('/waste/{waste}', [WasteController::class, 'destroy'])->name('manager.operational.waste.destroy');

        // ── Pesanan ──
        Route::get('/orders', [OrderController::class, 'index'])->name('manager.operational.orders.index');
        Route::post('/orders/{id}/mark-shipped', [OrderController::class, 'markAsShipped'])->name('manager.operational.orders.markAsShipped');
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('manager.operational.orders.cancel');
    });
    Route::prefix('finance')->group(function () {
        // ══ Accounting Module ══
        Route::get('/accounting/dashboard', [AccountingController::class, 'dashboard'])->name('manager.finance.accounting.dashboard');
        Route::get('/accounting/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('manager.finance.accounting.coa');
        Route::get('/accounting/journal-entries', [AccountingController::class, 'journalEntries'])->name('manager.finance.accounting.journals');
        Route::get('/accounting/income-statement', [AccountingController::class, 'incomeStatement'])->name('manager.finance.accounting.income');
        Route::get('/accounting/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('manager.finance.accounting.balance');
        Route::get('/accounting/cash-flow', [AccountingController::class, 'cashFlow'])->name('manager.finance.accounting.cashflow');

        Route::get('/rnd-request', [rndRequestController::class, 'index'])->name('finance.rnd-request.index');
        Route::post('/rnd-request/{id}/approveAll', [rndRequestController::class, 'approveAll'])->name('manager.finance.rnd-request.approveAll');
        Route::post('/rnd-request/{id}/rejectAll', [rndRequestController::class, 'rejectAll'])->name('manager.finance.rnd-request.rejectAll');
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('manager.finance.invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('manager.finance.invoices.show');
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('manager.finance.invoices.destroy');
        Route::get('/invoice/{id}/print', [InvoiceController::class, 'print'])->name('manager.finance.invoice.print');
        Route::get('/invoice/{id}/pdf', [InvoiceController::class, 'pdf'])->name('manager.finance.invoice.pdf');
        Route::get('/employees', [EmployeeController::class, 'index'])->name('manager.finance.employees.index');

        Route::get('/stock-batches-finance', [StockBatchesController::class, 'index'])->name('manager.finance.stock-batch-log.index');

    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('manager.finance.employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('manager.finance.employees.store');
        Route::get('/rnd/log', [RNDLogController::class, 'index'])->name('manager.finance.rnd.log');
    });
    Route::prefix('/marketing')->name('manager.marketing.')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        
        // Tambahkan ini:
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');


        // web.php
        // RESLLER MANAGEMENT (Marketing > Resellers)
        Route::get('/resellers', [ResellerController::class, 'index'])->name('resellers.index'); // daftar reseller
        Route::get('/resellers/create', [ResellerController::class, 'create'])->name('resellers.create'); // form tambah baru
        Route::post('/resellers', [ResellerController::class, 'store'])->name('resellers.store'); // simpan reseller baru
        Route::get('/resellers/{reseller}/edit', [ResellerController::class, 'edit'])->name('resellers.edit'); // edit
        Route::put('/resellers/{reseller}', [ResellerController::class, 'update'])->name('resellers.update'); // update
        Route::delete('/resellers/{reseller}', [ResellerController::class, 'destroy'])->name('resellers.destroy'); // hapus

        // Jika ingin reseller yang sudah ada ditambahkan ke store saat ini
        // Route::get('/resellers/attach', [ResellerController::class, 'attachForm'])->name('resellers.attach.form'); // halaman pilih reseller yang sudah ada
        // Route::post('/resellers/attach', [ResellerController::class, 'attach'])->name('resellers.attach'); // proses attach ke store

    });
    
    
   
    
});



