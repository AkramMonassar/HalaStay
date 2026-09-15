<?php

use App\Http\Controllers\Api\V1\AdminCityController;
use App\Http\Controllers\Api\V1\AdminDashboardController;
use App\Http\Controllers\Api\V1\AdminHotelController;
use App\Http\Controllers\Api\V1\AdminPaymentMethodController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\HotelController;
use App\Http\Controllers\Api\V1\OwnerBookingController;
use App\Http\Controllers\Api\V1\OwnerHotelController;
use App\Http\Controllers\Api\V1\OwnerPaymentController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\NotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    // 🌍 Public Data APIs (No Auth Required)
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

    // 🔍 Search & Hotel Details APIs
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/hotels/{hotel}', [HotelController::class, 'show']);
    Route::get('/hotels/{hotel}/rooms', [HotelController::class, 'rooms']);
    Route::get('/hotels/{hotel}/availability', [HotelController::class, 'availability']);

    // 🧾 Booking & Payment APIs (Auth Required)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/user/bookings', [BookingController::class, 'userBookings']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

        Route::post('/payments/manual', [PaymentController::class, 'store']);
        Route::post('/payments/manual/{payment}/receipt', [PaymentController::class, 'uploadReceipt']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    });

    // 🏨 Owner Dashboard APIs (hotel_owner Role Required)
    Route::middleware(['auth:sanctum', 'role:hotel_owner'])->prefix('owner')->group(function () {
        Route::get('/hotels', [OwnerHotelController::class, 'index']);
        Route::post('/hotels', [OwnerHotelController::class, 'store']);
        Route::get('/hotels/{hotel}', [OwnerHotelController::class, 'show']);
        Route::put('/hotels/{hotel}', [OwnerHotelController::class, 'update']);
        Route::post('/hotels/{hotel}/images', [OwnerHotelController::class, 'storeImages']);
        Route::get('/hotels/{hotel}/rooms', [OwnerHotelController::class, 'rooms']);
        Route::post('/hotels/{hotel}/rooms', [OwnerHotelController::class, 'storeRoom']);

        Route::get('/bookings', [OwnerBookingController::class, 'index']);
        Route::post('/bookings/{booking}/confirm', [OwnerBookingController::class, 'confirm']);
        Route::post('/bookings/{booking}/reject', [OwnerBookingController::class, 'reject']);

        Route::get('/payments', [OwnerPaymentController::class, 'index']);
        Route::patch('/payments/{payment}/review', [OwnerPaymentController::class, 'review']);

        Route::put('/hotels/{hotel}/rooms/{type}', [OwnerHotelController::class, 'updateRoom']);
        Route::patch('/hotels/{hotel}/rooms/{type}/toggle', [OwnerHotelController::class, 'toggleRoom']);
    });

    // 👑 Admin Dashboard APIs (admin Role Required)
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('/stats', [AdminDashboardController::class, 'stats']);

        Route::get('/hotels', [AdminHotelController::class, 'index']);
        Route::patch('/hotels/{hotel}/approve', [AdminHotelController::class, 'approve']);
        Route::patch('/hotels/{hotel}/reject', [AdminHotelController::class, 'reject']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);

        Route::get('/bookings', [AdminDashboardController::class, 'bookings']);
        Route::get('/payments', [AdminDashboardController::class, 'payments']);

        Route::get('/cities', [AdminCityController::class, 'index']);
        Route::post('/cities', [AdminCityController::class, 'store']);
        Route::patch('/cities/{city}', [AdminCityController::class, 'update']);

        Route::patch('/payment-methods/{method}/toggle', [AdminPaymentMethodController::class, 'toggle']);
        Route::get('/payment-methods', [AdminPaymentMethodController::class, 'index']);
    });

    // 🔐 Auth APIs
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });
});
