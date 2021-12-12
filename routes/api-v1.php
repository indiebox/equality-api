<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('auth')->group(function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', [UserController::class, 'index']);
    // Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke']);
    // Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])->middleware(['signed', 'throttle:6,1']);
    // Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1');
    // Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
});

// Route::get('/reset-password/{token}', [NewPasswordController::class, 'create']);
// Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
// Route::post('/reset-password', [NewPasswordController::class, 'store']);
