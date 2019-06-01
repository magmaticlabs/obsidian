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
Route::name('login')->post('/login', 'RootController@login');

// Authenticated API routes
Route::middleware(['auth:api'])->group(function() {
    Route::name('session')->get('/session', 'RootController@session');
});
