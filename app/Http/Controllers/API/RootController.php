<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class RootController extends Controller
{
    public function index(Request $request): Response
    {
        // Default routes for all users
        $routes = [
            '_self' => route('api.root'),
        ];

        return new Response([
            'meta'  => new \stdClass(),
            'data'  => null,
            'links' => $routes,
        ], 200);
    }
}
