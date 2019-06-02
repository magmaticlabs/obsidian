<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Transformers\Transformer;

final class OrganizationController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function index(Request $request): Response
    {
        return new Response($this->collection(
            $request,
            Organization::query(),
            new Transformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request): Response
    {
        $data = $this->validate($request, [
            'data.id'                      => 'not_present',
            'data.type'                    => 'required|match:organizations',
            'data.attributes.name'         => 'required|unique:organizations,name|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.display_name' => 'sometimes|string|min:3|max:255',
            'data.attributes.description'  => 'sometimes|string',
        ])['data'];

        $organization = Organization::create([
            'name'         => trim($data['attributes']['name']),
            'display_name' => isset($data['attributes']['display_name']) ? trim($data['attributes']['display_name']) : trim($data['attributes']['name']),
            'description'  => isset($data['attributes']['description']) ? trim($data['attributes']['description']) : '',
        ]);

        $response = new Response($this->item(
            $organization,
            new Transformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.organizations.show', (string) $organization->getKey()),
        ]);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function show(string $id): Response
    {
        if (empty($organization = Organization::find($id))) {
            abort(404);
        }

        return new Response($this->item(
            $organization,
            new Transformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, string $id): Response
    {
        if (empty($organization = Organization::find($id))) {
            abort(404);
        }

        $data = $this->validate($request, [
            'data.id'                      => "required|match:$id",
            'data.type'                    => 'required|match:organizations',
            'data.attributes.name'         => 'sometimes|unique:organizations,name|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.display_name' => 'sometimes|string|min:3|max:255',
            'data.attributes.description'  => 'sometimes|string',
        ])['data'];

        $attributes = [];

        if (isset($data['attributes']['name'])) {
            $attributes['name'] = trim($data['attributes']['name']);
        }

        if (isset($data['attributes']['display_name'])) {
            $attributes['display_name'] = $data['attributes']['display_name'];
        }

        if (isset($data['attributes']['description'])) {
            $attributes['description'] = $data['attributes']['description'];
        }

        if (!empty($attributes)) {
            $organization->update($attributes);
        }

        return new Response($this->item(
            $organization,
            new Transformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): Response
    {
        if (empty($organization = Organization::find($id))) {
            abort(404);
        }

        try {
            $organization->delete();
        } catch (\Throwable $ex) {
            return new Response('Failed to delete token', 500);
        }

        return new Response(null, 204);
    }
}
