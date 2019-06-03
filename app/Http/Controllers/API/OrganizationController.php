<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use MagmaticLabs\Obsidian\Domain\Transformers\OrganizationTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RelationshipTransformer;
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

        $organization = Organization::create([
            'name'         => trim($data['attributes']['name']),
            'display_name' => isset($data['attributes']['display_name']) ? trim($data['attributes']['display_name']) : trim($data['attributes']['name']),
            'description'  => isset($data['attributes']['description']) ? trim($data['attributes']['description']) : '',
        ]);

        $organization->addMember($user = $this->getUser());
        $organization->promoteMember($user);

        $response = new Response($this->item(
            $organization,
            new OrganizationTransformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.organizations.show', (string) $organization->getKey()),
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

        try {
            $organization->delete();
        } catch (\Throwable $ex) {
            return new Response('Failed to delete token', 500);
        }

        return new Response(null, 204);
    }

    // --

    /**
     * Members relationship
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
     * Members relationship index
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
     * Members relationship creation
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

        foreach ($data as $row) {
            $organization->addMember(User::find($row['id']));
        }

        return new Response($this->collection(
            $request,
            $organization->members(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Members relationship destruction
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

        foreach ($data as $row) {
            $organization->removeMember(User::find($row['id']));
        }

        return new Response($this->collection(
            $request,
            $organization->members(),
            new RelationshipTransformer()
        ), 200);
    }

    // --

    /**
     * Owners relationship
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
     * Owners relationship index
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
     * Owners relationship creation
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

        foreach ($data as $row) {
            $organization->promoteMember(User::find($row['id']));
        }

        return new Response($this->collection(
            $request,
            $organization->owners(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Owners relationship destruction
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

        foreach ($data as $row) {
            $organization->demoteMember(User::find($row['id']));
        }

        return new Response($this->collection(
            $request,
            $organization->owners(),
            new RelationshipTransformer()
        ), 200);
    }
}
