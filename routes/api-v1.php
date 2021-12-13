<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\UserController;

/*
|-------------------------------------------------------------
| Authorization, email-verification, etc.
|-------------------------------------------------------------
*/

Route::post('/register', [Auth\AuthController::class, 'register']);
Route::post('/login', [Auth\AuthController::class, 'login']);
Route::middleware('auth')->group(function() {
    Route::post('/logout', [Auth\AuthController::class, 'logout']);
    Route::post('/verify-email/send', [VerifyEmailController::class, 'send'])
        ->middleware(['throttle:3,1']);
});
Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

/*
|-------------------------------------------------------------
| User actions.
|-------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/user', [UserController::class, 'index']);
});

// Route::get('/reset-password/{token}', [NewPasswordController::class, 'create']);
// Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
// Route::post('/reset-password', [NewPasswordController::class, 'store']);
