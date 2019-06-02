<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Conditional wrapper allows PHPUnit to execute
if (!function_exists('resource_routes')) {
    function resource_routes(array $routes): void
    {
        foreach ($routes as $name) {
            $controller = ucwords(Str::singular($name)) . 'Controller';
            Route::name("$name.")->prefix("/$name")->group(function() use ($controller) {
                Route::name('index')->get('/', "$controller@index");
                Route::name('create')->post('/', "$controller@create");
                Route::name('show')->get('/{id}', "$controller@show");
                Route::name('update')->patch('/{id}', "$controller@update");
                Route::name('destroy')->delete('/{id}', "$controller@destroy");
            });
        }
    };
}

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
Route::name('session')->get('/session', 'AuthController@session');

// Authenticated API routes
Route::middleware(['auth:api'])->group(function() {
    resource_routes([
        'tokens',
        'organizations',
    ]);
});
