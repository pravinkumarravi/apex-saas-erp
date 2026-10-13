<?php

use App\Http\Controllers\DashboardController;
use Modules\Accounting\Http\Controllers\FinanceController;
use Modules\HumanResources\Http\Controllers\HrController;
use Modules\Inventory\Http\Controllers\InventoryController;
use App\Http\Controllers\LocaleController;
use Modules\Manufacturing\Http\Controllers\ManufacturingController;
use Modules\Procurement\Http\Controllers\ProcurementController;
use Modules\Projects\Http\Controllers\ProjectController;
use Modules\Sales\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Locale Switch Route (RTL / Language)
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// --- DASHBOARD & ANALYTICS ---
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');

// --- PROCUREMENT & SUPPLIERS ---
Route::prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/suppliers', [ProcurementController::class, 'suppliers'])->name('suppliers');
    Route::post('/suppliers', [ProcurementController::class, 'storeSupplier'])->name('suppliers.store');

    Route::get('/purchase-orders', [ProcurementController::class, 'purchaseOrders'])->name('purchase-orders');
    Route::post('/purchase-orders', [ProcurementController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
    Route::post('/purchase-orders/{id}/approve', [ProcurementController::class, 'approvePurchaseOrder'])->name('purchase-orders.approve');

    Route::get('/goods-receipts', [ProcurementController::class, 'goodsReceipts'])->name('goods-receipts');
    Route::post('/goods-receipts', [ProcurementController::class, 'storeGoodsReceipt'])->name('goods-receipts.store');
});

// --- INVENTORY & WAREHOUSES ---
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/products', [InventoryController::class, 'products'])->name('products');
    Route::post('/products', [InventoryController::class, 'storeProduct'])->name('products.store');

    Route::get('/stock-levels', [InventoryController::class, 'stockLevels'])->name('stock-levels');
    Route::post('/adjust-stock', [InventoryController::class, 'adjustStock'])->name('adjust-stock');

    Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->name('stock-movements');

    Route::get('/warehouses', [InventoryController::class, 'warehouses'])->name('warehouses');
    Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->name('warehouses.store');
});

// --- MANUFACTURING & MRP ---
Route::prefix('manufacturing')->name('manufacturing.')->group(function () {
    Route::get('/bom', [ManufacturingController::class, 'bom'])->name('bom');
    Route::post('/bom', [ManufacturingController::class, 'storeBom'])->name('bom.store');

    Route::get('/work-orders', [ManufacturingController::class, 'workOrders'])->name('work-orders');
    Route::post('/work-orders', [ManufacturingController::class, 'storeWorkOrder'])->name('work-orders.store');
    Route::post('/work-orders/{id}/start', [ManufacturingController::class, 'startWorkOrder'])->name('work-orders.start');
    Route::post('/work-orders/{id}/complete', [ManufacturingController::class, 'completeWorkOrder'])->name('work-orders.complete');

    Route::get('/material-consumption', [ManufacturingController::class, 'materialConsumption'])->name('material-consumption');

    Route::get('/quality-control', [ManufacturingController::class, 'qualityControl'])->name('quality-control');
    Route::post('/quality-control', [ManufacturingController::class, 'storeQualityInspection'])->name('quality-control.store');

    Route::get('/work-centers', [ManufacturingController::class, 'workCenters'])->name('work-centers');
    Route::post('/work-centers', [ManufacturingController::class, 'storeWorkCenter'])->name('work-centers.store');
});

// --- CRM & SALES ---
Route::prefix('crm')->name('crm.')->group(function () {
    Route::get('/customers', [SalesController::class, 'customers'])->name('customers');
    Route::post('/customers', [SalesController::class, 'storeCustomer'])->name('customers.store');

    Route::get('/leads', [SalesController::class, 'leads'])->name('leads');
    Route::post('/leads', [SalesController::class, 'storeLead'])->name('leads.store');
});

Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/orders', [SalesController::class, 'salesOrders'])->name('orders');
    Route::post('/orders', [SalesController::class, 'storeSalesOrder'])->name('orders.store');

    Route::get('/delivery-challans', [SalesController::class, 'deliveryChallans'])->name('delivery-challans');
    Route::post('/delivery-challans', [SalesController::class, 'storeDeliveryChallan'])->name('delivery-challans.store');
});

// --- FINANCE & ACCOUNTING ---
Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/chart-of-accounts', [FinanceController::class, 'chartOfAccounts'])->name('chart-of-accounts');
    Route::post('/chart-of-accounts', [FinanceController::class, 'storeAccount'])->name('chart-of-accounts.store');

    Route::get('/journal-entries', [FinanceController::class, 'journalEntries'])->name('journal-entries');
    Route::post('/journal-entries', [FinanceController::class, 'storeJournalEntry'])->name('journal-entries.store');

    Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{id}', [FinanceController::class, 'showInvoice'])->name('invoices.show');

    Route::get('/payments', [FinanceController::class, 'payments'])->name('payments');
    Route::post('/payments', [FinanceController::class, 'storePayment'])->name('payments.store');

    Route::get('/gst-reports', [FinanceController::class, 'gstReports'])->name('gst-reports');
    Route::get('/financial-statements', [FinanceController::class, 'financialStatements'])->name('financial-statements');
});

