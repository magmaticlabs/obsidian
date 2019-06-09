<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\UUID;
use MagmaticLabs\Obsidian\Domain\Transformers\BuildTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\PackageTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RelationshipTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RepositoryTransformer;

final class PackageController extends ResourceController
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('index', Package::class);

        return new Response($this->collection(
            $request,
            Package::query(),
            new PackageTransformer()
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
            'data.id'                  => 'not_present',
            'data.type'                => 'required|match:packages',
            'data.attributes.name'     => 'required|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.source'   => 'required|string|regex:/^git@[\S]+:[\S]+$/i',
            'data.attributes.ref'      => 'sometimes|string',
            'data.attributes.schedule' => 'sometimes|string|in:nightly,weekly,hook,none',
        ])['data'];

        $relationships = $this->validate($request, [
            'relationships.repository.data.type' => 'required|match:repositories',
            'relationships.repository.data.id'   => 'required|exists:repositories,id',
        ])['relationships'];

        $this->validate($request, [
            'data.attributes.name' => [
                Rule::unique('packages', 'name')->where('repository_id', $relationships['repository']['data']['id']),
            ],
        ]);

        /* @var Repository $repository */
        $repository = Repository::find($relationships['repository']['data']['id']);
        if (!$repository->organization->hasMember($this->getUser())) {
            abort(403, 'You are not a member of the specified organization');
        }

        try {
            $id = (string) UUID::generate();
        } catch (Exception $e) {
            abort(500, 'Unable to generate UUID');
            $id = null;
        }

        $this->commandbus->dispatch(new Command('package.create', [
            'id'            => $id,
            'name'          => trim($data['attributes']['name']),
            'repository_id' => $relationships['repository']['data']['id'],
            'source'        => trim($data['attributes']['source']),
            'ref'           => isset($data['attributes']['ref']) ? trim($data['attributes']['ref']) : 'master',
            'schedule'      => isset($data['attributes']['schedule']) ? trim($data['attributes']['schedule']) : 'hook',
        ]));

        $response = new Response($this->item(
            Package::find($id),
            new PackageTransformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.packages.show', (string) $id),
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
        $this->authorize('show', $package = Package::findOrFail($id));

        return new Response($this->item(
            $package,
            new PackageTransformer()
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
        /* @var Package $package */
        $this->authorize('update', $package = Package::findOrFail($id));

        $data = $this->validate($request, [
            'data.id'                  => "required|match:$id",
            'data.type'                => 'required|match:packages',
            'data.attributes.name'     => 'sometimes|min:3|regex:/^[a-z0-9\-]+$/i',
            'data.attributes.source'   => 'sometimes|string|regex:/^git@[\S]+:[\S]+$/i',
            'data.attributes.ref'      => 'sometimes|string',
            'data.attributes.schedule' => 'sometimes|string|in:nightly,weekly,hook,none',
        ])['data'];

        $this->validate($request, [
            'relationships' => 'not_present',
        ]);

        $this->validate($request, [
            'data.attributes.name' => [
                Rule::unique('packages', 'name')->where('repository_id', $package->repository->id),
            ],
        ]);

        $attributes = [];

        foreach (['name', 'source', 'ref', 'schedule'] as $key) {
            if (isset($data['attributes'][$key])) {
                $attributes[$key] = trim($data['attributes'][$key]);
            }
        }

        if (!empty($attributes)) {
            $this->commandbus->dispatch(new Command('package.update', [
                'id'         => $id,
                'attributes' => $attributes,
            ]));
        }

        return new Response($this->item(
            $package->refresh(),
            new PackageTransformer()
        ), 200);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(string $id): Response
    {
        $this->authorize('destroy', $package = Package::findOrFail($id));

        $this->commandbus->dispatch(new Command('package.destroy', [
            'id' => $id,
        ]));

        return new Response(null, 204);
    }

    // --

    /**
     * Repository relationship
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function repository(Request $request, string $id): Response
    {
        /* @var Package $package */
        $this->authorize('repository_index', $package = Package::findOrFail($id));

        return new Response($this->item(
            $package->repository,
            new RepositoryTransformer()
        ), 200);
    }

    /**
     * Repository relationship index
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function repository_index(Request $request, string $id): Response
    {
        /* @var Package $package */
        $this->authorize('repository_index', $package = Package::findOrFail($id));

        return new Response($this->item(
            $package->repository,
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Repository relationship creation
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function repository_create(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    /**
     * Repository relationship destruction
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function repository_destroy(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    // --

    /**
     * Builds relationship
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function builds(Request $request, string $id): Response
    {
        /* @var Package $package */
        $this->authorize('builds_index', $package = Package::findOrFail($id));

        return new Response($this->collection(
            $request,
            $package->builds(),
            new BuildTransformer()
        ), 200);
    }

    /**
     * Builds relationship index
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function builds_index(Request $request, string $id): Response
    {
        /* @var Package $package */
        $this->authorize('builds_index', $package = Package::findOrFail($id));

        return new Response($this->collection(
            $request,
            $package->builds(),
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Builds relationship creation
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function builds_create(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    /**
     * Builds relationship destruction
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function builds_destroy(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }
}
