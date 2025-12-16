<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\DeliveryRouteController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PackagingConversionController;
use App\Http\Controllers\Admin\PackagingTypeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AgentPricingController;
use App\Http\Controllers\Admin\AgentLedgerController;
use App\Http\Controllers\Admin\CommissionReportController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CommissionSettlementController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\VehicleLoadController;
use App\Http\Controllers\Admin\StockAuditController;
use App\Http\Controllers\Admin\BankReconciliationController;
use App\Http\Controllers\Admin\WarehouseLocationController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleScheduleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate']);
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('products-export', [ProductController::class, 'export'])->name('products.export');

    Route::get('packaging', [PackagingTypeController::class, 'index'])->name('packaging.index');
    Route::post('packaging', [PackagingTypeController::class, 'store'])->name('packaging.store');
    Route::get('packaging/{packagingType}/edit', [PackagingTypeController::class, 'edit'])->name('packaging.edit');
    Route::patch('packaging/{packagingType}', [PackagingTypeController::class, 'update'])->name('packaging.update');
    Route::delete('packaging/{packagingType}', [PackagingTypeController::class, 'destroy'])->name('packaging.destroy');
    Route::post('packaging/conversions', [PackagingConversionController::class, 'store'])->name('packaging.conversions.store');
    Route::delete('packaging/conversions/{conversion}', [PackagingConversionController::class, 'destroy'])->name('packaging.conversions.destroy');

    Route::get('tax-classes', [TaxClassController::class, 'index'])->name('tax-classes.index');
    Route::post('tax-classes', [TaxClassController::class, 'store'])->name('tax-classes.store');
    Route::get('tax-classes/{taxClass}/edit', [TaxClassController::class, 'edit'])->name('tax-classes.edit');
    Route::patch('tax-classes/{taxClass}', [TaxClassController::class, 'update'])->name('tax-classes.update');
    Route::delete('tax-classes/{taxClass}', [TaxClassController::class, 'destroy'])->name('tax-classes.destroy');

    Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

    Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('agents/create', [AgentController::class, 'create'])->name('agents.create');
    Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
    Route::patch('agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    Route::get('agents/{agent}/pricing', [AgentPricingController::class, 'edit'])->name('agents.pricing.edit');
    Route::patch('agents/{agent}/pricing', [AgentPricingController::class, 'update'])->name('agents.pricing.update');
    Route::get('agents/{agent}/ledger', [AgentLedgerController::class, 'show'])->name('agents.ledger.show');
    Route::get('commissions', [CommissionReportController::class, 'index'])->name('commissions.index');
    Route::get('settlements', [CommissionSettlementController::class, 'index'])->name('settlements.index');
    Route::post('settlements/generate', [CommissionSettlementController::class, 'generate'])->name('settlements.generate');
    Route::patch('settlements/{settlement}', [CommissionSettlementController::class, 'updateStatus'])->name('settlements.update-status');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders-picking', [OrderController::class, 'pickingOverview'])->name('orders.picking-overview');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::get('orders/{order}/picking-list', [OrderController::class, 'pickingList'])->name('orders.picking-list');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('orders/{order}/invoice', [FinanceController::class, 'createFromOrder'])->name('orders.invoice');

    Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::post('deliveries/optimize', [DeliveryController::class, 'optimize'])->name('deliveries.optimize');
    Route::patch('deliveries/{delivery}', [DeliveryController::class, 'update'])->name('deliveries.update');
    Route::delete('deliveries/{delivery}', [DeliveryController::class, 'destroy'])->name('deliveries.destroy');
    Route::get('deliveries/{delivery}/packing-slip', [DeliveryController::class, 'packingSlip'])->name('deliveries.packing-slip');
    Route::get('delivery-routes', [DeliveryRouteController::class, 'index'])->name('delivery-routes.index');
    Route::post('delivery-routes', [DeliveryRouteController::class, 'store'])->name('delivery-routes.store');
    Route::get('vehicle-load', [VehicleLoadController::class, 'index'])->name('vehicle-load.index');
    Route::get('vehicle-schedule', [VehicleScheduleController::class, 'index'])->name('vehicle-schedule.index');
    Route::post('vehicle-schedule', [VehicleScheduleController::class, 'store'])->name('vehicle-schedule.store');

    Route::get('stock/movements', [StockMovementController::class, 'index'])->name('stock.movements');
    Route::get('stock/transfers', [StockMovementController::class, 'create'])->name('stock.transfers');
    Route::post('stock/transfers', [StockMovementController::class, 'store'])->name('stock.transfers.store');
    Route::get('stock/audit', [StockAuditController::class, 'index'])->name('stock.audit');
    Route::post('stock/audit', [StockAuditController::class, 'store'])->name('stock.audit.store');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');

    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::patch('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::post('warehouses/{warehouse}/clear-stock', [WarehouseController::class, 'clearStock'])->name('warehouses.clear-stock');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    Route::get('warehouse-locations', [WarehouseLocationController::class, 'index'])->name('warehouse-locations.index');
    Route::post('warehouse-locations', [WarehouseLocationController::class, 'store'])->name('warehouse-locations.store');

    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::get('production', [ProductionController::class, 'index'])->name('production.index');
    Route::get('production/create', [ProductionController::class, 'create'])->name('production.create');
    Route::post('production', [ProductionController::class, 'store'])->name('production.store');
    Route::get('production/{production}/edit', [ProductionController::class, 'edit'])->name('production.edit');
    Route::patch('production/{production}', [ProductionController::class, 'update'])->name('production.update');
    Route::delete('production/{production}', [ProductionController::class, 'destroy'])->name('production.destroy');

    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('finance/{invoice}/receipt', [FinanceController::class, 'storeReceipt'])->name('finance.receipts.store');
    Route::get('finance/{invoice}/credit-note', [FinanceController::class, 'showCreditNoteForm'])->name('finance.credit-notes.create');
    Route::post('finance/{invoice}/credit-note', [FinanceController::class, 'storeCreditNote'])->name('finance.credit-notes.store');
    Route::get('finance/reconciliation', [BankReconciliationController::class, 'index'])->name('finance.reconciliation');
    Route::post('finance/reconciliation', [BankReconciliationController::class, 'update'])->name('finance.reconciliation.update');
    Route::get('reports/pl', [ReportController::class, 'profitAndLoss'])->name('reports.pl');
    Route::get('reports/vat', [ReportController::class, 'vat'])->name('reports.vat');
    Route::get('reports/bs', [ReportController::class, 'balanceSheet'])->name('reports.bs');
    Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
    Route::get('finance/{invoice}', [FinanceController::class, 'show'])->name('finance.show');
    Route::delete('finance/{invoice}', [FinanceController::class, 'destroy'])->name('finance.destroy');
    Route::delete('finance/receipts/{receipt}', [FinanceController::class, 'destroyReceipt'])->name('finance.receipts.destroy');
    Route::delete('finance/credit-notes/{creditNote}', [FinanceController::class, 'destroyCreditNote'])->name('finance.credit-notes.destroy');
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::patch('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('bills', [PurchaseBillController::class, 'index'])->name('bills.index');
    Route::get('bills/create', [PurchaseBillController::class, 'create'])->name('bills.create');
    Route::post('bills', [PurchaseBillController::class, 'store'])->name('bills.store');
    Route::post('bills/{bill}/pay', [PurchaseBillController::class, 'storePayment'])->name('bills.pay');

    Route::get('batches', [BatchController::class, 'index'])->name('batches.index');
    Route::post('batches', [BatchController::class, 'store'])->name('batches.store');
    Route::get('batches/{batch}/edit', [BatchController::class, 'edit'])->name('batches.edit');
    Route::patch('batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
});
