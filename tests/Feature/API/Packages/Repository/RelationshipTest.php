<?php

namespace Tests\Feature\API\Packages\Repository;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class RelationshipTest extends TestCase
{
    /**
     * Organization
     *
     * @var Organization
     */
    private $organization;

    /**
     * Repository
     *
     * @var Repository
     */
    private $repository;

    /**
     * Package
     *
     * @var Package
     */
    private $package;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();

        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->package = factory(Package::class)->create([
            'repository_id' => $this->repository->id,
        ]);
    }

    // --

    public function testCorrectData()
    {
        $response = $this->get(route('api.packages.repository', $this->package->id));
        $this->validateResponse($response, 200);

        $attributes = $this->repository->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->repository->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.packages.repository', 'missing'));
        $this->validateResponse($response, 404);
    }
}
