<?php

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

// PKG Routes

Route::name('pkg.')->prefix('pkg')->group(function() {
    Route::name('root')->get('/', 'PKGController@root');
    Route::name('organization')->get('/{id}', 'PKGController@organization');
    Route::name('repository')->get('/{org}/{id}', 'PKGController@repository');
    Route::name('package')->get('/{org}/{repo}/{id}', 'PKGController@package');
    Route::name('download')->get('/{org}/{repo}/{id}/{version}', 'PKGController@download');
});
