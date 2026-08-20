<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/otp', [MobileAuthController::class, 'sendOtp']);
    Route::post('/forgot-password/otp', [MobileAuthController::class, 'sendForgotPasswordOtp']);
    Route::post('/forgot-password/reset', [MobileAuthController::class, 'resetPassword']);
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::post('/register/customer', [MobileAuthController::class, 'registerCustomer']);
    Route::post('/register/driver', [MobileAuthController::class, 'registerDriver']);
});

Route::prefix('mobile')->controller(MobileAppController::class)->group(function () {
    Route::get('/services', 'services');
    Route::get('/pricing', 'pricing');
    Route::get('/customers/{user}/bookings', 'customerBookings');
    Route::get('/customers/{user}/money-summary', 'customerMoneySummary');
    Route::post('/customers/{user}/bookings', 'createBooking');
    Route::post('/customers/{user}/bookings/{booking}/pay', 'payBooking');
    Route::post('/customers/{user}/bookings/{booking}/cancel', 'cancelCustomerBooking');
    Route::get('/customers/{user}/bookings/{booking}/history', 'customerHistory');
    Route::post('/customers/{user}/bookings/{booking}/messages', 'customerMessage');
    Route::post('/customers/{user}/bookings/{booking}/rating', 'rateBooking');
    Route::get('/drivers/{user}/jobs/available', 'availableJobs');
    Route::get('/drivers/{user}/jobs', 'driverJobs');
    Route::get('/drivers/{user}/earnings', 'driverEarnings');
    Route::post('/drivers/{user}/availability', 'updateAvailability');
    Route::post('/drivers/{user}/jobs/{booking}/accept', 'acceptJob');
    Route::post('/drivers/{user}/jobs/{booking}/status', 'updateJobStatus');
    Route::post('/drivers/{user}/jobs/{booking}/cancel', 'cancelDriverJob');
    Route::get('/drivers/{user}/jobs/{booking}/history', 'driverHistory');
    Route::post('/drivers/{user}/jobs/{booking}/messages', 'driverMessage');
    Route::post('/users/{user}/profile', 'updateProfile');
    Route::post('/users/{user}/device-token', 'registerDeviceToken');
    Route::post('/users/{user}/password', 'changePassword');
});
