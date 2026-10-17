<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\LaundryLocationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DriverPayoutController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\LiveOperationController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\LanguageController;
use App\Models\User;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminAuthController::class, 'processLogin'])->name('login.submit');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
Route::post('/admin/services/settings', [ServiceController::class, 'updateSettings'])->name('admin.services.settings');

// TEMP DEV BYPASS - REMOVE BEFORE SUBMISSION
Route::get('/dev-login', function () {
    $user = User::where('EMAIL', 'coolclean@yopmail.com')->first();
    if ($user) {
        Session::put('admin_id', $user->ID);
        Session::put('admin_name', $user->FULL_NAME);
    }
    return redirect()->route('admin.dashboard');
});

Route::post('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/bookings', [BookingController::class, 'index'])->name('admin.bookings');
Route::get('/admin/customers', [CustomerController::class, 'index'])->name('admin.customers');
Route::get('/admin/drivers', [DriverController::class, 'index'])->name('admin.drivers');
Route::post('/admin/drivers/{id}/approve', [DriverController::class, 'approve'])->name('admin.drivers.approve');
Route::post('/admin/drivers/{id}/reject', [DriverController::class, 'reject'])->name('admin.drivers.reject');
Route::get('/admin/live-operations', [LiveOperationController::class, 'index'])->name('admin.live-operations');
Route::get('/admin/services', [ServiceController::class, 'index'])->name('admin.services');
Route::get('/admin/laundry-locations', [LaundryLocationController::class, 'index'])->name('admin.laundry-locations');
Route::get('/admin/payments', [PaymentController::class, 'index'])->name('admin.payments');
Route::get('/admin/driver-payouts', [DriverPayoutController::class, 'index'])->name('admin.driver-payouts');

Route::get('/admin/admin-users', [AdminUserController::class, 'index'])->name('admin.admin-users');
Route::post('/admin/admin-users', [AdminUserController::class, 'store'])->name('admin.admin-users.store');
Route::get('/admin/admin-users/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.admin-users.edit');
Route::post('/admin/admin-users/{id}', [AdminUserController::class, 'update'])->name('admin.admin-users.update');
Route::post('/admin/admin-users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.admin-users.toggle-status');

Route::get('/admin/change-password', [ChangePasswordController::class, 'show'])->name('admin.change-password');
Route::post('/admin/change-password', [ChangePasswordController::class, 'update'])->name('admin.change-password.update');