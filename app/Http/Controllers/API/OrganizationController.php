<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\UUID;
use MagmaticLabs\Obsidian\Domain\Transformers\OrganizationTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RelationshipTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RepositoryTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\Transformer;

final class OrganizationController extends ResourceController
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('index', Organization::class);

        return new Response($this->collection(
            $request,
            Organization::query(),
            new OrganizationTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Organization::class);

        $data = $this->validate($request, [
            'data.id'                      => 'not_present',
            'data.type'                    => 'required|match:organizations',
            'data.attributes.name'         => 'required|unique:organizations,name|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.display_name' => 'sometimes|string|min:3|max:255',
            'data.attributes.description'  => 'sometimes|string',
        ])['data'];

        try {
            $id = (string) UUID::generate();
        } catch (Exception $e) {
            abort(500, 'Unable to generate UUID');
            $id = null;
        }

        $this->commandbus->dispatch(new Command('organization.create', [
            'id'           => $id,
            'name'         => trim($data['attributes']['name']),
            'display_name' => isset($data['attributes']['display_name']) ? trim($data['attributes']['display_name']) : trim($data['attributes']['name']),
            'description'  => isset($data['attributes']['description']) ? trim($data['attributes']['description']) : '',
        ]));

        $response = new Response($this->item(
            Organization::find($id),
            new OrganizationTransformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.organizations.show', $id),
        ]);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(string $id): Response
    {
        $this->authorize('show', $organization = Organization::findOrFail($id));

        return new Response($this->item(
            $organization,
            new OrganizationTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, string $id): Response
    {
        $this->authorize('update', $organization = Organization::findOrFail($id));

        $data = $this->validate($request, [
            'data.id'                      => "required|match:{$id}",
            'data.type'                    => 'required|match:organizations',
            'data.attributes.name'         => 'sometimes|unique:organizations,name|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.display_name' => 'sometimes|string|min:3|max:255',
            'data.attributes.description'  => 'sometimes|string',
        ])['data'];

        $this->validate($request, [
            'relationships' => 'not_present',
        ]);

        $attributes = [];

        foreach (['name', 'display_name', 'description'] as $key) {
            if (isset($data['attributes'][$key])) {
                $attributes[$key] = trim($data['attributes'][$key]);
            }
        }

        if (!empty($attributes)) {
            $this->commandbus->dispatch(new Command('organization.update', [
                'id'         => $id,
                'attributes' => $attributes,
            ]));
        }

        return new Response($this->item(
            $organization->refresh(),
            new OrganizationTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(string $id): Response
    {
        $this->authorize('destroy', $organization = Organization::findOrFail($id));

        $this->commandbus->dispatch(new Command('organization.destroy', [
            'id' => $id,
        ]));

        return new Response(null, 204);
    }

    // --

    /**
     * Members relationship.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function members(Request $request, string $id): Response
    {
        $this->authorize('members_index', $organization = Organization::findOrFail($id));

        return new Response($this->collection(
            $request,
            $organization->members(),
            new Transformer()
        ), 200);
    }

    /**
     * Members relationship index.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function members_index(Request $request, string $id): Response
    {
        $this->authorize('members_index', $organization = Organization::findOrFail($id));

        return new Response($this->collection(
            $request,
            $organization->members(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Members relationship creation.
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function members_create(Request $request, string $id): Response
    {
        $this->authorize('members_create', $organization = Organization::findOrFail($id));

        $data = $this->validate($request, [
            'data'        => 'required|numeric_array',
            'data.*.type' => 'required|match:users',
            'data.*.id'   => 'required|exists:users,id',
        ])['data'];

        $this->commandbus->dispatch(new Command('organization.members.create', [
            'id'    => $id,
            'users' => array_map(function ($data) {
                return $data['id'];
            }, $data),
        ]));

        return new Response($this->collection(
            $request,
            $organization->members(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Members relationship destruction.
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function members_destroy(Request $request, string $id): Response
    {
        $this->authorize('members_destroy', $organization = Organization::findOrFail($id));

        $data = $this->validate($request, [
            'data'        => 'required|numeric_array',
            'data.*.type' => 'required|match:users',
            'data.*.id'   => 'required|exists:users,id|not_match:' . $this->getUser()->getKey(),
        ])['data'];

        $this->commandbus->dispatch(new Command('organization.members.destroy', [
            'id'    => $id,
            'users' => array_map(function ($data) {
                return $data['id'];
            }, $data),
        ]));

        return new Response($this->collection(
            $request,
            $organization->members(),
            new RelationshipTransformer()
        ), 200);
    }

    // --

    /**
     * Owners relationship.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function owners(Request $request, string $id): Response
    {
        $this->authorize('owners_index', $organization = Organization::findOrFail($id));

        return new Response($this->collection(
            $request,
            $organization->owners(),
            new Transformer()
        ), 200);
    }

    /**
     * Owners relationship index.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function owners_index(Request $request, string $id): Response
    {
        $this->authorize('owners_index', $organization = Organization::findOrFail($id));

        return new Response($this->collection(
            $request,
            $organization->owners(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Owners relationship creation.
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function owners_create(Request $request, string $id): Response
    {
        $this->authorize('owners_create', $organization = Organization::findOrFail($id));

        $data = $this->validate($request, [
            'data'        => 'required|numeric_array',
            'data.*.type' => 'required|match:users',
            'data.*.id'   => [
                'required',
                'exists:users,id',
                Rule::exists('organization_memberships', 'user_id')->where('organization_id', $id),
            ],
        ])['data'];

        $this->commandbus->dispatch(new Command('organization.owners.create', [
            'id'    => $id,
            'users' => array_map(function ($data) {
                return $data['id'];
            }, $data),
        ]));

        return new Response($this->collection(
            $request,
            $organization->owners(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Owners relationship destruction.
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function owners_destroy(Request $request, string $id): Response
    {
        $this->authorize('owners_destroy', $organization = Organization::findOrFail($id));

        $data = $this->validate($request, [
            'data'        => 'required|numeric_array',
            'data.*.type' => 'required|match:users',
            'data.*.id'   => [
                'required',
                'exists:users,id',
                'not_match:' . $this->getUser()->getKey(),
                Rule::exists('organization_memberships', 'user_id')->where('organization_id', $id),
            ],
        ])['data'];

        $this->commandbus->dispatch(new Command('organization.owners.destroy', [
            'id'    => $id,
            'users' => array_map(function ($data) {
                return $data['id'];
            }, $data),
        ]));

        return new Response($this->collection(
            $request,
            $organization->owners(),
            new RelationshipTransformer()
        ), 200);
    }

    // --

    /**
     * Repositories relationship.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function repositories(Request $request, string $id): Response
    {
        $this->authorize('repositories_index', $organization = Organization::findOrFail($id));

        return new Response($this->collection(
            $request,
            $organization->repositories(),
            new RepositoryTransformer()
        ), 200);
    }

    /**
     * Repositories relationship index.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function repositories_index(Request $request, string $id): Response
    {
        $this->authorize('repositories_index', $organization = Organization::findOrFail($id));

        return new Response($this->collection(
            $request,
            $organization->repositories(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Repositories relationship creation.
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function repositories_create(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    /**
     * Repositories relationship destruction.
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function repositories_destroy(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }
}
