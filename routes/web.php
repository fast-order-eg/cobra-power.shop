<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomsShipmentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected App Routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS Fast Alias
    Route::get('/pos', [InvoiceController::class, 'create'])->name('pos');

    // Products & Inventory
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::resource('products', ProductController::class);

    // Categories
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Customs & Landed Cost Shipments
    Route::post('/customs/{customs}/apply', [CustomsShipmentController::class, 'applyToInventory'])->name('customs.apply');
    Route::resource('customs', CustomsShipmentController::class)->only(['index', 'create', 'store', 'show']);

    // Invoices & Printing
    Route::get('/invoices/{invoice}/print-thermal', [InvoiceController::class, 'printThermal'])->name('invoices.print-thermal');
    Route::get('/invoices/{invoice}/print-a4', [InvoiceController::class, 'printA4'])->name('invoices.print-a4');
    Route::post('/invoices/{invoice}/sync-tax', [InvoiceController::class, 'syncTax'])->name('invoices.sync-tax');
    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);

    // Customers
    Route::resource('customers', \App\Http\Controllers\CustomerController::class)->only(['index', 'store', 'update', 'destroy']);

    // Expenses & Expense Categories
    Route::get('/expenses/export-excel', [ExpenseController::class, 'exportExcel'])->name('expenses.export-excel');
    Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->only(['store', 'update', 'destroy']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

    // Employees & Advances & Payroll
    Route::post('/employees/{employee}/advances', [EmployeeController::class, 'addAdvance'])->name('employees.advances.store');
    Route::post('/employees/{employee}/salary-slip', [EmployeeController::class, 'generateSalarySlip'])->name('employees.salary-slip.store');
    Route::resource('employees', EmployeeController::class)->only(['index', 'store', 'update', 'destroy']);

    // Reports
    Route::get('/reports/sales/export-excel', [ReportController::class, 'exportSalesExcel'])->name('reports.sales.export-excel');
    Route::get('/reports/inventory/export-excel', [ReportController::class, 'exportInventoryExcel'])->name('reports.inventory.export-excel');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

    // Settings
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
