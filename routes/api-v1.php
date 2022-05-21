<?php

use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Board;
use App\Http\Controllers\Api\V1\Card;
use App\Http\Controllers\Api\V1\Column;
use App\Http\Controllers\Api\V1\Project;
use App\Http\Controllers\Api\V1\Team;
use App\Http\Controllers\Api\V1\User;
use App\Models\Board as BoardModel;
use App\Models\Card as CardModel;
use App\Models\Column as ColumnModel;
use App\Models\Invite as InviteModel;
use App\Models\LeaderNomination as LeaderNominationModel;
use App\Models\Module as ModuleModel;
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

    Route::group([
        'prefix' => 'teams',
        'controller' => Team\TeamController::class,
    ], function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('{team}/leave', 'leave')->can('leave', 'team');

        // Projects.
        Route::group([
            'prefix' => '{team}/projects',
            'controller' => Team\ProjectController::class,
        ], function () {
            Route::get('/', 'index')->can('viewAny', [ProjectModel::class, 'team']);
            Route::get('/trashed', 'indexTrashed')->can('viewAny', [ProjectModel::class, 'team']);
            Route::post('/', 'store')->can('create', [ProjectModel::class, 'team']);
        });

        // Invites.
        Route::group([
            'prefix' => '{team}/invites',
            'controller' => Team\InviteController::class,
        ], function () {
            Route::get('/', 'index')->can('viewAny', [InviteModel::class, 'team']);
            Route::post('/', 'store')->can('create', [InviteModel::class, 'team']);
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

        Route::get('/{team}/members', 'members')->can('view', 'team');
        Route::get('/{team}', 'show')->can('view', 'team');
    });

    /*
    |-------------------------------------------------------------
    | Invites actions.
    |-------------------------------------------------------------
    */

    Route::group([
        'prefix' => 'invites',
        'controller' => User\InviteController::class,
    ], function () {
        Route::get('/', 'index');

        Route::prefix('/{pendingInvite}')->group(function () {
            Route::post('/accept', 'accept')->can('accept', 'pendingInvite');
            Route::post('/decline', 'decline')->can('decline', 'pendingInvite');
            Route::delete('/', [Team\InviteController::class, 'destroy'])->can('delete', 'pendingInvite');
        });
    });

    /*
    |-------------------------------------------------------------
    | Projects actions.
    |-------------------------------------------------------------
    */

    Route::group([
        'prefix' => 'projects',
        'controller' => Project\ProjectController::class,
    ], function () {
        // Leader nominations.
        Route::group([
            'prefix' => '{project}/leader-nominations',
            'controller' => Project\LeaderNominationController::class,
        ], function () {
            Route::get('/', 'index')->can('viewAny', [LeaderNominationModel::class, 'project']);
            Route::post('/{user}', 'nominate')->can('nominate', [LeaderNominationModel::class, 'project', 'user']);
        });

        // Boards.
        Route::group([
            'prefix' => '{project}/boards',
            'controller' => Project\BoardController::class,
        ], function () {
            Route::get('/', 'index')->can('viewAny', [BoardModel::class, 'project']);
            Route::get('/trashed', 'indexTrashed')->can('viewAny', [BoardModel::class, 'project']);
            Route::get('/closed', 'indexClosed')->can('viewAny', [BoardModel::class, 'project']);
            Route::post('/', 'store')->can('create', [BoardModel::class, 'project']);
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

        Route::post('/{trashed:project}/restore', 'restore')->can('restore', 'trashed:project');
        Route::get('/{project}/leader', 'leader')->can('view', 'project');
        Route::get('/{project}', 'show')->can('view', 'project');
        Route::delete('/{project}', 'destroy')->can('delete', 'project');
    });

    /*
    |--------------------------------------------------------------------------
    | Boards actions.
    |--------------------------------------------------------------------------
    */

    Route::group([
        'prefix' => 'boards',
        'controller' => Board\BoardController::class,
    ], function () {
        // Columns.
        Route::controller(Board\ColumnController::class)->group(function () {
            Route::get('{anyBoard}/columns', 'index')->can('viewAny', [ColumnModel::class, 'anyBoard']);
            Route::post('{board}/columns', 'store')->can('create', [ColumnModel::class, 'board']);
        });

        // Modules.
        Route::group([
            'prefix' => '{board}/modules',
            'controller' => Board\ModuleController::class,
        ], function () {
            Route::get('/', 'index')->can('viewAny', [ModuleModel::class, 'board']);

            // Kanban.
            Route::put('kanban', 'enableKanban')->can('enableKanban', [ModuleModel::class, 'board']);
            Route::post('kanban/disable', 'disableKanban')->can('disableKanban', [ModuleModel::class, 'board']);
        });

        Route::get('/{anyBoard}', 'show')->can('view', 'anyBoard');

        Route::patch('/{board}', 'update')->can('update', 'board');
        Route::post('/{closed:board}/open', 'open')->can('update', 'closed:board');
        Route::post('/{board}/close', 'close')->can('update', 'board');

        Route::post('/{trashed:board}/restore', 'restore')->can('restore', 'trashed:board');
        Route::delete('/{board}', 'destroy')->can('delete', 'board');
    });

    /*
    |--------------------------------------------------------------------------
    | Columns actions.
    |--------------------------------------------------------------------------
    */

    Route::group([
        'prefix' => 'columns',
        'controller' => Column\ColumnController::class,
    ], function () {
        // Cards.
        Route::group([
            'prefix' => '{column}/cards',
            'controller' => Column\CardController::class,
        ], function () {
            Route::get('/', 'index')->can('viewAny', [CardModel::class, 'column']);
            Route::post('/', 'store')->can('create', [CardModel::class, 'column']);
        });

        Route::post('/{column}/order', 'order')->can('update', 'column');
        Route::get('/{column}', 'show')->can('view', 'column');
        Route::patch('/{column}', 'update')->can('update', 'column');
        Route::delete('/{column}', 'destroy')->can('delete', 'column');
    });

    /*
    |--------------------------------------------------------------------------
    | Cards actions.
    |--------------------------------------------------------------------------
    */

    Route::group([
        'prefix' => 'cards',
        'controller' => Card\CardController::class,
    ], function () {
        Route::post('/{card}/order', 'order')->can('update', 'card');
        Route::post('/{card}/move/{column}', 'move')->can('move', ['card', 'column']);
        Route::get('/{card}', 'show')->can('view', 'card');
        Route::patch('/{card}', 'update')->can('update', 'card');
        Route::delete('/{card}', 'destroy')->can('delete', 'card');
    });
});
