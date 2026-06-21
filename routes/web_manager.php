<?php
use App\Http\Controllers\Manager\InventoryController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\Manager\DashboardManager;
use App\Http\Controllers\Manager\Inventory\RecipeController;
use App\Http\Controllers\Manager\Inventory\StockBatchController;
use App\Http\Controllers\Manager\Inventory\SemiFinishedProductController;
use App\Http\Controllers\Manager\Operational\OperationalController;
use App\Http\Controllers\Manager\Operational\SupplierController;
use App\Http\Controllers\Manager\Operational\WasteController;
use App\Http\Controllers\Manager\Operational\StockMovementController;
use App\Http\Controllers\Manager\Operational\StockOpnameController;
use App\Http\Controllers\Manager\Finance\FinanceController;
use App\Http\Controllers\Manager\Finance\rndRequestController;
use App\Http\Controllers\Manager\Finance\RNDLogController;
use App\Http\Controllers\Manager\Operational\rndController;
use App\Http\Controllers\Manager\Operational\OrderController;
use App\Http\Controllers\Manager\Finance\InvoiceController;
use App\Http\Controllers\Manager\Finance\StockBatchesController;
use App\Http\Controllers\Manager\Marketing\CustomerController;
use App\Http\Controllers\Manager\Marketing\MarketingDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Manager\Finance\EmployeeController;
use App\Http\Controllers\Manager\Finance\AccountingController;
use App\Http\Controllers\Manager\Finance\FinanceDashboardController;
use App\Http\Controllers\Manager\Finance\ExpenseController;
use App\Http\Controllers\Manager\Finance\AccountPayableController;
use App\Http\Controllers\Manager\Finance\AccountReceivableController;
use App\Http\Controllers\Manager\Finance\RevenueController;
use App\Http\Controllers\Manager\Finance\ProfitLossController;
use App\Http\Controllers\Manager\Finance\CashflowController;
use App\Http\Controllers\Manager\ImportExportController;
use App\Http\Controllers\Manager\Operational\StockAlertController;
use App\Http\Controllers\Manager\Operational\ShiftController;
use App\Http\Controllers\Manager\Operational\AttendanceController;
use App\Http\Controllers\Manager\Operational\MaintenanceController;
use App\Http\Controllers\Manager\Operational\ProductionPlanController;
use App\Http\Controllers\Manager\Operational\QualityControlController;
use App\Http\Controllers\Manager\Operational\ReturnController;
use App\Http\Controllers\Manager\Operational\OperationalKpiController;

