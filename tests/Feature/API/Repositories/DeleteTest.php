<?php

namespace Tests\Feature\API\Repositories;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class DeleteTest extends ResourceTestCase
{
    use TestDeleteEndpoints;

    protected $resourceType = 'repositories';

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Repository $resource */
        $resource = $this->createResource();
        $resource->organization->removeMember($this->user);

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

        return $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);
    }
}
