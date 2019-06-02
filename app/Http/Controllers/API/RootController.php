<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class RootController extends Controller
{
    public function index(Request $request): Response
    {

        $routes = [
            'session' => route('api.session'),
            '_self' => route('api.root'),
        ];

        auth()->shouldUse('api');
        if (auth()->check()) {
            $routes = array_merge([
                'session' => route('api.session'),
            ], $routes);
        } else {
            $routes = array_merge([
                'login' => route('api.login'),
            ], $routes);
        }

        return new Response([
            'meta'  => new \stdClass(),
            'data'  => null,
            'links' => $routes,
        ], 200);
    }
}
