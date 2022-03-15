<?php

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Board;
use App\Http\Controllers\Api\V1\Column;
use App\Http\Controllers\Api\V1\Project;
use App\Http\Controllers\Api\V1\Team;
use App\Http\Controllers\Api\V1\User;
use App\Models\Board as BoardModel;
use App\Models\Column as ColumnModel;
use App\Models\Invite as InviteModel;
use App\Models\LeaderNomination as LeaderNominationModel;
use App\Models\Project as ProjectModel;
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
            Route::get('/', [Team\ProjectController::class, 'index'])->can('viewAny', [ProjectModel::class, 'team']);
            Route::get('/trashed', [Team\ProjectController::class, 'indexTrashed'])
                ->can('viewAny', [ProjectModel::class, 'team']);
            Route::post('/', [Team\ProjectController::class, 'store'])->can('create', [ProjectModel::class, 'team']);
        });

        // Invites.
        Route::group([
            'prefix' => '{team}/invites',
        ], function () {
            Route::get('/', [Team\InviteController::class, 'index'])->can('viewAny', [InviteModel::class, 'team']);
            Route::post('/', [Team\InviteController::class, 'store'])->can('create', [InviteModel::class, 'team']);
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
                ->can('viewAny', [LeaderNominationModel::class, 'project']);
            Route::post('/{user}', [Project\LeaderNominationController::class, 'nominate'])
                ->can('nominate', [LeaderNominationModel::class, 'project', 'user']);
        });

        // Boards.
        Route::group([
            'prefix' => '{project}/boards',
        ], function () {
            Route::get('/', [Project\BoardController::class, 'index'])->can('viewAny', [BoardModel::class, 'project']);
            Route::get('/trashed', [Project\BoardController::class, 'indexTrashed'])
                ->can('viewAny', [BoardModel::class, 'project']);
            Route::post('/', [Project\BoardController::class, 'store'])->can('create', [BoardModel::class, 'project']);
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

        Route::post('/{trashed:project}/restore', [Project\ProjectController::class, 'restore'])
            ->can('restore', 'trashed:project');
        Route::get('/{project}/leader', [Project\ProjectController::class, 'leader'])->can('view', 'project');
        Route::get('/{project}', [Project\ProjectController::class, 'show'])->can('view', 'project');
        Route::delete('/{project}', [Project\ProjectController::class, 'destroy'])->can('delete', 'project');
    });

    /*
    |--------------------------------------------------------------------------
    | Boards actions.
    |--------------------------------------------------------------------------
    */

    Route::prefix('boards')->group(function () {
        // Columns.
        Route::group([
            'prefix' => '{board}/columns',
        ], function () {
            Route::get('/', [Board\ColumnController::class, 'index'])->can('viewAny', [ColumnModel::class, 'board']);
            Route::post('/', [Board\ColumnController::class, 'store'])->can('create', [ColumnModel::class, 'board']);
        });

        Route::get('/{board}', [Board\BoardController::class, 'show'])->can('view', 'board');
        Route::post('/{trashed:board}/restore', [Board\BoardController::class, 'restore'])->can('restore', 'trashed:board');
        Route::patch('/{board}', [Board\BoardController::class, 'update'])->can('update', 'board');
        Route::delete('/{board}', [Board\BoardController::class, 'destroy'])->can('delete', 'board');
    });

    /*
    |--------------------------------------------------------------------------
    | Columns actions.
    |--------------------------------------------------------------------------
    */

    Route::prefix('columns')->group(function () {
        Route::get('/{column}', [Column\ColumnController::class, 'show'])->can('view', 'column');
        Route::patch('/{column}', [Column\ColumnController::class, 'update'])->can('update', 'column');
        Route::delete('/{column}', [Column\ColumnController::class, 'destroy'])->can('delete', 'column');
    });
});
