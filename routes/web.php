<?php

use App\Http\Controllers\Admin\BulkImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\VillaController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware(['auth'])->group(function (): void {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Villas
    Route::resource('villas', VillaController::class);
    Route::patch('/villas/{villa}/status', [VillaController::class, 'updateStatus'])->name('villas.status');

    // Bookings
    Route::resource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/payments', [BookingController::class, 'addPayment'])->name('bookings.payments.store');
    Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');

    // Guests
    Route::resource('guests', GuestController::class);

    // Maintenance & Housekeeping
    Route::resource('maintenance', MaintenanceController::class);
    Route::patch('/maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.status');

    // Expenses & Accounting
    Route::resource('expenses', ExpenseController::class);

    // Admin Only Bulk Import
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
        Route::post('/bulk-import', [BulkImportController::class, 'import'])->name('bulk-import.store');
        Route::get('/bulk-import/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');
        Route::get('/bulk-import/logs/{log}', [BulkImportController::class, 'viewErrors'])->name('bulk-import.errors');
    });
});
