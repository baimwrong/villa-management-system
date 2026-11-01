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
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:10,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware(['auth'])->group(function (): void {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Villas
    Route::resource('villas', VillaController::class)->except(['destroy']);
    Route::delete('/villas/{villa}', [VillaController::class, 'destroy'])->name('villas.destroy')->middleware('role:admin');
    Route::patch('/villas/{villa}/status', [VillaController::class, 'updateStatus'])->name('villas.status');

    // Bookings
    Route::get('/bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
    Route::resource('bookings', BookingController::class)->except(['destroy']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy')->middleware('role:admin');
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/payments', [BookingController::class, 'addPayment'])->name('bookings.payments.store');
    Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::post('/bookings/{booking}/charges', [BookingController::class, 'addCharge'])->name('bookings.charges.store');
    Route::delete('/bookings/{booking}/charges/{charge}', [BookingController::class, 'destroyCharge'])->name('bookings.charges.destroy');

    // Guests
    Route::resource('guests', GuestController::class)->except(['destroy']);
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy')->middleware('role:admin,manager');

    // Maintenance & Housekeeping
    Route::resource('maintenance', MaintenanceController::class);
    Route::patch('/maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.status');

    // Expenses & Accounting (Restricted to Managers & Admins)
    Route::middleware(['role:manager'])->group(function (): void {
        Route::resource('expenses', ExpenseController::class)->except(['destroy']);
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('role:admin');
    });

    // Admin Only Bulk Import
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/bulk-import', [BulkImportController::class, 'index'])->name('bulk-import.index');
        Route::post('/bulk-import', [BulkImportController::class, 'import'])->name('bulk-import.store');
        Route::get('/bulk-import/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('bulk-import.template');
        Route::get('/bulk-import/logs/{log}', [BulkImportController::class, 'viewErrors'])->name('bulk-import.errors');
    });
});
