<?php

namespace Tests\Feature\API\Packages;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class IndexTest extends ResourceTestCase
{
    use TestIndexEndpoints;

    protected $resourceType = 'packages';

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

        return $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);
    }
}
