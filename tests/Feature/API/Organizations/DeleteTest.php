<?php

namespace Tests\Feature\API\Organizations;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class DeleteTest extends ResourceTestCase
{
    use TestDeleteEndpoints;

    protected $resourceType = 'organizations';

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));
        $this->validateResponse($response, 403);
    }

    protected function createResource(): EloquentModel
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);
        $organization->promoteMember($this->user);

        return $organization;
    }
}
