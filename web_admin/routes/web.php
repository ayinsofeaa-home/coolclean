<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');
Route::view('/privacy-policy', 'public.privacy')->name('privacy');
Route::view('/terms-of-use', 'public.terms')->name('terms');
Route::view('/contact-us', 'public.contact')->name('contact');

// Guest routes can only be used before the user logs in.
Route::middleware('guest')->group(function () {
    // GET /login: show the login form.
    Route::get('/login', [LoginController::class, 'create'])->name('login');

    // POST /login: receive and process the submitted form.
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// Auth routes can only be used after the user logs in.
Route::middleware(['auth', 'admin', 'password.changed'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::get('/admin/bookings/{booking}', [AdminController::class, 'booking'])->name('admin.bookings.show');
    Route::get('/admin/customers', [AdminController::class, 'customers'])->name('admin.customers');
    Route::get('/admin/drivers', [AdminController::class, 'drivers'])->name('admin.drivers');
    Route::get('/admin/services', [AdminController::class, 'services'])->name('admin.services');
    Route::get('/admin/laundry-locations', [AdminController::class, 'laundryLocations'])->name('admin.locations');
    Route::get('/admin/payments', [AdminController::class, 'payments'])->name('admin.payments');
    Route::get('/admin/payouts', [AdminController::class, 'payouts'])->name('admin.payouts');
    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/admin/admins', [AdminController::class, 'admins'])->name('admin.admins');
    Route::get('/admin/operations', [AdminController::class, 'operations'])->name('admin.operations');
    Route::post('/admin/operations', [AdminController::class, 'updateOperations'])->name('admin.operations.update');
    Route::post('/admin/drivers/{driver}/approve', [AdminController::class, 'approveDriver'])->name('admin.drivers.approve');
    Route::post('/admin/drivers/{driver}/reject', [AdminController::class, 'rejectDriver'])->name('admin.drivers.reject');
    Route::post('/admin/services/{service}', [AdminController::class, 'updateService'])->name('admin.services.update');
    Route::post('/admin/payouts/drivers/{driver}/mark-paid', [AdminController::class, 'markPayoutPaid'])->name('admin.payouts.paid');
    Route::post('/admin/admins', [AdminController::class, 'createAdmin'])->name('admin.admins.create');
    Route::get('/admin/admins/{admin}/edit', [AdminController::class, 'editAdmin'])->name('admin.admins.edit');
    Route::post('/admin/admins/{admin}/edit', [AdminController::class, 'updateAdmin'])->name('admin.admins.update');
    Route::post('/admin/admins/{admin}/status', [AdminController::class, 'updateAdminStatus'])->name('admin.admins.status');
    Route::get('/admin/change-password', [AdminController::class, 'passwordForm'])->name('admin.password.form');
    Route::post('/admin/change-password', [AdminController::class, 'changePassword'])->name('admin.password.change');

    // Receive the logout request.
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
