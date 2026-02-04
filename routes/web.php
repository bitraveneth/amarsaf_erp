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
use App\Http\Controllers\Admin\SalaryDistributionController;
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
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeContractController;
use App\Http\Controllers\Admin\EmployeeAllowanceController;
use App\Http\Controllers\Admin\EmployeeEquipmentController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\EmployeeLeaveController;
use App\Http\Controllers\Admin\CustomerReturnController;
use App\Http\Controllers\Admin\SupplierReturnController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\EmployeeLocationController;
use App\Http\Controllers\Admin\CustomerGiftController;
use App\Http\Controllers\Admin\BomController;
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
    Route::view('help', 'admin.help')->name('help');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('products-export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products-price-list', [ProductController::class, 'priceList'])->name('products.prices.index');
    Route::get('products-price-list/{product}', [ProductController::class, 'showPriceList'])->name('products.prices.show');

    Route::get('packaging', [PackagingTypeController::class, 'index'])->name('packaging.index');
    Route::post('packaging', [PackagingTypeController::class, 'store'])->name('packaging.store');
    Route::get('packaging/{packagingType}/edit', [PackagingTypeController::class, 'edit'])->name('packaging.edit');
    Route::patch('packaging/{packagingType}', [PackagingTypeController::class, 'update'])->name('packaging.update');
    Route::delete('packaging/{packagingType}', [PackagingTypeController::class, 'destroy'])->name('packaging.destroy');
    Route::post('packaging/conversions', [PackagingConversionController::class, 'store'])->name('packaging.conversions.store');
    Route::delete('packaging/conversions/{conversion}', [PackagingConversionController::class, 'destroy'])->name('packaging.conversions.destroy');

    Route::get('boms', [BomController::class, 'index'])->name('boms.index');
    Route::get('boms/create', [BomController::class, 'create'])->name('boms.create');
    Route::post('boms', [BomController::class, 'store'])->name('boms.store');
    Route::get('boms/{bom}/edit', [BomController::class, 'edit'])->name('boms.edit');
    Route::patch('boms/{bom}', [BomController::class, 'update'])->name('boms.update');
    Route::delete('boms/{bom}', [BomController::class, 'destroy'])->name('boms.destroy');

    Route::get('tax-classes', [TaxClassController::class, 'index'])->name('tax-classes.index');
    Route::post('tax-classes', [TaxClassController::class, 'store'])->name('tax-classes.store');
    Route::get('tax-classes/{taxClass}/edit', [TaxClassController::class, 'edit'])->name('tax-classes.edit');
    Route::patch('tax-classes/{taxClass}', [TaxClassController::class, 'update'])->name('tax-classes.update');
    Route::delete('tax-classes/{taxClass}', [TaxClassController::class, 'destroy'])->name('tax-classes.destroy');

    Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::patch('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('contracts', [EmployeeContractController::class, 'all'])->name('contracts.index');
    Route::get('contracts/create', [EmployeeContractController::class, 'createGlobal'])->name('contracts.create');
    Route::post('contracts', [EmployeeContractController::class, 'storeGlobal'])->name('contracts.store');
    Route::get('employees/{employee}/contracts', [EmployeeContractController::class, 'index'])->name('employees.contracts.index');
    Route::get('employees/{employee}/contracts/create', [EmployeeContractController::class, 'create'])->name('employees.contracts.create');
    Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])->name('employees.contracts.store');
    Route::get('employees/{employee}/contracts/{contract}/edit', [EmployeeContractController::class, 'edit'])->name('employees.contracts.edit');
    Route::patch('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'update'])->name('employees.contracts.update');
    Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])->name('employees.contracts.destroy');

    Route::get('allowances', [EmployeeAllowanceController::class, 'all'])->name('allowances.index');
    Route::get('allowances/create', [EmployeeAllowanceController::class, 'createGlobal'])->name('allowances.create');
    Route::post('allowances', [EmployeeAllowanceController::class, 'storeGlobal'])->name('allowances.store');
    Route::get('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'index'])->name('employees.allowances.index');
    Route::get('employees/{employee}/allowances/create', [EmployeeAllowanceController::class, 'create'])->name('employees.allowances.create');
    Route::post('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'store'])->name('employees.allowances.store');
    Route::get('employees/{employee}/allowances/{allowance}/edit', [EmployeeAllowanceController::class, 'edit'])->name('employees.allowances.edit');
    Route::patch('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'update'])->name('employees.allowances.update');
    Route::delete('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'destroy'])->name('employees.allowances.destroy');

    Route::get('equipment', [EmployeeEquipmentController::class, 'all'])->name('equipment.index');
    Route::get('equipment/create', [EmployeeEquipmentController::class, 'createGlobal'])->name('equipment.create');
    Route::post('equipment', [EmployeeEquipmentController::class, 'storeGlobal'])->name('equipment.store');
    Route::get('employees/{employee}/equipment', [EmployeeEquipmentController::class, 'index'])->name('employees.equipment.index');
    Route::get('employees/{employee}/equipment/create', [EmployeeEquipmentController::class, 'create'])->name('employees.equipment.create');
    Route::post('employees/{employee}/equipment', [EmployeeEquipmentController::class, 'store'])->name('employees.equipment.store');
    Route::get('employees/{employee}/equipment/{equipment}/edit', [EmployeeEquipmentController::class, 'edit'])->name('employees.equipment.edit');
    Route::patch('employees/{employee}/equipment/{equipment}', [EmployeeEquipmentController::class, 'update'])->name('employees.equipment.update');
    Route::delete('employees/{employee}/equipment/{equipment}', [EmployeeEquipmentController::class, 'destroy'])->name('employees.equipment.destroy');

    Route::get('leaves', [EmployeeLeaveController::class, 'all'])->name('leaves.index');
    Route::get('leaves/create', [EmployeeLeaveController::class, 'createGlobal'])->name('leaves.create');
    Route::post('leaves', [EmployeeLeaveController::class, 'storeGlobal'])->name('leaves.store');
    Route::get('employees/{employee}/leaves', [EmployeeLeaveController::class, 'index'])->name('employees.leaves.index');
    Route::get('employees/{employee}/leaves/create', [EmployeeLeaveController::class, 'create'])->name('employees.leaves.create');
    Route::post('employees/{employee}/leaves', [EmployeeLeaveController::class, 'store'])->name('employees.leaves.store');
    Route::get('employees/{employee}/leaves/{leave}/edit', [EmployeeLeaveController::class, 'edit'])->name('employees.leaves.edit');
    Route::patch('employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'update'])->name('employees.leaves.update');
    Route::delete('employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'destroy'])->name('employees.leaves.destroy');

    Route::get('locations', [EmployeeLocationController::class, 'all'])->name('locations.index');
    Route::get('locations/create', [EmployeeLocationController::class, 'createGlobal'])->name('locations.create');
    Route::post('locations', [EmployeeLocationController::class, 'storeGlobal'])->name('locations.store');
    Route::get('employees/{employee}/locations', [EmployeeLocationController::class, 'index'])->name('employees.locations.index');
    Route::get('employees/{employee}/locations/create', [EmployeeLocationController::class, 'create'])->name('employees.locations.create');
    Route::post('employees/{employee}/locations', [EmployeeLocationController::class, 'store'])->name('employees.locations.store');
    Route::get('employees/{employee}/locations/{location}/edit', [EmployeeLocationController::class, 'edit'])->name('employees.locations.edit');
    Route::patch('employees/{employee}/locations/{location}', [EmployeeLocationController::class, 'update'])->name('employees.locations.update');
    Route::delete('employees/{employee}/locations/{location}', [EmployeeLocationController::class, 'destroy'])->name('employees.locations.destroy');

    Route::get('badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::get('badges/create', [BadgeController::class, 'create'])->name('badges.create');
    Route::post('badges', [BadgeController::class, 'store'])->name('badges.store');
    Route::get('badges/{badge}/edit', [BadgeController::class, 'edit'])->name('badges.edit');
    Route::patch('badges/{badge}', [BadgeController::class, 'update'])->name('badges.update');
    Route::delete('badges/{badge}', [BadgeController::class, 'destroy'])->name('badges.destroy');
    Route::get('badges/{badge}/grant', [BadgeController::class, 'grantFromBadgeForm'])->name('badges.grant-form');
    Route::post('badges/{badge}/grant', [BadgeController::class, 'grantFromBadge'])->name('badges.grant');

    Route::get('employees/{employee}/badges/grant', [BadgeController::class, 'grantForm'])->name('employees.badges.grant-form');
    Route::post('employees/{employee}/badges/grant', [BadgeController::class, 'grant'])->name('employees.badges.grant');

    Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('agents/create', [AgentController::class, 'create'])->name('agents.create');
    Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
    Route::get('agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
    Route::patch('agents/{agent}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('agents/{agent}', [AgentController::class, 'destroy'])->name('agents.destroy');
    Route::get('agents/{agent}/pricing', [AgentPricingController::class, 'edit'])->name('agents.pricing.edit');
    Route::patch('agents/{agent}/pricing', [AgentPricingController::class, 'update'])->name('agents.pricing.update');
    Route::get('agents/{agent}/ledger', [AgentLedgerController::class, 'show'])->name('agents.ledger.show');
    Route::get('commissions', [CommissionReportController::class, 'index'])->name('commissions.index');
    Route::get('commission-rules', [CommissionReportController::class, 'rules'])->name('commissions.rules');
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
    Route::get('deliveries/pod', [DeliveryController::class, 'podIndex'])->name('deliveries.pod-index');
    Route::get('deliveries/packing-slips', [DeliveryController::class, 'packingIndex'])->name('deliveries.packing-index');
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
    Route::get('stock/write-off', [StockMovementController::class, 'writeOffForm'])->name('stock.writeoff');
    Route::post('stock/write-off', [StockMovementController::class, 'writeOffStore'])->name('stock.writeoff.store');
    Route::post('stock/entries/{entry}/write-off', [StockMovementController::class, 'writeOffEntry'])->name('stock.entries.writeoff');
    Route::get('stock/audit', [StockAuditController::class, 'index'])->name('stock.audit');
    Route::post('stock/audit', [StockAuditController::class, 'store'])->name('stock.audit.store');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');

    Route::get('notifications', [AdminController::class, 'notifications'])->name('notifications.index');

    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::patch('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::post('warehouses/{warehouse}/clear-stock', [WarehouseController::class, 'clearStock'])->name('warehouses.clear-stock');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    Route::get('warehouse-locations', [WarehouseLocationController::class, 'index'])->name('warehouse-locations.index');
    Route::post('warehouse-locations', [WarehouseLocationController::class, 'store'])->name('warehouse-locations.store');
    Route::delete('warehouse-locations/{location}', [WarehouseLocationController::class, 'destroy'])->name('warehouse-locations.destroy');

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
    Route::get('reports/agents', [ReportController::class, 'agentPerformance'])->name('reports.agents');
    Route::get('reports/production', [ReportController::class, 'productionSummary'])->name('reports.production');
    Route::get('reports/payroll', [ReportController::class, 'payrollSummary'])->name('reports.payroll');
    Route::get('salary-distributions', [SalaryDistributionController::class, 'index'])->name('salary-distributions.index');
    Route::get('salary-distributions/create', [SalaryDistributionController::class, 'create'])->name('salary-distributions.create');
    Route::post('salary-distributions', [SalaryDistributionController::class, 'store'])->name('salary-distributions.store');
    Route::get('salary-distributions/{salaryDistribution}/edit', [SalaryDistributionController::class, 'edit'])->name('salary-distributions.edit');
    Route::patch('salary-distributions/{salaryDistribution}', [SalaryDistributionController::class, 'update'])->name('salary-distributions.update');
    Route::delete('salary-distributions/{salaryDistribution}', [SalaryDistributionController::class, 'destroy'])->name('salary-distributions.destroy');
    Route::get('finance/{invoice}', [FinanceController::class, 'show'])->name('finance.show');
    Route::delete('finance/{invoice}', [FinanceController::class, 'destroy'])->name('finance.destroy');
    Route::delete('finance/receipts/{receipt}', [FinanceController::class, 'destroyReceipt'])->name('finance.receipts.destroy');
    Route::delete('finance/credit-notes/{creditNote}', [FinanceController::class, 'destroyCreditNote'])->name('finance.credit-notes.destroy');
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
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

    Route::get('returns/customer', [CustomerReturnController::class, 'index'])->name('returns.customer.index');
    Route::get('returns/customer/create', [CustomerReturnController::class, 'create'])->name('returns.customer.create');
    Route::post('returns/customer', [CustomerReturnController::class, 'store'])->name('returns.customer.store');

    Route::get('returns/supplier', [SupplierReturnController::class, 'index'])->name('returns.supplier.index');

    Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::patch('campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');

    Route::get('gifts', [CustomerGiftController::class, 'index'])->name('gifts.index');
    Route::get('gifts/create', [CustomerGiftController::class, 'create'])->name('gifts.create');
    Route::post('gifts', [CustomerGiftController::class, 'store'])->name('gifts.store');
    Route::get('gifts/{gift}/edit', [CustomerGiftController::class, 'edit'])->name('gifts.edit');
    Route::patch('gifts/{gift}', [CustomerGiftController::class, 'update'])->name('gifts.update');
    Route::delete('gifts/{gift}', [CustomerGiftController::class, 'destroy'])->name('gifts.destroy');
});
