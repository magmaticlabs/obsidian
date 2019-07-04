<?php

namespace Tests\Feature\API;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\BuildController
 */
final class BuildsTest extends ResourceTestCase
{
    use TestIndexEndpoints;
    use TestCreateEndpoints;
    use TestShowEndpoints;

    protected $resourceType = 'builds';

    /**
     * @test
     */
    public function create_permissions()
    {
        $relationship = $this->getParentRelationship();

        /** @var Package $package */
        $package = Package::find($relationship['package']['data']['id']);
        $package->repository->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => [],
            ],
            'relationships' => $relationship,
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function update_not_allowed()
    {
        $resource = $this->createResource();

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id));
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function delete_not_allowed()
    {
        $resource = $this->createResource();

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));
        $this->validateResponse($response, 403);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        return $this->factory(Build::class)->create([
            'package_id' => $package->id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getParentRelationship(): array
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        return [
            'package' => [
                'data' => [
                    'type' => 'packages',
                    'id'   => $package->id,
                ],
            ],
        ];
    }
}
