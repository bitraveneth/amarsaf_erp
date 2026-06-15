<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\DocumentController;
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
use App\Http\Controllers\Admin\FiscalPeriodController;
use App\Http\Controllers\Admin\JournalEntryController;
use App\Http\Controllers\Admin\SalaryDistributionController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierProductCategoryController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\GoodsReceiptController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\KycDocumentTypeController;
use App\Http\Controllers\Admin\MaterialCategoryController;
use App\Http\Controllers\Admin\TaxClassController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\VehicleLoadController;
use App\Http\Controllers\Admin\StockAuditController;
use App\Http\Controllers\Admin\BankReconciliationController;
use App\Http\Controllers\Admin\WarehouseLocationController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\FleetExpenseController;
use App\Http\Controllers\Admin\LogisticsBillController;
use App\Http\Controllers\Admin\LogisticsDashboardController;
use App\Http\Controllers\Admin\LogisticsCarrierController;
use App\Http\Controllers\Admin\CarrierRateCardController;
use App\Http\Controllers\Admin\FleetRecurringChargeController;
use App\Http\Controllers\Admin\VehicleScheduleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeContractController;
use App\Http\Controllers\Admin\EmployeeAllowanceController;
use App\Http\Controllers\Admin\EmployeeEquipmentController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\EmployeeLeaveController;
use App\Http\Controllers\Admin\EmployeeOvertimeController;
use App\Http\Controllers\Admin\CustomerReturnController;
use App\Http\Controllers\Admin\SupplierReturnController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\EmployeeLocationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ErpAssistantController;
use App\Http\Controllers\Admin\ExportCenterController;
use App\Http\Controllers\Admin\LearningHubController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\ModuleExportController;
use App\Http\Controllers\Admin\MonthEndExportController;
use App\Http\Controllers\Admin\MrpController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\TallyExportController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\CustomerGiftController;
use App\Http\Controllers\Admin\BomController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SalesDashboardController;
use App\Http\Controllers\Admin\SalesTargetController;
use App\Http\Controllers\Admin\ManufacturingDashboardController;
use App\Http\Controllers\Admin\AccountingDashboardController;
use App\Http\Controllers\Admin\ReportsDashboardController;
use App\Http\Controllers\Admin\WarehouseDashboardController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\AgentAdvanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

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

