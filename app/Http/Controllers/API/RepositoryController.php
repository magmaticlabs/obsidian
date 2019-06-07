<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Transformers\OrganizationTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\PackageTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RelationshipTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RepositoryTransformer;

final class RepositoryController extends ResourceController
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('index', Repository::class);

        return new Response($this->collection(
            $request,
            Repository::query(),
            new RepositoryTransformer()
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
        $this->authorize('create', Repository::class);

        $data = $this->validate($request, [
            'data.id'                      => 'not_present',
            'data.type'                    => 'required|match:repositories',
            'data.attributes.name'         => 'required|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.display_name' => 'sometimes|string|min:3|max:255',
            'data.attributes.description'  => 'sometimes|string',
        ])['data'];

        $relationships = $this->validate($request, [
            'relationships.organization.data.type' => 'required|match:organizations',
            'relationships.organization.data.id'   => 'required|exists:organizations,id',
        ])['relationships'];

        $this->validate($request, [
            'data.attributes.name' => [
                Rule::unique('repositories', 'name')->where('organization_id', $relationships['organization']['data']['id']),
            ],
        ]);

        $organization = Organization::find($relationships['organization']['data']['id']);
        if (!$organization->hasMember($this->getUser())) {
            abort(403, 'You are not a member of the specified organization');
        }

        $repository = Repository::create([
            'name'            => trim($data['attributes']['name']),
            'organization_id' => $relationships['organization']['data']['id'],
            'display_name'    => isset($data['attributes']['display_name']) ? trim($data['attributes']['display_name']) : trim($data['attributes']['name']),
            'description'     => isset($data['attributes']['description']) ? trim($data['attributes']['description']) : '',
        ]);

        $response = new Response($this->item(
            $repository,
            new RepositoryTransformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.repositories.show', (string) $repository->getKey()),
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
        $this->authorize('show', $repository = Repository::findOrFail($id));

        return new Response($this->item(
            $repository,
            new RepositoryTransformer()
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
        /* @var Repository $repository */
        $this->authorize('update', $repository = Repository::findOrFail($id));

        $data = $this->validate($request, [
            'data.id'                      => "required|match:$id",
            'data.type'                    => 'required|match:repositories',
            'data.attributes.name'         => 'sometimes|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.display_name' => 'sometimes|string|min:3|max:255',
            'data.attributes.description'  => 'sometimes|string',
        ])['data'];

        $this->validate($request, [
            'relationships' => 'not_present',
        ]);

        $this->validate($request, [
            'data.attributes.name' => [
                Rule::unique('repositories', 'name')->where('organization_id', $repository->organization->id),
            ],
        ]);

        $attributes = [];

        foreach (['name', 'display_name', 'description'] as $key) {
            if (isset($data['attributes'][$key])) {
                $attributes[$key] = trim($data['attributes'][$key]);
            }
        }

        if (!empty($attributes)) {
            $repository->update($attributes);
        }

        return new Response($this->item(
            $repository,
            new RepositoryTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(string $id): Response
    {
        $this->authorize('destroy', $repository = Repository::findOrFail($id));

        try {
            $repository->delete();
        } catch (\Throwable $ex) {
            return new Response('Failed to delete repository', 500);
        }

        return new Response(null, 204);
    }

    // --

    /**
     * Organization relationship
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function organization(Request $request, string $id): Response
    {
        /* @var Repository $repository */
        $this->authorize('organization_index', $repository = Repository::findOrFail($id));

        return new Response($this->item(
            $repository->organization,
            new OrganizationTransformer()
        ), 200);
    }

    /**
     * Organization relationship index
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function organization_index(Request $request, string $id): Response
    {
        /* @var Repository $repository */
        $this->authorize('organization_index', $repository = Repository::findOrFail($id));

        return new Response($this->item(
            $repository->organization,
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Organization relationship creation
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function organization_create(Request $request, string $id): Response
    {
        return abort(405, 'Not Allowed');
    }

    /**
     * Organization relationship destruction
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function organization_destroy(Request $request, string $id): Response
    {
        return abort(405, 'Not Allowed');
    }

    // --

    /**
     * Packages relationship
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function packages(Request $request, string $id): Response
    {
        $this->authorize('packages_index', $repository = Repository::findOrFail($id));

        return new Response($this->collection(
            $request,
            $repository->packages(),
            new PackageTransformer()
        ), 200);
    }

    /**
     * Packages relationship index
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function packages_index(Request $request, string $id): Response
    {
        $this->authorize('packages_index', $repository = Repository::findOrFail($id));

        return new Response($this->collection(
            $request,
            $repository->packages(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Packages relationship creation
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function packages_create(Request $request, string $id): Response
    {
        return abort(405, 'Not Allowed');
    }

    /**
     * Packages relationship destruction
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function packages_destroy(Request $request, string $id): Response
    {
        return abort(405, 'Not Allowed');
    }
}
