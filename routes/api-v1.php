<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Team;
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
    Route::post('/verify-email/send', Auth\SendEmailVerificationLinkController::class)
        ->middleware(['throttle:3,1']);
});

/*
|-------------------------------------------------------------
| User actions.
|-------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/user', [UserController::class, 'index']);

    Route::group([
        'prefix' => 'teams',
    ], function() {
        Route::get('/', [Team\TeamController::class, 'index']);
        Route::get('/{team}', [Team\TeamController::class, 'show'])->can('view', "team");
    });
});