// --- HR & PAYROLL ---
Route::prefix('hr')->name('hr.')->group(function () {
    Route::get('/employees', [HrController::class, 'employees'])->name('employees');
    Route::post('/employees', [HrController::class, 'storeEmployee'])->name('employees.store');

    Route::get('/attendance', [HrController::class, 'attendance'])->name('attendance');
    Route::post('/attendance', [HrController::class, 'recordAttendance'])->name('attendance.record');

    Route::get('/leaves', [HrController::class, 'leaves'])->name('leaves');
    Route::post('/leaves', [HrController::class, 'storeLeave'])->name('leaves.store');
    Route::post('/leaves/{id}/status', [HrController::class, 'updateLeaveStatus'])->name('leaves.status');

    Route::get('/payroll', [HrController::class, 'payroll'])->name('payroll');
    Route::post('/payroll/process', [HrController::class, 'processPayroll'])->name('payroll.process');
});

// --- PROJECTS & ASSETS ---
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::post('/projects', [ProjectController::class, 'storeProject'])->name('projects.store');
Route::post('/projects/tasks', [ProjectController::class, 'storeTask'])->name('projects.tasks.store');

Route::get('/assets', [ProjectController::class, 'assets'])->name('assets.index');
Route::post('/assets', [ProjectController::class, 'storeAsset'])->name('assets.store');

// --- GOVERNANCE & SETTINGS ---
Route::prefix('governance')->name('governance.')->group(function () {
    Route::get('/approvals', [SettingsController::class, 'approvals'])->name('approvals');
    Route::post('/approvals/{id}/action', [SettingsController::class, 'actionApproval'])->name('approvals.action');

    Route::get('/audit-logs', [SettingsController::class, 'auditLogs'])->name('audit-logs');
});

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/roles', [SettingsController::class, 'roles'])->name('roles');
    Route::post('/roles', [SettingsController::class, 'storeRole'])->name('roles.store');

    Route::get('/tenant', [SettingsController::class, 'tenantSettings'])->name('tenant');
    Route::post('/tenant', [SettingsController::class, 'updateTenantSettings'])->name('tenant.update');

    Route::get('/api-tokens', [SettingsController::class, 'apiTokens'])->name('api-tokens');
    Route::post('/api-tokens', [SettingsController::class, 'createApiToken'])->name('api-tokens.create');

    // Modular Extension & Plugin Management
    Route::get('/modules', [\App\Http\Controllers\ModuleManagementController::class, 'index'])->name('modules.index');
    Route::post('/modules/upload', [\App\Http\Controllers\ModuleManagementController::class, 'upload'])->name('modules.upload');
    Route::post('/modules/{slug}/toggle', [\App\Http\Controllers\ModuleManagementController::class, 'toggle'])->name('modules.toggle');
    Route::delete('/modules/{slug}', [\App\Http\Controllers\ModuleManagementController::class, 'destroy'])->name('modules.destroy');
});

// --- PRESERVED TEMPLATE DEMO PAGES FOR TAILADMIN COMPATIBILITY ---
Route::get('/calendar', fn () => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
Route::get('/profile', fn () => view('pages.profile', ['title' => 'Profile']))->name('profile');
Route::get('/form-elements', fn () => view('pages.form.form-elements', ['title' => 'Form Elements']))->name('form-elements');
Route::get('/basic-tables', fn () => view('pages.tables.basic-tables', ['title' => 'Basic Tables']))->name('basic-tables');
Route::get('/blank', fn () => view('pages.blank', ['title' => 'Blank']))->name('blank');
Route::get('/error-404', fn () => view('pages.errors.error-404', ['title' => 'Error 404']))->name('error-404');
Route::get('/line-chart', fn () => view('pages.chart.line-chart', ['title' => 'Line Chart']))->name('line-chart');
Route::get('/bar-chart', fn () => view('pages.chart.bar-chart', ['title' => 'Bar Chart']))->name('bar-chart');
Route::get('/signin', fn () => view('pages.auth.signin', ['title' => 'Sign In']))->name('signin');
Route::get('/signup', fn () => view('pages.auth.signup', ['title' => 'Sign Up']))->name('signup');
Route::get('/alerts', fn () => view('pages.ui-elements.alerts', ['title' => 'Alerts']))->name('alerts');
Route::get('/avatars', fn () => view('pages.ui-elements.avatars', ['title' => 'Avatars']))->name('avatars');
Route::get('/badge', fn () => view('pages.ui-elements.badges', ['title' => 'Badges']))->name('badge');
Route::get('/buttons', fn () => view('pages.ui-elements.buttons', ['title' => 'Buttons']))->name('buttons');
Route::get('/image', fn () => view('pages.ui-elements.images', ['title' => 'Images']))->name('image');
Route::get('/videos', fn () => view('pages.ui-elements.videos', ['title' => 'Videos']))->name('videos');
