<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Team;
use App\Http\Controllers\Api\V1\User;
use App\Models\Invite;

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
    // Authenticated user.
    Route::prefix('user')->group(function() {
        Route::get('/', [User\UserController::class, 'index']);

        // Invites.
        Route::prefix('/invites')->group(function() {
            Route::get('/', [User\InviteController::class, 'index']);
            Route::post('/{pendingInvite}/accept', [User\InviteController::class, 'accept'])->can('accept', 'pendingInvite');
            Route::post('/{pendingInvite}/decline', [User\InviteController::class, 'decline'])->can('decline', 'pendingInvite');
        });
    });

    // Teams.
    Route::prefix('teams')->group(function() {
        Route::get('/', [Team\TeamController::class, 'index']);
        Route::post('/', [Team\TeamController::class, 'store']);
        Route::post('{team}/leave', [Team\TeamController::class, 'leave'])->can('leave', 'team');

        // Invites.
        Route::group([
            'prefix' => '{team}/invites',
        ], function() {
            Route::get('/', [Team\InviteController::class, 'index'])->can('viewAny', [Invite::class, 'team']);
            Route::post('/', [Team\InviteController::class, 'store'])->can('create', [Invite::class, 'team']);
            Route::delete('/{pendingInvite}', [Team\InviteController::class, 'destroy'])->can('delete', ['pendingInvite', 'team']);
        });

        // Update team settings.
        Route::group([
            'prefix' => '{team}',
            'middleware' => 'can:update,team',
        ], function() {
            Route::patch('/', [Team\TeamController::class, 'update']);

            Route::post('/logo', [Team\LogoController::class, 'store']);
            Route::delete('/logo', [Team\LogoController::class, 'destroy']);
        });

        Route::get('/{team}', [Team\TeamController::class, 'show'])->can('view', 'team');
    });
});
