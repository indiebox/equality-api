<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\UserController;

/*
|-------------------------------------------------------------
| Authorization, email-verification, etc.
|-------------------------------------------------------------
*/

Route::post('/register', [Auth\AuthController::class, 'register']);
Route::post('/login', [Auth\AuthController::class, 'login']);
Route::post('/forgot-password', [Auth\ResetPasswordController::class, 'send']);
Route::post('/reset-password', [Auth\ResetPasswordController::class, 'reset']);
Route::middleware('auth')->group(function() {
    Route::post('/logout', [Auth\AuthController::class, 'logout']);
    Route::post('/verify-email/send', [Auth\VerifyEmailController::class, 'send'])
        ->middleware(['throttle:3,1']);
});
Route::get('/verify-email/{id}/{hash}', [Auth\VerifyEmailController::class, 'verify'])
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
