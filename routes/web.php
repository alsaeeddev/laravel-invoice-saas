<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

// Breeze auth routes
require __DIR__.'/auth.php';

// Business setup (authenticated, but no business required)
Route::middleware('auth')->group(function () {
        Route::delete('invoices/bulk-destroy', [InvoiceController::class, 'bulkDestroy'])->name('invoices.bulkDestroy');
    Route::delete('invoices/destroy-all', [InvoiceController::class, 'destroyAll'])->name('invoices.destroyAll');

    Route::get('/business/create', [BusinessController::class, 'create'])->name('business.create');
    Route::post('/business', [BusinessController::class, 'store'])->name('business.store');
});

// All app routes require auth + business
Route::middleware(['auth', 'has.business'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Products
    Route::resource('products', ProductController::class);

    // Invoices + nested payments
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/resend', [InvoiceController::class, 'resend'])->name('invoices.resend');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    Route::resource('invoices.payments', PaymentController::class)
        ->only(['index', 'create', 'store', 'destroy']);

        Route::get('invoices/{invoice}/refunds/create', [PaymentController::class, 'createRefund'])->name('invoices.refunds.create');
    Route::post('invoices/{invoice}/refunds', [PaymentController::class, 'storeRefund'])->name('invoices.refunds.store');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::get('expenses/{expense}/receipt', [ExpenseController::class, 'downloadReceipt'])->name('expenses.receipt');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/logo', [SettingsController::class, 'destroyLogo'])->name('settings.logo.destroy');

    // Staff
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff/invite', [StaffController::class, 'invite'])->name('staff.invite');
    Route::delete('/staff/{user}', [StaffController::class, 'remove'])->name('staff.remove');
});
