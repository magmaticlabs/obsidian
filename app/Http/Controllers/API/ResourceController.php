<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class ResourceController extends Controller
{
    /**
     * Catch all for missing methods.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return \Illuminate\Http\Response|mixed
     */
    public function __call($method, $parameters)
    {
        return $this->_notimplemented();
    }

    /**
     * Display all resources in the collection.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->_notimplemented();
    }

    /**
     * Create a new resource.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->_notimplemented();
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     *
     * @return Response
     */
    public function show(string $id): Response
    {
        return $this->_notimplemented();
    }

    /**
     * Update the specified resource.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     */
    public function update(Request $request, string $id): Response
    {
        return $this->_notimplemented();
    }

    /**
     * Destroy the specified resource.
     *
     * @param string $id
     *
     * @return Response
     */
    public function destroy(string $id): Response
    {
        return $this->_notimplemented();
    }

    /**
     * Return an unavailable response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function unavailable(Request $request): Response
    {
        return new Response([
            'errors' => [[
                'status' => '403',
                'title'  => Response::$statusTexts[403],
                'detail' => 'This action is unavailable',
            ]],
            'meta'  => new \stdClass(),
            'links' => [
                '_self' => $request->fullUrl(),
            ],
        ], 403);
    }

    /**
     * Generate a 501 response.
     *
     * @return Response
     */
    private function _notimplemented(): Response
    {
        return new Response([
            'errors' => [[
                'status' => 501,
                'title'  => 'Not Implemented',
                'detail' => 'This operation has not been implemented',
            ]],
        ], 501);
    }
}
