<?php

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Project;
use App\Http\Controllers\Api\V1\Team;
use App\Http\Controllers\Api\V1\User;
use App\Models\Board;
use App\Models\Invite;
use App\Models\LeaderNomination;
use Illuminate\Support\Facades\Route;

/*
|-------------------------------------------------------------
| Authorization, email-verification, etc.
|-------------------------------------------------------------
*/

Route::post('/login', [Auth\AuthController::class, 'login']);
Route::post('/forgot-password', [Auth\ResetPasswordController::class, 'send']);
Route::post('/reset-password', [Auth\ResetPasswordController::class, 'reset']);
Route::middleware('auth')->group(function () {
    Route::post('/logout', [Auth\AuthController::class, 'logout']);
    Route::post('/verify-email/send', Auth\SendEmailVerificationLinkController::class)
        ->middleware(['throttle:mail_verification']);
});

/*
|-------------------------------------------------------------
| Base api.
|-------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    /*
    |-------------------------------------------------------------
    | User actions.
    |-------------------------------------------------------------
    */

    Route::prefix('user')->group(function () {
        Route::get('/', [User\UserController::class, 'index'])->withoutMiddleware('verified');
    });

    /*
    |-------------------------------------------------------------
    | Teams actions.
    |-------------------------------------------------------------
    */

    Route::prefix('teams')->group(function () {
        Route::get('/', [Team\TeamController::class, 'index']);
        Route::post('/', [Team\TeamController::class, 'store']);
        Route::post('{team}/leave', [Team\TeamController::class, 'leave'])->can('leave', 'team');

        // Projects.
        Route::group([
            'prefix' => '{team}/projects',
        ], function () {
            Route::get('/', [Team\ProjectController::class, 'index'])->can('viewAny', [Project::class, 'team']);
            Route::post('/', [Team\ProjectController::class, 'store'])->can('create', [Project::class, 'team']);
        });

        // Invites.
        Route::group([
            'prefix' => '{team}/invites',
        ], function () {
            Route::get('/', [Team\InviteController::class, 'index'])->can('viewAny', [Invite::class, 'team']);
            Route::post('/', [Team\InviteController::class, 'store'])->can('create', [Invite::class, 'team']);
        });

        // Update team settings.
        Route::group([
            'prefix' => '{team}',
            'middleware' => 'can:update,team',
        ], function () {
            Route::patch('/', [Team\TeamController::class, 'update']);

            Route::post('/logo', [Team\LogoController::class, 'store']);
            Route::delete('/logo', [Team\LogoController::class, 'destroy']);
        });

        Route::get('/{team}/members', [Team\TeamController::class, 'members'])->can('view', 'team');
        Route::get('/{team}', [Team\TeamController::class, 'show'])->can('view', 'team');
    });

    /*
    |-------------------------------------------------------------
    | Invites actions.
    |-------------------------------------------------------------
    */

    Route::prefix('invites')->group(function () {
        Route::get('/', [User\InviteController::class, 'index']);

        Route::prefix('/{pendingInvite}')->group(function () {
            Route::post('/accept', [User\InviteController::class, 'accept'])->can('accept', 'pendingInvite');
            Route::post('/decline', [User\InviteController::class, 'decline'])->can('decline', 'pendingInvite');
            Route::delete('/', [Team\InviteController::class, 'destroy'])->can('delete', 'pendingInvite');
        });
    });

    /*
    |-------------------------------------------------------------
    | Projects actions.
    |-------------------------------------------------------------
    */

    Route::prefix('projects')->group(function () {
        // Leader nominations.
        Route::group([
            'prefix' => '{project}/leader-nominations',
        ], function () {
            Route::get('/', [Project\LeaderNominationController::class, 'index'])
                ->can('viewAny', [LeaderNomination::class, 'project']);
            Route::post('/{user}', [Project\LeaderNominationController::class, 'nominate'])
                ->can('nominate', [LeaderNomination::class, 'project', 'user']);
        });

        // Boards.
        Route::group([
            'prefix' => '{project}/boards',
        ], function () {
            Route::get('/', [Project\BoardController::class, 'index'])->can('viewAny', [Board::class, 'project']);
        });

        // Update project settings.
        Route::group([
            'prefix' => '{project}',
            'middleware' => 'can:update,project',
        ], function () {
            Route::patch('/', [Project\ProjectController::class, 'update']);

            Route::post('/image', [Project\ImageController::class, 'store']);
            Route::delete('/image', [Project\ImageController::class, 'destroy']);
        });

        Route::get('/{project}', [Project\ProjectController::class, 'show'])->can('view', 'project');
    });
});
