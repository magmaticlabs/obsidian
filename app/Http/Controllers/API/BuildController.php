<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\UUID;
use MagmaticLabs\Obsidian\Domain\Transformers\BuildTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\PackageTransformer;
use MagmaticLabs\Obsidian\Domain\Transformers\RelationshipTransformer;

final class BuildController extends ResourceController
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('index', Build::class);

        return new Response($this->collection(
            $request,
            Build::query(),
            new BuildTransformer()
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
            'data.type'                => 'required|match:builds',
        ])['data'];

        $relationships = $this->validate($request, [
            'relationships.package.data.type' => 'required|match:packages',
            'relationships.package.data.id'   => 'required|exists:packages,id',
        ])['relationships'];

        /* @var Package $package */
        $package = Package::find($relationships['package']['data']['id']);
        if (!$package->repository->organization->hasMember($this->getUser())) {
            abort(403, 'You are not a member of the specified organization');
        }

        try {
            $id = (string) UUID::generate();
        } catch (Exception $e) {
            abort(500, 'Unable to generate UUID');
            $id = null;
        }

        $this->commandbus->dispatch(new Command('build.create', [
            'id'         => $id,
            'package_id' => $relationships['package']['data']['id'],
            'ref'        => $package->ref,
            'status'     => 'pending',
        ]));

        $response = new Response($this->item(
            Build::find($id),
            new BuildTransformer()
        ), 201);

        $response->withHeaders([
            'Location' => route('api.builds.show', (string) $id),
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
        $this->authorize('show', $build = Build::findOrFail($id));

        return new Response($this->item(
            $build,
            new BuildTransformer()
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
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    // --

    /**
     * Package relationship
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Illuminate\Http\Response
     */
    public function package(Request $request, string $id): Response
    {
        /* @var Build $build */
        $this->authorize('package_index', $build = Build::findOrFail($id));

        return new Response($this->item(
            $build->package,
            new PackageTransformer()
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
    public function package_index(Request $request, string $id): Response
    {
        /* @var Build $build */
        $this->authorize('package_index', $build = Build::findOrFail($id));

        return new Response($this->item(
            $build->package,
            new RelationshipTransformer()
        ), 200);
    }

    /**
     * Package relationship creation
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function package_create(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }

    /**
     * Package relationship destruction
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Response
     */
    public function package_destroy(Request $request, string $id): Response
    {
        abort(405, 'Not Allowed');

        return new Response('Not Allowed', 405);
    }
}