Route::middleware(['web', 'auth', 'role:Manager'])->group(function () {

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
        Route::get('/stock/detail/{type}/{id}', [InventoryController::class, 'stockDetailApi'])->name('manager.inventory.stock.detail-api');
        Route::get('/stock/export', [InventoryController::class, 'exportStock'])->name('manager.inventory.stock.export');
        Route::get('/stock/from-rnd/{id}', [InventoryController::class, 'createBatchFromRnd'])->name('manager.inventory.stock.batch.createFromRnd');

        // Recipes (BOM)
        Route::get('/recipes', [RecipeController::class, 'index'])->name('manager.inventory.recipes.index');
        Route::get('/recipes/create', [RecipeController::class, 'create'])->name('manager.inventory.recipes.create');
        Route::post('/recipes', [RecipeController::class, 'store'])->name('manager.inventory.recipes.store');
        Route::get('/recipes/{product}/edit', [RecipeController::class, 'edit'])->name('manager.inventory.recipes.edit');
        Route::put('/recipes/{product}', [RecipeController::class, 'update'])->name('manager.inventory.recipes.update');

        // RND batch conversion
        Route::get('/stock/rnd/{rnd}/batch/create', [InventoryController::class, 'createBatchFromRnd'])->name('manager.inventory.stock.batch.createFromRnd.rnd');
        Route::post('/stock/rnd/{rnd}/batch/store', [InventoryController::class, 'storeBatchFromRnd'])->name('manager.inventory.stock.batch.storeFromRnd');
        Route::post('/stock/{id}/reduce-expired', [InventoryController::class, 'reduceExpiredStock'])->name('manager.inventory.stock.reduceExpiredStored');
            Route::get('/products/{id}/edit', [InventoryController::class, 'edit'])->name('manager.products.edit');
            Route::put('/products/{id}', [InventoryController::class, 'update'])->name('manager.products.update');
        Route::delete('/recipes/{variant}', [RecipeController::class, 'destroy'])
    ->name('manager.inventory.recipes.destroy');
        Route::delete('/recipes/product/{product}', [RecipeController::class, 'destroyProduct'])
    ->name('manager.inventory.recipes.destroy-product');

        // Semi-Finished Products (Produk Setengah Jadi)
        Route::get('/semi-finished', [SemiFinishedProductController::class, 'index'])->name('manager.inventory.semi-finished.index');
        Route::get('/semi-finished/create', [SemiFinishedProductController::class, 'create'])->name('manager.inventory.semi-finished.create');
        Route::post('/semi-finished', [SemiFinishedProductController::class, 'store'])->name('manager.inventory.semi-finished.store');
        Route::get('/semi-finished/{id}/edit', [SemiFinishedProductController::class, 'edit'])->name('manager.inventory.semi-finished.edit');
        Route::put('/semi-finished/{id}', [SemiFinishedProductController::class, 'update'])->name('manager.inventory.semi-finished.update');
        Route::delete('/semi-finished/{id}', [SemiFinishedProductController::class, 'destroy'])->name('manager.inventory.semi-finished.destroy');
        Route::get('/semi-finished/{id}/produce', [SemiFinishedProductController::class, 'createProduction'])->name('manager.inventory.semi-finished.produce');
        Route::post('/semi-finished/{id}/produce', [SemiFinishedProductController::class, 'storeProduction'])->name('manager.inventory.semi-finished.produce.store');
        Route::get('/semi-finished-production-history', [SemiFinishedProductController::class, 'productionHistory'])->name('manager.inventory.semi-finished.production-history');

            // Handle expired variant (by variant ID)
            Route::post('/products/variant/{id}/discard', [InventoryController::class, 'discardExpiredVariant'])->name('manager.products.variant.discard-variant');
            Route::post('/products/variant/{id}/ignore', [InventoryController::class, 'ignoreExpiredVariant'])->name('manager.products.variant.ignore-variant');

        });


    Route::prefix('operational')->group(function () {
        // ── Operational Dashboard ──
        Route::get('/dashboard', [\App\Http\Controllers\Manager\Operational\OperationalDashboardController::class, 'index'])->name('manager.operational.dashboard');

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
        // ══ Finance Dashboard ══
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('manager.finance.dashboard.index');

        // ══ Revenue ══
        Route::get('/revenue', [RevenueController::class, 'index'])->name('manager.finance.revenue.index');

        // ══ Expenses ══
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('manager.finance.expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('manager.finance.expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('manager.finance.expenses.store');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('manager.finance.expenses.destroy');

        // ══ Profit & Loss ══
        Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('manager.finance.profit-loss.index');

        // ══ Cashflow ══
        Route::get('/cashflow', [CashflowController::class, 'index'])->name('manager.finance.cashflow.index');

        // ══ Accounts Payable ══
        Route::get('/accounts-payable', [AccountPayableController::class, 'index'])->name('manager.finance.ap.index');
        Route::get('/accounts-payable/create', [AccountPayableController::class, 'create'])->name('manager.finance.ap.create');
        Route::post('/accounts-payable', [AccountPayableController::class, 'store'])->name('manager.finance.ap.store');
        Route::post('/accounts-payable/pay/{ap}', [AccountPayableController::class, 'pay'])->name('manager.finance.ap.pay');

        // ══ Accounts Receivable ══
        Route::get('/accounts-receivable', [AccountReceivableController::class, 'index'])->name('manager.finance.ar.index');
        Route::get('/accounts-receivable/create', [AccountReceivableController::class, 'create'])->name('manager.finance.ar.create');
        Route::post('/accounts-receivable', [AccountReceivableController::class, 'store'])->name('manager.finance.ar.store');
        Route::post('/accounts-receivable/pay/{ar}', [AccountReceivableController::class, 'pay'])->name('manager.finance.ar.pay');

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
        // Marketing Dashboard
        Route::get('/dashboard', [MarketingDashboardController::class, 'index'])->name('dashboard');
        Route::get('/customer-analytics', [MarketingDashboardController::class, 'customerAnalytics'])->name('customer-analytics');
        Route::get('/retention', [MarketingDashboardController::class, 'retention'])->name('retention');
        Route::get('/product-performance', [MarketingDashboardController::class, 'productPerformance'])->name('product-performance');
        Route::get('/revenue-analytics', [MarketingDashboardController::class, 'revenueAnalytics'])->name('revenue-analytics');
        Route::get('/campaign-analysis', [MarketingDashboardController::class, 'campaignAnalysis'])->name('campaign-analysis');

        // customer management
        // resource routes will be named customers.* and prefixed by manager.marketing.
        Route::resource('customers', CustomerController::class)->except(['show']);


        // web.php
    });

    // ══════════════════════════════════════════════════
    //  IMPORT / EXPORT ROUTES
    // ══════════════════════════════════════════════════
    Route::prefix('import-export')->name('manager.io.')->group(function () {
        Route::get('/export/{type}', [ImportExportController::class, 'export'])->name('export');
        Route::get('/template/{type}', [ImportExportController::class, 'template'])->name('template');
        Route::post('/import/{type}', [ImportExportController::class, 'import'])->name('import');

        // History
        Route::get('/history', [ImportExportController::class, 'historyPage'])->name('history');
        Route::get('/history/json', [ImportExportController::class, 'historyIndex'])->name('history.json');
        Route::get('/history/{id}/status', [ImportExportController::class, 'historyStatus'])->name('history.status');
        Route::get('/history/{id}/download', [ImportExportController::class, 'historyDownload'])->name('history.download');
        Route::get('/history/{id}/error-log', [ImportExportController::class, 'historyErrorLog'])->name('history.errorlog');
        Route::post('/history/{id}/retry', [ImportExportController::class, 'historyRetry'])->name('history.retry');
    });

    // ══════════════════════════════════════════════════
    //  OPERATIONAL KPI DASHBOARD
    // ══════════════════════════════════════════════════
    Route::get('operational/kpi', [OperationalKpiController::class, 'index'])->name('manager.operational.kpi');

    // ══════════════════════════════════════════════════
    //  STOCK ALERTS & REORDER
    // ══════════════════════════════════════════════════
    Route::prefix('operational/stock-alerts')->name('manager.operational.stock-alerts.')->group(function () {
        Route::get('/', [StockAlertController::class, 'index'])->name('index');
        Route::post('/{id}/acknowledge', [StockAlertController::class, 'acknowledge'])->name('acknowledge');
        Route::get('/reorder-suggestions', [StockAlertController::class, 'reorderSuggestions'])->name('reorder');
        Route::post('/reorder-suggestions/{id}/dismiss', [StockAlertController::class, 'dismissSuggestion'])->name('reorder.dismiss');
    });

    // ══════════════════════════════════════════════════
    //  SHIFT & ATTENDANCE
    // ══════════════════════════════════════════════════
    Route::prefix('operational/shifts')->name('manager.operational.shifts.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::post('/', [ShiftController::class, 'store'])->name('store');
        Route::put('/{id}', [ShiftController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShiftController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('operational/attendance')->name('manager.operational.attendance.')->group(function () {
        Route::get('/schedule', [AttendanceController::class, 'schedule'])->name('schedule');
        Route::post('/schedule', [AttendanceController::class, 'storeSchedule'])->name('schedule.store');
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('clockIn');
        Route::post('/{id}/clock-out', [AttendanceController::class, 'clockOut'])->name('clockOut');
        Route::get('/summary', [AttendanceController::class, 'summary'])->name('summary');
        Route::post('/bulk', [AttendanceController::class, 'bulkRecord'])->name('bulk');
    });

    // ══════════════════════════════════════════════════
    //  PREVENTIVE MAINTENANCE
    // ══════════════════════════════════════════════════
    Route::prefix('operational/maintenance')->name('manager.operational.maintenance.')->group(function () {
        Route::get('/', [MaintenanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/report', [MaintenanceController::class, 'report'])->name('report');

        Route::prefix('equipment')->name('equipment.')->group(function () {
            Route::get('/', [MaintenanceController::class, 'equipmentIndex'])->name('index');
            Route::get('/create', [MaintenanceController::class, 'equipmentCreate'])->name('create');
            Route::post('/', [MaintenanceController::class, 'equipmentStore'])->name('store');
            Route::get('/{id}', [MaintenanceController::class, 'equipmentShow'])->name('show');
            Route::get('/{id}/edit', [MaintenanceController::class, 'equipmentEdit'])->name('edit');
            Route::put('/{id}', [MaintenanceController::class, 'equipmentUpdate'])->name('update');
            Route::delete('/{id}', [MaintenanceController::class, 'equipmentDestroy'])->name('destroy');
        });

        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/{equipmentId}/create', [MaintenanceController::class, 'scheduleCreate'])->name('create');
            Route::post('/', [MaintenanceController::class, 'scheduleStore'])->name('store');
            Route::delete('/{id}', [MaintenanceController::class, 'scheduleDestroy'])->name('destroy');
        });

        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/{equipmentId}/create', [MaintenanceController::class, 'logCreate'])->name('create');
            Route::post('/', [MaintenanceController::class, 'logStore'])->name('store');
        });
    });


    // ══════════════════════════════════════════════════
    //  QUALITY CONTROL
    // ══════════════════════════════════════════════════
    Route::prefix('operational/quality-control')->name('manager.operational.qc.')->group(function () {
        Route::get('/', [QualityControlController::class, 'dashboard'])->name('dashboard');

        Route::prefix('standards')->name('standards.')->group(function () {
            Route::get('/', [QualityControlController::class, 'standards'])->name('index');
            Route::get('/create', [QualityControlController::class, 'createStandard'])->name('create');
            Route::post('/', [QualityControlController::class, 'storeStandard'])->name('store');
            Route::get('/{id}/edit', [QualityControlController::class, 'editStandard'])->name('edit');
            Route::put('/{id}', [QualityControlController::class, 'updateStandard'])->name('update');
            Route::delete('/{id}', [QualityControlController::class, 'destroyStandard'])->name('destroy');
        });

        Route::prefix('inspections')->name('inspections.')->group(function () {
            Route::get('/', [QualityControlController::class, 'inspections'])->name('index');
            Route::get('/create', [QualityControlController::class, 'createInspection'])->name('create');
            Route::post('/', [QualityControlController::class, 'storeInspection'])->name('store');
            Route::get('/{id}', [QualityControlController::class, 'showInspection'])->name('show');
        });

        Route::prefix('non-conformances')->name('nc.')->group(function () {
            Route::get('/', [QualityControlController::class, 'nonConformances'])->name('index');
            Route::get('/create', [QualityControlController::class, 'createNonConformance'])->name('create');
            Route::post('/', [QualityControlController::class, 'storeNonConformance'])->name('store');
            Route::post('/{id}/close', [QualityControlController::class, 'closeNonConformance'])->name('close');
        });
    });

    // ══════════════════════════════════════════════════
    //  RETURN & REFUND (RMA)
    // ══════════════════════════════════════════════════
    Route::prefix('operational/returns')->name('manager.operational.returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::get('/create', [ReturnController::class, 'create'])->name('create');
        Route::post('/', [ReturnController::class, 'store'])->name('store');
        Route::get('/{id}', [ReturnController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [ReturnController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ReturnController::class, 'reject'])->name('reject');
        Route::post('/{id}/process', [ReturnController::class, 'process'])->name('process');
        Route::post('/{id}/complete', [ReturnController::class, 'complete'])->name('complete');
    });

});



