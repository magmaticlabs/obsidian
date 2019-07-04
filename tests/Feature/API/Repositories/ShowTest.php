<?php

namespace Tests\Feature\API\Repositories;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class ShowTest extends ResourceTestCase
{
    use TestShowEndpoints;

    protected $resourceType = 'repositories';

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        return $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);
    }
}
