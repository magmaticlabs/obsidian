<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Top level API routes
Route::name('root')->get('/', 'RootController@index');
Route::name('login')->post('/login', 'AuthController@login');

// Authenticated API routes
Route::middleware(['auth:api'])->group(function() {
    Route::name('session')->get('/session', 'AuthController@session');

    // Token routes
    Route::name('tokens.')->prefix('/tokens')->group(function() {
        Route::name('index')->get('/', 'TokenController@index');
        Route::name('create')->post('/', 'TokenController@create');
        Route::name('show')->get('/{id}', 'TokenController@show');
        Route::name('update')->patch('/{id}', 'TokenController@update');
        Route::name('destroy')->delete('/{id}', 'TokenController@destroy');
    });
});
