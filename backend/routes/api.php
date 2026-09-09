<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\HotelController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    // 🌍 Public Data APIs (No Auth Required)
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

    // 🔍 Search API
    Route::get('/search', [SearchController::class, 'index']);

    // 🏨 Hotel Details & Availability APIs
    Route::get('/hotels/{hotel}', [HotelController::class, 'show']);
    Route::get('/hotels/{hotel}/rooms', [HotelController::class, 'rooms']);
    Route::get('/hotels/{hotel}/availability', [HotelController::class, 'availability']);

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