<?php

namespace Tests\Feature\API\Packages;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class DeleteTest extends ResourceTestCase
{
    use TestDeleteEndpoints;

    protected $resourceType = 'packages';

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Package $resource */
        $resource = $this->createResource();
        $resource->repository->organization->removeMember($this->user);

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));
        $this->validateResponse($response, 403);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        return $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);
    }
}
