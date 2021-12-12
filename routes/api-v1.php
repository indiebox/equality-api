<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('auth')->group(function() {
    Route::post('/register', [Auth\AuthController::class, 'register']);
    Route::post('/login', [Auth\AuthController::class, 'login']);

    Route::middleware('auth')->group(function() {
        Route::post('/logout', [Auth\AuthController::class, 'logout']);
        Route::post('/verify-email/send', [VerifyEmailController::class, 'send']);
    });

    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/user', [UserController::class, 'index']);
});

// Route::get('/reset-password/{token}', [NewPasswordController::class, 'create']);
// Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
// Route::post('/reset-password', [NewPasswordController::class, 'store']);
