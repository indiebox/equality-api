<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('/', 'welcome');

/*
|-------------------------------------------------------------
| Register and Email verification
|-------------------------------------------------------------
*/

Route::group([
    'prefix' => '/register',
    'as' => 'register.',
], function () {
    Route::get('/', [Auth\RegisteredUserController::class, 'create'])->name('create');
    Route::post('/', [Auth\RegisteredUserController::class, 'store'])->name('store');
});
Route::get('/verify-email/{id}/{hash}', Auth\VerifyEmailController::class)
    ->middleware('signed')
    ->name('verification.verify');
