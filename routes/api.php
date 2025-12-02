<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\{MenuController, PaymentLinkController, UserController, UserTypeController};
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::apiResource('payment-links', PaymentLinkController::class);
    Route::apiResource('user-types', UserTypeController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('menus', MenuController::class);
    
    // Get menus for authenticated user based on their user type
    Route::get('/menus/user/my-menus', [MenuController::class, 'getUserMenus']);
});


