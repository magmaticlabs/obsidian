<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTest\ResourceTest;

class IncludesTest extends ResourceTest
{
    public function testDefaultReturnAll()
    {
        /* @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        $response = $this->get(route('api.repositories.show', $repository->id));

        $response->assertJson([
            'included'  => [
                [
                    'type'       => 'organizations',
                    'id'         => $organization->id,
                    'attributes' => [],
                ],
                [
                    'type'       => 'packages',
                    'id'         => $package->id,
                    'attributes' => [],
                ],
            ],
        ]);
    }

    public function testRequestNone()
    {
        /* @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        $response = $this->get(route('api.repositories.show', [$repository->id, 'include=null']));

        $response->assertJsonMissing([
            'included'  => [
                [
                    'type'       => 'organizations',
                    'id'         => $organization->id,
                    'attributes' => [],
                ],
                [
                    'type'       => 'packages',
                    'id'         => $package->id,
                    'attributes' => [],
                ],
            ],
        ]);
    }

    public function testRequestPackageOnly()
    {
        /* @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        $response = $this->get(route('api.repositories.show', [$repository->id, 'include=packages']));

        $response->assertJson([
            'included'  => [
                [
                    'type'       => 'packages',
                    'id'         => $package->id,
                    'attributes' => [],
                ],
            ],
        ]);
    }
}