Route::post('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'show'])->name('login');
    Route::post('/', [LoginController::class, 'authenticate'])->middleware('throttle:login');
    Route::get('login', fn () => redirect('/'));
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Backward-compatible home URL
Route::get('/home', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('home');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('export-center', ExportCenterController::class)->name('export-center');
    Route::get('assistant/bootstrap', [ErpAssistantController::class, 'bootstrap'])->name('assistant.bootstrap');
    Route::post('assistant/ask', [ErpAssistantController::class, 'ask'])
        ->middleware('throttle:30,1')
        ->name('assistant.ask');
    Route::get('exports/{module}/preview', [ModuleExportController::class, 'preview'])
        ->name('modules.export.preview');
    Route::get('exports/{module}/{format}', ModuleExportController::class)
        ->where('format', 'csv|pdf')
        ->name('modules.export');
    Route::get('documents/{type}/{id}', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('documents/{type}/{id}/pdf', [DocumentController::class, 'pdf'])->name('documents.pdf');
    Route::view('help', 'admin.help')->name('help');
    Route::get('learning-hub', LearningHubController::class)->name('learning-hub');
    Route::view('client-guide', 'admin.client-guide')->name('client-guide');
    Route::get('settings', [SystemSettingController::class, 'index'])->middleware('perm:system.settings')->name('settings.index');
    Route::patch('settings', [SystemSettingController::class, 'update'])->middleware('perm:system.settings')->name('settings.update');
    Route::post('settings/backups', [SystemSettingController::class, 'createBackup'])->middleware('perm:system.settings')->name('settings.backups.create');
    Route::post('settings/backups/import', [SystemSettingController::class, 'importBackup'])->middleware('perm:system.settings')->name('settings.backups.import');
    Route::post('settings/backups/delete', [SystemSettingController::class, 'deleteBackup'])->middleware('perm:system.settings')->name('settings.backups.delete');
    Route::get('settings/backups/{filename}', [SystemSettingController::class, 'downloadBackup'])->middleware('perm:system.settings')->where('filename', '.*')->name('settings.backups.download');
    Route::post('settings/backups/restore', [SystemSettingController::class, 'restoreBackup'])->middleware('perm:system.settings')->name('settings.backups.restore');

    // Sales dashboard
    Route::get('sales-dashboard', SalesDashboardController::class)
        ->middleware('perm:sales.manage')
        ->name('sales.dashboard');

    // Accounting dashboard
    Route::get('accounting-dashboard', AccountingDashboardController::class)
        ->middleware('perm:accounting.manage')
        ->name('accounting.dashboard');

    // Reports dashboard
    Route::get('reports-dashboard', ReportsDashboardController::class)
        ->middleware('perm:reports.view')
        ->name('reports.dashboard');

    // Manufacturing dashboard
    Route::get('manufacturing-dashboard', ManufacturingDashboardController::class)
        ->middleware('perm:manufacturing.manage')
        ->name('manufacturing.dashboard');

    // Warehouse dashboard (Control → Warehouses)
    Route::get('warehouses-dashboard', WarehouseDashboardController::class)
        ->middleware('perm:control.warehouses')
        ->name('warehouses.dashboard');

    // My profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Role manager
    Route::get('roles', [RoleController::class, 'index'])->middleware('perm:roles.manage')->name('roles.index');
    Route::post('roles', [RoleController::class, 'create'])->middleware('perm:roles.manage')->name('roles.create');
    Route::patch('roles/{user}', [RoleController::class, 'update'])->middleware('perm:roles.manage')->name('roles.update');

    // User manager
    Route::get('users', [UserController::class, 'index'])->middleware('perm:system.settings')->name('users.index');
    Route::post('users', [UserController::class, 'store'])->middleware('perm:system.settings')->name('users.store');
    Route::get('users/{user}/access', [UserController::class, 'editAccess'])->middleware('perm:system.settings')->name('users.access.edit');
    Route::patch('users/{user}/access', [UserController::class, 'updateAccess'])->middleware('perm:system.settings')->name('users.access.update');

    // Permission manager (super admin)
    Route::get('permissions', [PermissionController::class, 'index'])->middleware('perm:permissions.manage')->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->middleware('perm:permissions.manage')->name('permissions.store');
    Route::post('permissions/roles', [PermissionController::class, 'updateRoles'])->middleware('perm:permissions.manage')->name('permissions.roles.update');
    Route::post('permissions/roles/{role}', [PermissionController::class, 'updateRole'])->middleware('perm:permissions.manage')->name('permissions.roles.update-single');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('perm:permissions.manage')->name('permissions.destroy');
    Route::post('permissions/groups/rename', [PermissionController::class, 'renameGroup'])->middleware('perm:permissions.manage')->name('permissions.groups.rename');
    Route::post('permissions/groups/delete', [PermissionController::class, 'deleteGroup'])->middleware('perm:permissions.manage')->name('permissions.groups.delete');

    // Menu manager (sidebar groups & items)
    Route::get('menu', [MenuController::class, 'index'])->middleware('perm:permissions.manage')->name('menu.index');
    Route::post('menu/groups', [MenuController::class, 'storeGroup'])->middleware('perm:permissions.manage')->name('menu.groups.store');
    Route::patch('menu/groups/{group}', [MenuController::class, 'updateGroup'])->middleware('perm:permissions.manage')->name('menu.groups.update');
    Route::delete('menu/groups/{group}', [MenuController::class, 'deleteGroup'])->middleware('perm:permissions.manage')->name('menu.groups.delete');
    Route::post('menu/groups/{group}/move', [MenuController::class, 'moveGroup'])->middleware('perm:permissions.manage')->name('menu.groups.move');
    Route::post('menu/items', [MenuController::class, 'storeItem'])->middleware('perm:permissions.manage')->name('menu.items.store');
    Route::patch('menu/items/{item}', [MenuController::class, 'updateItem'])->middleware('perm:permissions.manage')->name('menu.items.update');
    Route::delete('menu/items/{item}', [MenuController::class, 'deleteItem'])->middleware('perm:permissions.manage')->name('menu.items.delete');
    Route::post('menu/items/{item}/move', [MenuController::class, 'moveItem'])->middleware('perm:permissions.manage')->name('menu.items.move');
    Route::post('menu/items/{item}/move-group', [MenuController::class, 'moveItemGroup'])->middleware('perm:permissions.manage')->name('menu.items.move-group');

    // Finished products catalog
    Route::get('products/suggest-sku', [ProductController::class, 'suggestSku'])->middleware('perm:control.products')->name('products.suggest-sku');
    Route::get('products', [ProductController::class, 'index'])->middleware('perm:control.products')->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->middleware('perm:control.products')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->middleware('perm:control.products')->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('perm:control.products')->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->middleware('perm:control.products')->name('products.edit');
    Route::patch('products/{product}', [ProductController::class, 'update'])->middleware('perm:control.products')->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('perm:control.products')->name('products.destroy');
    Route::get('products-export', [ProductController::class, 'export'])->middleware('perm:control.products')->name('products.export');
    Route::get('products-price-list', [ProductController::class, 'priceList'])->middleware('perm:control.products')->name('products.prices.index');
    Route::patch('products-price-list/{product}/prices', [ProductController::class, 'updateProductPrices'])->middleware('perm:control.products')->name('products.prices.update');
    Route::get('products-price-list/{product}', [ProductController::class, 'showPriceList'])->middleware('perm:control.products')->name('products.prices.show');
    Route::patch('products-price-list/overrides/{agentPriceList}', [ProductController::class, 'updatePriceListOverride'])->middleware('perm:control.products')->name('products.prices.overrides.update');
    Route::delete('products-price-list/overrides/{agentPriceList}', [ProductController::class, 'destroyPriceListOverride'])->middleware('perm:control.products')->name('products.prices.overrides.destroy');

    // Materials (raw / service / in‑house) – managed separately but stored in products table
    Route::get('materials', [ProductController::class, 'materialsIndex'])->middleware('perm:control.products')->name('materials.index');
    Route::get('materials/create', [ProductController::class, 'materialsCreate'])->middleware('perm:control.products')->name('materials.create');
    Route::post('materials', [ProductController::class, 'store'])->middleware('perm:control.products')->name('materials.store');
    Route::get('materials/{product}', [ProductController::class, 'materialsShow'])->middleware('perm:control.products')->name('materials.show');
    Route::get('materials/{product}/edit', [ProductController::class, 'materialsEdit'])->middleware('perm:control.products')->name('materials.edit');
    Route::patch('materials/{product}', [ProductController::class, 'update'])->middleware('perm:control.products')->name('materials.update');
    Route::delete('materials/{product}', [ProductController::class, 'destroy'])->middleware('perm:control.products')->name('materials.destroy');

    Route::get('material-categories', [MaterialCategoryController::class, 'index'])->middleware('perm:control.products')->name('material-categories.index');
    Route::post('material-categories', [MaterialCategoryController::class, 'store'])->middleware('perm:control.products')->name('material-categories.store');
    Route::patch('material-categories/{materialCategory}', [MaterialCategoryController::class, 'update'])->middleware('perm:control.products')->name('material-categories.update');
    Route::delete('material-categories/{materialCategory}', [MaterialCategoryController::class, 'destroy'])->middleware('perm:control.products')->name('material-categories.destroy');

    Route::get('units', [\App\Http\Controllers\Admin\UnitOfMeasureController::class, 'index'])->middleware('perm:control.products')->name('units.index');
    Route::post('units', [\App\Http\Controllers\Admin\UnitOfMeasureController::class, 'store'])->middleware('perm:control.products')->name('units.store');
    Route::patch('units/{unit}', [\App\Http\Controllers\Admin\UnitOfMeasureController::class, 'update'])->middleware('perm:control.products')->name('units.update');
    Route::delete('units/{unit}', [\App\Http\Controllers\Admin\UnitOfMeasureController::class, 'destroy'])->middleware('perm:control.products')->name('units.destroy');

    Route::get('packaging', [PackagingTypeController::class, 'index'])->middleware('perm:control.products')->name('packaging.index');
    Route::post('packaging', [PackagingTypeController::class, 'store'])->middleware('perm:control.products')->name('packaging.store');
    Route::get('packaging/{packagingType}', [PackagingTypeController::class, 'show'])->middleware('perm:control.products')->name('packaging.show');
    Route::get('packaging/{packagingType}/edit', [PackagingTypeController::class, 'edit'])->middleware('perm:control.products')->name('packaging.edit');
    Route::patch('packaging/{packagingType}', [PackagingTypeController::class, 'update'])->middleware('perm:control.products')->name('packaging.update');
    Route::delete('packaging/{packagingType}', [PackagingTypeController::class, 'destroy'])->middleware('perm:control.products')->name('packaging.destroy');
    Route::post('packaging/conversions', [PackagingConversionController::class, 'store'])->middleware('perm:control.products')->name('packaging.conversions.store');
    Route::delete('packaging/conversions/{conversion}', [PackagingConversionController::class, 'destroy'])->middleware('perm:control.products')->name('packaging.conversions.destroy');

    Route::get('boms', [BomController::class, 'index'])->middleware('perm:manufacturing.manage')->name('boms.index');
    Route::get('boms/create', [BomController::class, 'create'])->middleware('perm:manufacturing.manage')->name('boms.create');
    Route::post('boms', [BomController::class, 'store'])->middleware('perm:manufacturing.manage')->name('boms.store');
    Route::get('boms/{bom}', [BomController::class, 'show'])->middleware('perm:manufacturing.manage')->name('boms.show');
    Route::get('boms/{bom}/edit', [BomController::class, 'edit'])->middleware('perm:manufacturing.manage')->name('boms.edit');
    Route::patch('boms/{bom}', [BomController::class, 'update'])->middleware('perm:manufacturing.manage')->name('boms.update');
    Route::delete('boms/{bom}', [BomController::class, 'destroy'])->middleware('perm:manufacturing.manage')->name('boms.destroy');

    Route::get('tax-classes', [TaxClassController::class, 'index'])->middleware('perm:control.products')->name('tax-classes.index');
    Route::post('tax-classes', [TaxClassController::class, 'store'])->middleware('perm:control.products')->name('tax-classes.store');
    Route::get('tax-classes/{taxClass}', [TaxClassController::class, 'show'])->middleware('perm:control.products')->name('tax-classes.show');
    Route::get('tax-classes/{taxClass}/edit', [TaxClassController::class, 'edit'])->middleware('perm:control.products')->name('tax-classes.edit');
    Route::patch('tax-classes/{taxClass}', [TaxClassController::class, 'update'])->middleware('perm:control.products')->name('tax-classes.update');
    Route::delete('tax-classes/{taxClass}', [TaxClassController::class, 'destroy'])->middleware('perm:control.products')->name('tax-classes.destroy');

    Route::get('vehicles', [VehicleController::class, 'index'])->middleware('perm:control.warehouses')->name('vehicles.index');
    Route::get('vehicles/create', [VehicleController::class, 'create'])->middleware('perm:control.warehouses')->name('vehicles.create');
    Route::post('vehicles', [VehicleController::class, 'store'])->middleware('perm:control.warehouses')->name('vehicles.store');
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->middleware('perm:control.warehouses')->name('vehicles.show');

    Route::get('logistics', [LogisticsDashboardController::class, 'index'])->middleware('perm:control.warehouses')->name('logistics.dashboard');
    Route::get('logistics/carriers', [LogisticsCarrierController::class, 'index'])->middleware('perm:control.warehouses')->name('logistics.carriers.index');
    Route::get('logistics/carriers/create', [LogisticsCarrierController::class, 'create'])->middleware('perm:control.warehouses')->name('logistics.carriers.create');
    Route::post('logistics/carriers', [LogisticsCarrierController::class, 'store'])->middleware('perm:control.warehouses')->name('logistics.carriers.store');
    Route::get('logistics/carriers/{carrier}/edit', [LogisticsCarrierController::class, 'edit'])->middleware('perm:control.warehouses')->name('logistics.carriers.edit');
    Route::patch('logistics/carriers/{carrier}', [LogisticsCarrierController::class, 'update'])->middleware('perm:control.warehouses')->name('logistics.carriers.update');
    Route::delete('logistics/carriers/{carrier}', [LogisticsCarrierController::class, 'destroy'])->middleware('perm:control.warehouses')->name('logistics.carriers.destroy');
    Route::get('logistics/route-costs', fn () => redirect()->route('admin.reports.route-costs', request()->query()))->middleware('perm:reports.view')->name('logistics.route-costs');

    Route::get('carrier-rate-cards', [CarrierRateCardController::class, 'index'])->middleware('perm:control.warehouses')->name('carrier-rate-cards.index');
    Route::get('carrier-rate-cards/create', [CarrierRateCardController::class, 'create'])->middleware('perm:control.warehouses')->name('carrier-rate-cards.create');
    Route::post('carrier-rate-cards', [CarrierRateCardController::class, 'store'])->middleware('perm:control.warehouses')->name('carrier-rate-cards.store');
    Route::get('carrier-rate-cards/{carrierRateCard}/edit', [CarrierRateCardController::class, 'edit'])->middleware('perm:control.warehouses')->name('carrier-rate-cards.edit');
    Route::patch('carrier-rate-cards/{carrierRateCard}', [CarrierRateCardController::class, 'update'])->middleware('perm:control.warehouses')->name('carrier-rate-cards.update');
    Route::delete('carrier-rate-cards/{carrierRateCard}', [CarrierRateCardController::class, 'destroy'])->middleware('perm:control.warehouses')->name('carrier-rate-cards.destroy');

    Route::get('fleet-recurring', [FleetRecurringChargeController::class, 'index'])->middleware('perm:control.warehouses')->name('fleet-recurring.index');
    Route::get('fleet-recurring/create', [FleetRecurringChargeController::class, 'create'])->middleware('perm:control.warehouses')->name('fleet-recurring.create');
    Route::post('fleet-recurring', [FleetRecurringChargeController::class, 'store'])->middleware('perm:control.warehouses')->name('fleet-recurring.store');
    Route::post('fleet-recurring/generate', [FleetRecurringChargeController::class, 'generate'])->middleware('perm:control.warehouses')->name('fleet-recurring.generate');
    Route::delete('fleet-recurring/{fleetRecurring}', [FleetRecurringChargeController::class, 'destroy'])->middleware('perm:control.warehouses')->name('fleet-recurring.destroy');

    Route::get('fleet-expenses', [FleetExpenseController::class, 'index'])->middleware('perm:control.warehouses')->name('fleet-expenses.index');
    Route::get('fleet-expenses/create', [FleetExpenseController::class, 'create'])->middleware('perm:control.warehouses')->name('fleet-expenses.create');
    Route::post('fleet-expenses', [FleetExpenseController::class, 'store'])->middleware('perm:control.warehouses')->name('fleet-expenses.store');
    Route::get('fleet-expenses/{fleetExpense}/edit', [FleetExpenseController::class, 'edit'])->middleware('perm:control.warehouses')->name('fleet-expenses.edit');
    Route::patch('fleet-expenses/{fleetExpense}', [FleetExpenseController::class, 'update'])->middleware('perm:control.warehouses')->name('fleet-expenses.update');
    Route::delete('fleet-expenses/{fleetExpense}', [FleetExpenseController::class, 'destroy'])->middleware('perm:control.warehouses')->name('fleet-expenses.destroy');

    Route::get('logistics-bills', [LogisticsBillController::class, 'index'])->middleware('perm:control.warehouses')->name('logistics-bills.index');
    Route::get('logistics-bills/create', [LogisticsBillController::class, 'create'])->middleware('perm:control.warehouses')->name('logistics-bills.create');
    Route::post('logistics-bills', [LogisticsBillController::class, 'store'])->middleware('perm:control.warehouses')->name('logistics-bills.store');
    Route::get('logistics-bills/{logisticsBill}', [LogisticsBillController::class, 'show'])->middleware('perm:control.warehouses')->name('logistics-bills.show');
    Route::post('logistics-bills/{logisticsBill}/pay', [LogisticsBillController::class, 'storePayment'])->middleware('perm:control.warehouses')->name('logistics-bills.pay');
    Route::delete('logistics-bills/{logisticsBill}', [LogisticsBillController::class, 'destroy'])->middleware('perm:control.warehouses')->name('logistics-bills.destroy');

    Route::get('employees', [EmployeeController::class, 'index'])->middleware('perm:control.employees')->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->middleware('perm:control.employees')->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->middleware('perm:control.employees')->name('employees.store');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->middleware('perm:control.employees')->name('employees.show');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('perm:control.employees')->name('employees.edit');
    Route::patch('employees/{employee}', [EmployeeController::class, 'update'])->middleware('perm:control.employees')->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.destroy');

    // Create login account for an employee
    Route::get('employees/{employee}/user', [EmployeeController::class, 'createUser'])->middleware(['perm:control.employees', 'perm:system.settings'])->name('employees.user.create');
    Route::post('employees/{employee}/user', [EmployeeController::class, 'storeUser'])->middleware(['perm:control.employees', 'perm:system.settings'])->name('employees.user.store');

    Route::get('contracts', [EmployeeContractController::class, 'all'])->middleware('perm:control.employees')->name('contracts.index');
    Route::get('contracts/create', [EmployeeContractController::class, 'createGlobal'])->middleware('perm:control.employees')->name('contracts.create');
    Route::post('contracts', [EmployeeContractController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('contracts.store');
    Route::get('contracts/{contract}', [EmployeeContractController::class, 'show'])->middleware('perm:control.employees')->name('contracts.show');
    Route::get('employees/{employee}/contracts', [EmployeeContractController::class, 'index'])->middleware('perm:control.employees')->name('employees.contracts.index');
    Route::get('employees/{employee}/contracts/create', [EmployeeContractController::class, 'create'])->middleware('perm:control.employees')->name('employees.contracts.create');
    Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])->middleware('perm:control.employees')->name('employees.contracts.store');
    Route::get('employees/{employee}/contracts/{contract}/edit', [EmployeeContractController::class, 'edit'])->middleware('perm:control.employees')->name('employees.contracts.edit');
    Route::patch('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'update'])->middleware('perm:control.employees')->name('employees.contracts.update');
    Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.contracts.destroy');

    Route::get('allowances', [EmployeeAllowanceController::class, 'all'])->middleware('perm:control.employees')->name('allowances.index');
    Route::get('allowances/create', [EmployeeAllowanceController::class, 'createGlobal'])->middleware('perm:control.employees')->name('allowances.create');
    Route::post('allowances', [EmployeeAllowanceController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('allowances.store');
    Route::get('allowances/{allowance}', [EmployeeAllowanceController::class, 'show'])->middleware('perm:control.employees')->name('allowances.show');
    Route::get('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'index'])->middleware('perm:control.employees')->name('employees.allowances.index');
    Route::get('employees/{employee}/allowances/create', [EmployeeAllowanceController::class, 'create'])->middleware('perm:control.employees')->name('employees.allowances.create');
    Route::post('employees/{employee}/allowances', [EmployeeAllowanceController::class, 'store'])->middleware('perm:control.employees')->name('employees.allowances.store');
    Route::get('employees/{employee}/allowances/{allowance}/edit', [EmployeeAllowanceController::class, 'edit'])->middleware('perm:control.employees')->name('employees.allowances.edit');
    Route::patch('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'update'])->middleware('perm:control.employees')->name('employees.allowances.update');
    Route::delete('employees/{employee}/allowances/{allowance}', [EmployeeAllowanceController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.allowances.destroy');

    Route::get('equipment', [EmployeeEquipmentController::class, 'all'])->middleware('perm:control.employees')->name('equipment.index');
    Route::get('equipment/create', [EmployeeEquipmentController::class, 'createGlobal'])->middleware('perm:control.employees')->name('equipment.create');
    Route::post('equipment', [EmployeeEquipmentController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('equipment.store');
    Route::get('equipment/{equipment}', [EmployeeEquipmentController::class, 'show'])->middleware('perm:control.employees')->name('equipment.show');
    Route::get('employees/{employee}/equipment', [EmployeeEquipmentController::class, 'index'])->middleware('perm:control.employees')->name('employees.equipment.index');
    Route::get('employees/{employee}/equipment/create', [EmployeeEquipmentController::class, 'create'])->middleware('perm:control.employees')->name('employees.equipment.create');
    Route::post('employees/{employee}/equipment', [EmployeeEquipmentController::class, 'store'])->middleware('perm:control.employees')->name('employees.equipment.store');
    Route::get('employees/{employee}/equipment/{equipment}/edit', [EmployeeEquipmentController::class, 'edit'])->middleware('perm:control.employees')->name('employees.equipment.edit');
    Route::patch('employees/{employee}/equipment/{equipment}', [EmployeeEquipmentController::class, 'update'])->middleware('perm:control.employees')->name('employees.equipment.update');
    Route::delete('employees/{employee}/equipment/{equipment}', [EmployeeEquipmentController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.equipment.destroy');

    Route::get('leaves', [EmployeeLeaveController::class, 'all'])->middleware('perm:control.employees')->name('leaves.index');
    Route::get('leaves/create', [EmployeeLeaveController::class, 'createGlobal'])->middleware('perm:control.employees')->name('leaves.create');
    Route::post('leaves', [EmployeeLeaveController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('leaves.store');
    Route::get('employees/{employee}/leaves', [EmployeeLeaveController::class, 'index'])->middleware('perm:control.employees')->name('employees.leaves.index');
    Route::get('employees/{employee}/leaves/create', [EmployeeLeaveController::class, 'create'])->middleware('perm:control.employees')->name('employees.leaves.create');
    Route::post('employees/{employee}/leaves', [EmployeeLeaveController::class, 'store'])->middleware('perm:control.employees')->name('employees.leaves.store');
    Route::get('employees/{employee}/leaves/{leave}/edit', [EmployeeLeaveController::class, 'edit'])->middleware('perm:control.employees')->name('employees.leaves.edit');
    Route::patch('employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'update'])->middleware('perm:control.employees')->name('employees.leaves.update');
    Route::delete('employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.leaves.destroy');

    Route::get('overtime', [EmployeeOvertimeController::class, 'all'])->middleware('perm:control.employees')->name('overtime.index');
    Route::get('overtime/create', [EmployeeOvertimeController::class, 'createGlobal'])->middleware('perm:control.employees')->name('overtime.create');
    Route::post('overtime', [EmployeeOvertimeController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('overtime.store');
    Route::get('employees/{employee}/overtime', [EmployeeOvertimeController::class, 'index'])->middleware('perm:control.employees')->name('employees.overtime.index');
    Route::get('employees/{employee}/overtime/create', [EmployeeOvertimeController::class, 'create'])->middleware('perm:control.employees')->name('employees.overtime.create');
    Route::post('employees/{employee}/overtime', [EmployeeOvertimeController::class, 'store'])->middleware('perm:control.employees')->name('employees.overtime.store');
    Route::get('employees/{employee}/overtime/{overtime}/edit', [EmployeeOvertimeController::class, 'edit'])->middleware('perm:control.employees')->name('employees.overtime.edit');
    Route::patch('employees/{employee}/overtime/{overtime}', [EmployeeOvertimeController::class, 'update'])->middleware('perm:control.employees')->name('employees.overtime.update');
    Route::delete('employees/{employee}/overtime/{overtime}', [EmployeeOvertimeController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.overtime.destroy');

    Route::get('locations', [EmployeeLocationController::class, 'all'])->middleware('perm:control.employees')->name('locations.index');
    Route::get('locations/create', [EmployeeLocationController::class, 'createGlobal'])->middleware('perm:control.employees')->name('locations.create');
    Route::post('locations', [EmployeeLocationController::class, 'storeGlobal'])->middleware('perm:control.employees')->name('locations.store');
    Route::get('employees/{employee}/locations', [EmployeeLocationController::class, 'index'])->middleware('perm:control.employees')->name('employees.locations.index');
    Route::get('employees/{employee}/locations/create', [EmployeeLocationController::class, 'create'])->middleware('perm:control.employees')->name('employees.locations.create');
    Route::post('employees/{employee}/locations', [EmployeeLocationController::class, 'store'])->middleware('perm:control.employees')->name('employees.locations.store');
    Route::get('employees/{employee}/locations/{location}/edit', [EmployeeLocationController::class, 'edit'])->middleware('perm:control.employees')->name('employees.locations.edit');
    Route::patch('employees/{employee}/locations/{location}', [EmployeeLocationController::class, 'update'])->middleware('perm:control.employees')->name('employees.locations.update');
    Route::delete('employees/{employee}/locations/{location}', [EmployeeLocationController::class, 'destroy'])->middleware('perm:control.employees')->name('employees.locations.destroy');

    Route::get('badges', [BadgeController::class, 'index'])->middleware('perm:control.employees')->name('badges.index');
    Route::get('badges/create', [BadgeController::class, 'create'])->middleware('perm:control.employees')->name('badges.create');
    Route::post('badges', [BadgeController::class, 'store'])->middleware('perm:control.employees')->name('badges.store');
    Route::get('badges/{badge}/edit', [BadgeController::class, 'edit'])->middleware('perm:control.employees')->name('badges.edit');
    Route::patch('badges/{badge}', [BadgeController::class, 'update'])->middleware('perm:control.employees')->name('badges.update');
    Route::delete('badges/{badge}', [BadgeController::class, 'destroy'])->middleware('perm:control.employees')->name('badges.destroy');
    Route::get('badges/{badge}/grant', [BadgeController::class, 'grantFromBadgeForm'])->middleware('perm:control.employees')->name('badges.grant-form');
    Route::post('badges/{badge}/grant', [BadgeController::class, 'grantFromBadge'])->middleware('perm:control.employees')->name('badges.grant');

    Route::get('employees/{employee}/badges/grant', [BadgeController::class, 'grantForm'])->middleware('perm:control.employees')->name('employees.badges.grant-form');
    Route::post('employees/{employee}/badges/grant', [BadgeController::class, 'grant'])->middleware('perm:control.employees')->name('employees.badges.grant');

    Route::get('agents', [AgentController::class, 'index'])->middleware('perm:control.agents')->name('agents.index');
    Route::get('agents/create', [AgentController::class, 'create'])->middleware('perm:control.agents')->name('agents.create');
    Route::post('agents', [AgentController::class, 'store'])->middleware('perm:control.agents')->name('agents.store');
    Route::get('agents/{agent}', [AgentController::class, 'show'])->middleware('perm:control.agents')->name('agents.show');
    Route::get('agents/{agent}/edit', [AgentController::class, 'edit'])->middleware('perm:control.agents')->name('agents.edit');
    Route::patch('agents/{agent}', [AgentController::class, 'update'])->middleware('perm:control.agents')->name('agents.update');
    Route::patch('agents/{agent}/commission', [AgentPricingController::class, 'updateCommission'])->middleware('perm:control.agents')->name('agents.commission.update');
    Route::patch('agents/{agent}/credit', [AgentController::class, 'updateCredit'])->middleware('perm:control.agents')->name('agents.credit.update');
    Route::delete('agents/{agent}', [AgentController::class, 'destroy'])->middleware('perm:control.agents')->name('agents.destroy');

    Route::get('kyc-document-types', [KycDocumentTypeController::class, 'index'])->middleware('perm:control.agents')->name('kyc-document-types.index');
    Route::post('kyc-document-types', [KycDocumentTypeController::class, 'store'])->middleware('perm:control.agents')->name('kyc-document-types.store');
    Route::delete('kyc-document-types/{kycDocumentType}', [KycDocumentTypeController::class, 'destroy'])->middleware('perm:control.agents')->name('kyc-document-types.destroy');
    Route::get('agents/{agent}/pricing', [AgentPricingController::class, 'edit'])->middleware('perm:control.agents')->name('agents.pricing.edit');
    Route::get('agents/{agent}/terms', [AgentPricingController::class, 'edit'])->middleware('perm:control.agents')->name('agents.terms.edit');
    Route::patch('agents/{agent}/pricing', [AgentPricingController::class, 'update'])->middleware('perm:control.agents')->name('agents.pricing.update');
    Route::patch('agents/{agent}/terms', [AgentPricingController::class, 'update'])->middleware('perm:control.agents')->name('agents.terms.update');
    Route::get('agents/{agent}/ledger', [AgentLedgerController::class, 'show'])->middleware('perm:control.agents')->name('agents.ledger.show');
    Route::get('sales-targets', [SalesTargetController::class, 'index'])->middleware('perm:sales.manage')->name('sales-targets.index');
    Route::get('sales-targets/create', [SalesTargetController::class, 'create'])->middleware('perm:sales.manage')->name('sales-targets.create');
    Route::post('sales-targets', [SalesTargetController::class, 'store'])->middleware('perm:sales.manage')->name('sales-targets.store');
    Route::get('sales-targets/{salesTarget}/edit', [SalesTargetController::class, 'edit'])->middleware('perm:sales.manage')->name('sales-targets.edit');
    Route::patch('sales-targets/{salesTarget}', [SalesTargetController::class, 'update'])->middleware('perm:sales.manage')->name('sales-targets.update');
    Route::get('commissions', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.commissions', $request->query(), 301))->middleware('perm:control.agents')->name('commissions.index');
    Route::get('commissions/summary', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.commissions', $request->query(), 301))->middleware('perm:control.agents')->name('commissions.summary');
    Route::get('commissions/export', [CommissionReportController::class, 'export'])->middleware('perm:control.agents')->name('commissions.export');
    Route::get('commission-rules', [CommissionReportController::class, 'rules'])->middleware('perm:control.agents')->name('commissions.rules');
    Route::get('settlements', [CommissionSettlementController::class, 'index'])->middleware('perm:control.agents')->name('settlements.index');
    Route::post('settlements/generate', [CommissionSettlementController::class, 'generate'])->middleware('perm:control.agents')->name('settlements.generate');
    Route::patch('settlements/{settlement}', [CommissionSettlementController::class, 'updateStatus'])->middleware('perm:accounting.manage')->name('settlements.update-status');

    Route::get('orders', [OrderController::class, 'index'])->middleware('perm:sales.manage')->name('orders.index');
    Route::get('orders-picking', [OrderController::class, 'pickingOverview'])->middleware('perm:sales.manage')->name('orders.picking-overview');
    Route::get('orders/create', [OrderController::class, 'create'])->middleware('perm:sales.manage')->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->middleware('perm:sales.manage')->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->middleware('perm:sales.manage')->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->middleware('perm:sales.manage')->name('orders.edit');
    Route::patch('orders/{order}', [OrderController::class, 'update'])->middleware('perm:sales.manage')->name('orders.update');
    Route::get('orders/{order}/picking-list', [OrderController::class, 'pickingList'])->middleware('perm:sales.manage')->name('orders.picking-list');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('perm:sales.manage')->name('orders.status.update');
    Route::post('orders/{order}/confirm-pick', [OrderController::class, 'confirmPick'])->middleware('perm:sales.manage')->name('orders.confirm-pick');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->middleware('perm:sales.manage')->name('orders.destroy');
    Route::post('orders/{order}/invoice', [FinanceController::class, 'createFromOrder'])->middleware('perm:sales.manage')->name('orders.invoice');

    Route::get('deliveries', [DeliveryController::class, 'index'])->middleware('perm:control.warehouses')->name('deliveries.index');
    Route::get('deliveries/pod', [DeliveryController::class, 'podIndex'])->middleware('perm:control.warehouses')->name('deliveries.pod-index');
    Route::get('deliveries/packing-slips', [DeliveryController::class, 'packingIndex'])->middleware('perm:control.warehouses')->name('deliveries.packing-index');
    Route::get('deliveries/create', [DeliveryController::class, 'create'])->middleware('perm:control.warehouses')->name('deliveries.create');
    Route::post('deliveries', [DeliveryController::class, 'store'])->middleware('perm:control.warehouses')->name('deliveries.store');
    Route::get('deliveries/{delivery}', [DeliveryController::class, 'show'])->middleware('perm:control.warehouses')->name('deliveries.show');
    Route::get('deliveries/{delivery}/edit', [DeliveryController::class, 'edit'])->middleware('perm:control.warehouses')->name('deliveries.edit');
    Route::post('deliveries/optimize', [DeliveryController::class, 'optimize'])->middleware('perm:control.warehouses')->name('deliveries.optimize');
    Route::patch('deliveries/{delivery}', [DeliveryController::class, 'update'])->middleware('perm:control.warehouses')->name('deliveries.update');
    Route::delete('deliveries/{delivery}', [DeliveryController::class, 'destroy'])->middleware('perm:control.warehouses')->name('deliveries.destroy');
    Route::get('deliveries/{delivery}/packing-slip', [DeliveryController::class, 'packingSlip'])->middleware('perm:control.warehouses')->name('deliveries.packing-slip');
    Route::get('deliveries/{delivery}/pod-pdf', [DeliveryController::class, 'podPdf'])->middleware('perm:control.warehouses')->name('deliveries.pod-pdf');
    Route::get('delivery-routes', [DeliveryRouteController::class, 'index'])->middleware('perm:control.warehouses')->name('delivery-routes.index');
    Route::post('delivery-routes', [DeliveryRouteController::class, 'store'])->middleware('perm:control.warehouses')->name('delivery-routes.store');
    Route::get('vehicle-load', [VehicleLoadController::class, 'index'])->middleware('perm:control.warehouses')->name('vehicle-load.index');
    Route::get('vehicle-schedule', [VehicleScheduleController::class, 'index'])->middleware('perm:control.warehouses')->name('vehicle-schedule.index');
    Route::post('vehicle-schedule', [VehicleScheduleController::class, 'store'])->middleware('perm:control.warehouses')->name('vehicle-schedule.store');

    Route::get('stock/movements', [StockMovementController::class, 'index'])->middleware('perm:inventory.manage')->name('stock.movements');
    Route::get('stock/transfers', [StockMovementController::class, 'create'])->middleware('perm:inventory.manage')->name('stock.transfers');
    Route::post('stock/transfers', [StockMovementController::class, 'store'])->middleware('perm:inventory.manage')->name('stock.transfers.store');
    Route::get('stock/write-off', [StockMovementController::class, 'writeOffForm'])->middleware('perm:inventory.manage')->name('stock.writeoff');
    Route::post('stock/write-off', [StockMovementController::class, 'writeOffStore'])->middleware('perm:inventory.manage')->name('stock.writeoff.store');
    Route::post('stock/entries/{entry}/write-off', [StockMovementController::class, 'writeOffEntry'])->middleware('perm:inventory.manage')->name('stock.entries.writeoff');
    Route::get('stock/audit', [StockAuditController::class, 'index'])->middleware('perm:inventory.manage')->name('stock.audit');
    Route::post('stock/audit', [StockAuditController::class, 'store'])->middleware('perm:inventory.manage')->name('stock.audit.store');
    Route::delete('stock/audit/{audit}', [StockAuditController::class, 'destroy'])->middleware('perm:inventory.manage')->name('stock.audit.destroy');

    Route::get('inventory', [InventoryController::class, 'index'])->middleware('perm:inventory.manage')->name('inventory.index');
    Route::get('inventory/materials', [InventoryController::class, 'materials'])->middleware('perm:inventory.manage')->name('inventory.materials');
    Route::get('inventory/low-stock', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.low-stock', $request->query(), 301))->middleware('perm:inventory.manage')->name('inventory.low-stock');
    Route::get('mrp', [MrpController::class, 'index'])->middleware('perm:inventory.manage')->name('mrp.index');

    Route::get('notifications', [AdminController::class, 'notifications'])->name('notifications.index');
    Route::get('notifications/header-data', [AdminController::class, 'headerNotifications'])->name('notifications.header-data');
    Route::get('notifications/{notification}/open', [AdminController::class, 'openNotification'])->name('notifications.open');
    Route::post('notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    Route::post('notifications/mark-all-unread', [AdminController::class, 'markAllNotificationsUnread'])->name('notifications.mark-all-unread');
    Route::post('notifications/{notification}/mark-read', [AdminController::class, 'markNotificationRead'])->name('notifications.mark-read');
    Route::post('notifications/{notification}/mark-unread', [AdminController::class, 'markNotificationUnread'])->name('notifications.mark-unread');

    Route::get('warehouses', [WarehouseController::class, 'index'])->middleware('perm:control.warehouses')->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->middleware('perm:control.warehouses')->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->middleware('perm:control.warehouses')->name('warehouses.store');
    Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->middleware('perm:control.warehouses')->name('warehouses.show');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->middleware('perm:control.warehouses')->name('warehouses.edit');
    Route::patch('warehouses/{warehouse}', [WarehouseController::class, 'update'])->middleware('perm:control.warehouses')->name('warehouses.update');
    Route::post('warehouses/{warehouse}/clear-stock', [WarehouseController::class, 'clearStock'])->middleware('perm:control.warehouses')->name('warehouses.clear-stock');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->middleware('perm:control.warehouses')->name('warehouses.destroy');
    Route::get('warehouse-locations', [WarehouseLocationController::class, 'index'])->middleware('perm:control.warehouses')->name('warehouse-locations.index');
    Route::post('warehouse-locations', [WarehouseLocationController::class, 'store'])->middleware('perm:control.warehouses')->name('warehouse-locations.store');
    Route::delete('warehouse-locations/{location}', [WarehouseLocationController::class, 'destroy'])->middleware('perm:control.warehouses')->name('warehouse-locations.destroy');

    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('perm:control.suppliers')->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'create'])->middleware('perm:control.suppliers')->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'store'])->middleware('perm:control.suppliers')->name('suppliers.store');
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->middleware('perm:control.suppliers')->name('suppliers.show');
    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('perm:control.suppliers')->name('suppliers.edit');
    Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('perm:control.suppliers')->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('perm:control.suppliers')->name('suppliers.destroy');
    Route::post('suppliers/{supplier}/categories', [SupplierController::class, 'attachCategory'])->middleware('perm:control.suppliers')->name('suppliers.categories.attach');
    Route::delete('suppliers/{supplier}/categories/{supplierProductCategory}', [SupplierController::class, 'detachCategory'])->middleware('perm:control.suppliers')->name('suppliers.categories.detach');
    Route::post('supplier-product-categories', [SupplierProductCategoryController::class, 'store'])->middleware('perm:control.suppliers')->name('supplier-product-categories.store');
    Route::patch('supplier-product-categories/{supplierProductCategory}', [SupplierProductCategoryController::class, 'update'])->middleware('perm:control.suppliers')->name('supplier-product-categories.update');
    Route::delete('supplier-product-categories/{supplierProductCategory}', [SupplierProductCategoryController::class, 'destroy'])->middleware('perm:control.suppliers')->name('supplier-product-categories.destroy');

    Route::get('production', [ProductionController::class, 'index'])->middleware('perm:manufacturing.manage')->name('production.index');
    Route::get('production/create', [ProductionController::class, 'create'])->middleware('perm:manufacturing.manage')->name('production.create');
    Route::post('production', [ProductionController::class, 'store'])->middleware('perm:manufacturing.manage')->name('production.store');
    Route::get('production/pending-receipts', [ProductionController::class, 'pendingReceipts'])->middleware('perm:manufacturing.manage')->name('production.pending-receipts');
    Route::get('production/order-number', [ProductionController::class, 'orderNumber'])->middleware('perm:manufacturing.manage')->name('production.order-number');
    Route::get('production/{production}', [ProductionController::class, 'show'])->middleware('perm:manufacturing.manage')->name('production.show');
    Route::get('production/{production}/edit', [ProductionController::class, 'edit'])->middleware('perm:manufacturing.manage')->name('production.edit');
    Route::patch('production/{production}', [ProductionController::class, 'update'])->middleware('perm:manufacturing.manage')->name('production.update');
    Route::post('production/{production}/qc-review', [ProductionController::class, 'submitQcReview'])->middleware('perm:manufacturing.manage')->name('production.qc-review');
    Route::post('production/{production}/confirm-stock', [ProductionController::class, 'confirmStock'])->middleware('perm:manufacturing.manage')->name('production.confirm-stock');
    Route::delete('production/{production}', [ProductionController::class, 'destroy'])->middleware('perm:manufacturing.manage')->name('production.destroy');

    Route::get('finance', [FinanceController::class, 'index'])->middleware('perm:accounting.manage')->name('finance.index');
    Route::post('finance/{invoice}/receipt', [FinanceController::class, 'storeReceipt'])->middleware('perm:accounting.manage')->name('finance.receipts.store');
    Route::get('finance/{invoice}/credit-note', [FinanceController::class, 'showCreditNoteForm'])->middleware('perm:accounting.manage')->name('finance.credit-notes.create');
    Route::post('finance/{invoice}/credit-note', [FinanceController::class, 'storeCreditNote'])->middleware('perm:accounting.manage')->name('finance.credit-notes.store');
    Route::get('finance/{invoice}/pdf', [FinanceController::class, 'downloadPdf'])->middleware('perm:accounting.manage')->name('finance.pdf');
    Route::get('finance/{invoice}/json', [FinanceController::class, 'exportJson'])->middleware('perm:accounting.manage')->name('finance.json');
    Route::get('finance/{invoice}/e-invoice', [FinanceController::class, 'exportEInvoice'])->middleware('perm:accounting.manage')->name('finance.e-invoice');
    Route::get('finance/reconciliation', [BankReconciliationController::class, 'index'])->middleware('perm:accounting.manage')->name('finance.reconciliation');
    Route::post('finance/reconciliation/import', [BankReconciliationController::class, 'import'])->middleware('perm:accounting.manage')->name('finance.reconciliation.import');
    Route::post('finance/reconciliation', [BankReconciliationController::class, 'update'])->middleware('perm:accounting.manage')->name('finance.reconciliation.update');
    Route::get('reports/income-statement', [ReportController::class, 'profitAndLoss'])->middleware('perm:reports.view')->name('reports.pl');
    Route::get('reports/pl', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.pl', $request->query(), 301));
    Route::get('reports/operations', [ReportController::class, 'operationsHub'])->middleware('perm:reports.view')->name('reports.operations');
    Route::get('reports/accountant', [ReportController::class, 'accountantHub'])->middleware('perm:reports.view')->name('reports.accountant');
    Route::get('reports/costs', [ReportController::class, 'costsHub'])->middleware('perm:reports.view')->name('reports.costs');
    Route::get('reports/logistics', [ReportController::class, 'logisticsHub'])->middleware('perm:reports.view')->name('reports.logistics');
    Route::get('reports/sales', [ReportController::class, 'salesHub'])->middleware('perm:reports.view')->name('reports.sales');
    Route::get('reports/executive', [ReportController::class, 'executiveSummary'])->middleware('perm:reports.view')->name('reports.executive');
    Route::get('reports/expense-summary', [ReportController::class, 'expenseSummary'])->middleware('perm:reports.view')->name('reports.expense-summary');
    Route::get('reports/utilities', [ReportController::class, 'utilitiesReport'])->middleware('perm:reports.view')->name('reports.utilities');
    Route::get('reports/logistics-bills', [ReportController::class, 'logisticsBillsSummary'])->middleware('perm:reports.view')->name('reports.logistics-bills');
    Route::get('reports/route-costs', [ReportController::class, 'routeCosts'])->middleware('perm:reports.view')->name('reports.route-costs');
    Route::get('reports/fleet-expenses', [ReportController::class, 'fleetExpensesReport'])->middleware('perm:reports.view')->name('reports.fleet-expenses');
    Route::get('reports/sales-register', [ReportController::class, 'salesRegister'])->middleware('perm:reports.view')->name('reports.sales-register');
    Route::get('reports/outstanding-invoices', [ReportController::class, 'outstandingInvoicesReport'])->middleware('perm:reports.view')->name('reports.outstanding-invoices');
    Route::get('reports/outstanding-bills', [ReportController::class, 'outstandingBillsReport'])->middleware('perm:reports.view')->name('reports.outstanding-bills');
    Route::get('reports/commissions', [ReportController::class, 'commissionsReport'])->middleware('perm:control.agents')->name('reports.commissions');
    Route::get('reports/sales-targets', [ReportController::class, 'salesTargetsReport'])->middleware('perm:reports.view')->name('reports.sales-targets');
    Route::get('reports/low-stock', [ReportController::class, 'lowStockReport'])->middleware('perm:reports.view')->name('reports.low-stock');
    Route::get('reports/delivery-performance', [ReportController::class, 'deliveryPerformanceReport'])->middleware('perm:reports.view')->name('reports.delivery-performance');
    Route::get('reports/bank-reconciliation', [ReportController::class, 'bankReconciliationReport'])->middleware('perm:reports.view')->name('reports.bank-reconciliation');
    Route::get('reports/customer-statement', [ReportController::class, 'customerStatementReport'])->middleware('perm:reports.view')->name('reports.customer-statement');
    Route::get('reports/supplier-statement', [ReportController::class, 'supplierStatementReport'])->middleware('perm:reports.view')->name('reports.supplier-statement');
    Route::get('reports/journal-register', [ReportController::class, 'journalRegister'])->middleware('perm:reports.view')->name('reports.journal-register');
    Route::get('reports/manufacturing-schedule', [ReportController::class, 'manufacturingSchedule'])->middleware('perm:reports.view')->name('reports.manufacturing-schedule');
    Route::get('reports/trial-balance', [ReportController::class, 'trialBalance'])->middleware('perm:reports.view')->name('reports.trial-balance');
    Route::get('reports/general-ledger', [ReportController::class, 'generalLedger'])->middleware('perm:reports.view')->name('reports.general-ledger');
    Route::get('reports/inventory-valuation', [ReportController::class, 'inventoryValuation'])->middleware('perm:reports.view')->name('reports.inventory-valuation');
    Route::get('reports/ar-aging', [ReportController::class, 'receivableAging'])->middleware('perm:reports.view')->name('reports.ar-aging');
    Route::get('reports/ap-aging', [ReportController::class, 'payableAging'])->middleware('perm:reports.view')->name('reports.ap-aging');
    Route::get('reports/vat-report', [ReportController::class, 'vat'])->middleware('perm:reports.view')->name('reports.vat');
    Route::get('reports/vat', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.vat', $request->query(), 301));
    Route::get('reports/vat/export', [ReportController::class, 'vatExport'])->middleware('perm:reports.view')->name('reports.vat.export');
    Route::get('reports/production-variance', [ReportController::class, 'productionVariance'])->middleware('perm:reports.view')->name('reports.production-variance');
    Route::get('reports/batch-trace', [ReportController::class, 'batchTraceLookup'])->middleware('perm:reports.view')->name('reports.batch-trace');
    Route::get('reports/batch-trace/{batch}', [ReportController::class, 'batchTrace'])->middleware('perm:reports.view')->name('reports.batch-trace.show');
    Route::get('exports/tally', [TallyExportController::class, 'redirect'])->middleware('perm:accounting.manage')->name('exports.tally');
    Route::get('exports/tally/preview', [TallyExportController::class, 'preview'])->middleware('perm:accounting.manage')->name('exports.tally.preview');
    Route::get('exports/tally/download', [TallyExportController::class, 'download'])->middleware('perm:accounting.manage')->name('exports.tally.download');
    Route::get('exports/month-end-pack/download', [MonthEndExportController::class, 'download'])->middleware('perm:reports.view')->name('exports.month-end-pack.download');
    Route::get('search', [SearchController::class, 'index'])->middleware('perm:reports.view')->name('search.index');
    Route::get('search/suggest', [SearchController::class, 'suggest'])->middleware('perm:reports.view')->name('search.suggest');
    Route::get('webhooks', [WebhookController::class, 'index'])->middleware('perm:system.settings')->name('webhooks.index');
    Route::post('webhooks', [WebhookController::class, 'store'])->middleware('perm:system.settings')->name('webhooks.store');
    Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy'])->middleware('perm:system.settings')->name('webhooks.destroy');
    Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet'])->middleware('perm:reports.view')->name('reports.bs');
    Route::get('reports/bs', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.bs', $request->query(), 301));
    Route::get('reports/cash-flow', [ReportController::class, 'cashflow'])->middleware('perm:reports.view')->name('reports.cashflow');
    Route::get('reports/cashflow', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.cashflow', $request->query(), 301));
    Route::get('reports/agent-performance', [ReportController::class, 'agentPerformance'])->middleware('perm:reports.view')->name('reports.agents');
    Route::get('reports/agents', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.agents', $request->query(), 301));
    Route::get('reports/production-summary', [ReportController::class, 'productionSummary'])->middleware('perm:reports.view')->name('reports.production');
    Route::get('reports/production', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.production', $request->query(), 301));
    Route::get('reports/payroll-summary', [ReportController::class, 'payrollSummary'])->middleware('perm:reports.view')->name('reports.payroll');
    Route::get('reports/payroll', fn (\Illuminate\Http\Request $request) => redirect()->route('admin.reports.payroll', $request->query(), 301));
    Route::get('salary-distributions', [SalaryDistributionController::class, 'index'])->middleware('perm:accounting.manage')->name('salary-distributions.index');
    Route::get('salary-distributions/create', [SalaryDistributionController::class, 'create'])->middleware('perm:accounting.manage')->name('salary-distributions.create');
    Route::post('salary-distributions', [SalaryDistributionController::class, 'store'])->middleware('perm:accounting.manage')->name('salary-distributions.store');
    Route::get('salary-distributions/{salaryDistribution}/edit', [SalaryDistributionController::class, 'edit'])->middleware('perm:accounting.manage')->name('salary-distributions.edit');
    Route::patch('salary-distributions/{salaryDistribution}', [SalaryDistributionController::class, 'update'])->middleware('perm:accounting.manage')->name('salary-distributions.update');
    Route::delete('salary-distributions/{salaryDistribution}', [SalaryDistributionController::class, 'destroy'])->middleware('perm:accounting.manage')->name('salary-distributions.destroy');
    Route::get('finance/{invoice}', [FinanceController::class, 'show'])->middleware('perm:accounting.manage')->name('finance.show');
    Route::patch('finance/{invoice}/withholding', [FinanceController::class, 'updateWithholding'])->middleware('perm:accounting.manage')->name('finance.withholding.update');
    Route::delete('finance/{invoice}', [FinanceController::class, 'destroy'])->middleware('perm:accounting.manage')->name('finance.destroy');
    Route::delete('finance/receipts/{receipt}', [FinanceController::class, 'destroyReceipt'])->middleware('perm:accounting.manage')->name('finance.receipts.destroy');
    Route::delete('finance/credit-notes/{creditNote}', [FinanceController::class, 'destroyCreditNote'])->middleware('perm:accounting.manage')->name('finance.credit-notes.destroy');
    Route::post('expenses/sync-ledger', [ExpenseController::class, 'syncLedger'])->middleware('perm:accounting.manage')->name('expenses.sync-ledger');
    Route::get('expenses', [ExpenseController::class, 'index'])->middleware('perm:accounting.manage')->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->middleware('perm:accounting.manage')->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->middleware('perm:accounting.manage')->name('expenses.store');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('perm:accounting.manage')->name('expenses.edit');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->middleware('perm:accounting.manage')->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('perm:accounting.manage')->name('expenses.destroy');
    Route::get('expense-categories', [ExpenseCategoryController::class, 'index'])->middleware('perm:accounting.manage')->name('expense-categories.index');
    Route::patch('expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->middleware('perm:accounting.manage')->name('expense-categories.update');
    Route::get('accounts', [AccountController::class, 'index'])->middleware('perm:accounting.manage')->name('accounts.index');
    Route::get('accounts/create', [AccountController::class, 'create'])->middleware('perm:accounting.manage')->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->middleware('perm:accounting.manage')->name('accounts.store');
    Route::get('accounts/{account}', [AccountController::class, 'show'])->middleware('perm:accounting.manage')->name('accounts.show');
    Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])->middleware('perm:accounting.manage')->name('accounts.edit');
    Route::patch('accounts/{account}', [AccountController::class, 'update'])->middleware('perm:accounting.manage')->name('accounts.update');
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->middleware('perm:accounting.manage')->name('accounts.destroy');

    Route::get('journals', [JournalEntryController::class, 'index'])->middleware('perm:accounting.manage')->name('journals.index');
    Route::get('journals/create', [JournalEntryController::class, 'create'])->middleware('perm:accounting.manage')->name('journals.create');
    Route::post('journals', [JournalEntryController::class, 'store'])->middleware('perm:accounting.manage')->name('journals.store');
    Route::get('journals/{journal}', [JournalEntryController::class, 'show'])->middleware('perm:accounting.manage')->name('journals.show');
    Route::get('journals/{journal}/edit', [JournalEntryController::class, 'edit'])->middleware('perm:accounting.manage')->name('journals.edit');
    Route::patch('journals/{journal}', [JournalEntryController::class, 'update'])->middleware('perm:accounting.manage')->name('journals.update');
    Route::post('journals/{journal}/post', [JournalEntryController::class, 'post'])->middleware('perm:accounting.manage')->name('journals.post');
    Route::post('journals/{journal}/reverse', [JournalEntryController::class, 'reverse'])->middleware('perm:accounting.manage')->name('journals.reverse');

    Route::get('accounting-periods', [FiscalPeriodController::class, 'index'])->middleware('perm:accounting.manage')->name('accounting-periods.index');
    Route::patch('accounting-periods/{period}/close', [FiscalPeriodController::class, 'close'])->middleware('perm:accounting.manage')->name('accounting-periods.close');
    Route::patch('accounting-periods/{period}/open', [FiscalPeriodController::class, 'open'])->middleware('perm:accounting.manage')->name('accounting-periods.open');
    Route::get('agent-advances', [AgentAdvanceController::class, 'index'])->middleware('perm:accounting.manage')->name('agent-advances.index');
    Route::get('agent-advances/create', [AgentAdvanceController::class, 'create'])->middleware('perm:accounting.manage')->name('agent-advances.create');
    Route::post('agent-advances', [AgentAdvanceController::class, 'store'])->middleware('perm:accounting.manage')->name('agent-advances.store');

    Route::get('bills', [PurchaseBillController::class, 'index'])->middleware('perm:accounting.manage')->name('bills.index');
    Route::get('bills/create', [PurchaseBillController::class, 'create'])->middleware('perm:accounting.manage')->name('bills.create');
    Route::post('bills', [PurchaseBillController::class, 'store'])->middleware('perm:accounting.manage')->name('bills.store');
    Route::get('bills/{bill}/edit', [PurchaseBillController::class, 'edit'])->middleware('perm:accounting.manage')->name('bills.edit');
    Route::put('bills/{bill}', [PurchaseBillController::class, 'update'])->middleware('perm:accounting.manage')->name('bills.update');
    Route::delete('bills/{bill}', [PurchaseBillController::class, 'destroy'])->middleware('perm:accounting.manage')->name('bills.destroy');
    Route::post('bills/{bill}/pay', [PurchaseBillController::class, 'storePayment'])->middleware('perm:accounting.manage')->name('bills.pay');
    Route::post('bills/batch-pay', [PurchaseBillController::class, 'storeBatchPayment'])->middleware('perm:accounting.manage')->name('bills.batch-pay');

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('perm:control.suppliers')->name('purchase-orders.index');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->middleware('perm:control.suppliers')->name('purchase-orders.create');
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('perm:control.suppliers')->name('purchase-orders.store');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('perm:control.suppliers')->name('purchase-orders.show');
    Route::get('purchase-orders/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->middleware('perm:control.suppliers')->name('purchase-orders.edit');
    Route::patch('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->middleware('perm:control.suppliers')->name('purchase-orders.update');
    Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->middleware('perm:control.suppliers')->name('purchase-orders.destroy');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->middleware('perm:control.suppliers')->name('purchase-orders.approve');
    Route::get('purchase-orders/{purchaseOrder}/receive', [GoodsReceiptController::class, 'receiveFromPurchaseOrder'])->name('purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/receive', [GoodsReceiptController::class, 'storeFromPurchaseOrder'])->name('purchase-orders.receive.store');

    Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->middleware('perm:inventory.grn.create')->name('goods-receipts.index');
    Route::get('goods-receipts/create', [GoodsReceiptController::class, 'create'])->middleware('perm:inventory.grn.create')->name('goods-receipts.create');
    Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->middleware('perm:inventory.grn.create')->name('goods-receipts.store');
    Route::get('goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->middleware('perm:inventory.grn.create')->name('goods-receipts.show');
    Route::post('goods-receipts/{goodsReceipt}/approve-warehouse', [GoodsReceiptController::class, 'approveWarehouse'])->middleware('perm:inventory.grn.approve.warehouse')->name('goods-receipts.approve-warehouse');
    Route::post('goods-receipts/{goodsReceipt}/approve-procurement', [GoodsReceiptController::class, 'approveProcurement'])->middleware('perm:purchase.grn.approve')->name('goods-receipts.approve-procurement');
    Route::post('goods-receipts/{goodsReceipt}/reverse', [GoodsReceiptController::class, 'reverse'])->middleware('perm:inventory.grn.create')->name('goods-receipts.reverse');

    Route::get('batches', [BatchController::class, 'index'])->middleware('perm:manufacturing.manage')->name('batches.index');
    Route::post('batches', [BatchController::class, 'store'])->middleware('perm:manufacturing.manage')->name('batches.store');
    Route::get('batches/{batch}', [BatchController::class, 'show'])->middleware('perm:manufacturing.manage')->name('batches.show');
    Route::get('batches/{batch}/edit', [BatchController::class, 'edit'])->middleware('perm:manufacturing.manage')->name('batches.edit');
    Route::patch('batches/{batch}', [BatchController::class, 'update'])->middleware('perm:manufacturing.manage')->name('batches.update');
    Route::delete('batches/{batch}', [BatchController::class, 'destroy'])->middleware('perm:manufacturing.manage')->name('batches.destroy');

    Route::get('returns/customer', [CustomerReturnController::class, 'index'])->middleware('perm:sales.manage')->name('returns.customer.index');
    Route::get('returns/customer/create', [CustomerReturnController::class, 'create'])->middleware('perm:sales.manage')->name('returns.customer.create');
    Route::post('returns/customer', [CustomerReturnController::class, 'store'])->middleware('perm:sales.manage')->name('returns.customer.store');

    Route::get('returns/supplier', [SupplierReturnController::class, 'index'])->middleware('perm:control.suppliers')->name('returns.supplier.index');

    Route::get('campaigns', [CampaignController::class, 'index'])->middleware('perm:sales.manage')->name('campaigns.index');
    Route::get('campaigns/create', [CampaignController::class, 'create'])->middleware('perm:sales.manage')->name('campaigns.create');
    Route::post('campaigns', [CampaignController::class, 'store'])->middleware('perm:sales.manage')->name('campaigns.store');
    Route::get('campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->middleware('perm:sales.manage')->name('campaigns.edit');
    Route::patch('campaigns/{campaign}', [CampaignController::class, 'update'])->middleware('perm:sales.manage')->name('campaigns.update');
    Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy'])->middleware('perm:sales.manage')->name('campaigns.destroy');

    Route::get('gifts', [CustomerGiftController::class, 'index'])->middleware('perm:sales.manage')->name('gifts.index');
    Route::get('gifts/create', [CustomerGiftController::class, 'create'])->middleware('perm:sales.manage')->name('gifts.create');
    Route::post('gifts', [CustomerGiftController::class, 'store'])->middleware('perm:sales.manage')->name('gifts.store');
    Route::get('gifts/{gift}/edit', [CustomerGiftController::class, 'edit'])->middleware('perm:sales.manage')->name('gifts.edit');
    Route::patch('gifts/{gift}', [CustomerGiftController::class, 'update'])->middleware('perm:sales.manage')->name('gifts.update');
    Route::delete('gifts/{gift}', [CustomerGiftController::class, 'destroy'])->middleware('perm:sales.manage')->name('gifts.destroy');
});
