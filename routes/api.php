<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Conditional wrapper allows PHPUnit to execute
if (!function_exists('resource_routes')) {
    function resource_routes(array $routes): void
    {
        foreach ($routes as $name => $relationships) {
            if (is_numeric($name)) {
                $name = $relationships;
                $relationships = [];
            }

            $controller = ucwords(Str::singular($name)) . 'Controller';
            Route::name("${name}.")->prefix("/${name}")->group(function() use ($controller, $relationships) {
                Route::name('index')->get('/', "${controller}@index");
                Route::name('create')->post('/', "${controller}@create");
                Route::name('show')->get('/{id}', "${controller}@show");
                Route::name('update')->patch('/{id}', "${controller}@update");
                Route::name('destroy')->delete('/{id}', "${controller}@destroy");

                foreach ($relationships as $relationship) {
                    Route::name($relationship)->get("/{id}/$relationship", "${controller}@${relationship}");
                    Route::name("${relationship}.index")->get("/{id}/relationships/$relationship", "${controller}@${relationship}_index");
                    Route::name("${relationship}.create")->post("/{id}/relationships/$relationship", "${controller}@${relationship}_create");
                    Route::name("${relationship}.update")->patch("/{id}/relationships/$relationship", "${controller}@${relationship}_update");
                    Route::name("${relationship}.destroy")->delete("/{id}/relationships/$relationship", "${controller}@${relationship}_destroy");
                }
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
Route::name('auth.')->prefix('auth')->group(function() {
    Route::name('session')->get('/', 'AuthController@session');
    Route::name('login')->post('/login', 'AuthController@login');
});

// Authenticated API routes
Route::middleware(['auth:api'])->group(function() {
    resource_routes([
        'tokens',
        'users',
        'organizations' => [
            'members',
            'owners',
            'repositories',
        ],
        'repositories' => [
            'organization',
            'packages',
        ],
        'packages' => [
            'repository',
            'builds',
        ],
        'builds' => [
            'package',
        ]
    ]);
});
