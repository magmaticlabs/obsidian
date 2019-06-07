<?php

namespace Tests\Feature\API\Repositories\Packages;

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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    // --

    public function testDefaultEmpty()
    {
        $response = $this->get(route('api.repositories.packages', $this->repository->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testCorrectCount()
    {
        $count = 5;
        factory(Package::class)->times($count)->create([
            'repository_id' => $this->repository->id,
        ]);

        $response = $this->get(route('api.repositories.packages', $this->repository->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testCorrectData()
    {
        $package = factory(Package::class)->create([
            'repository_id' => $this->repository->id,
        ]);

        $response = $this->get(route('api.repositories.packages', $this->repository->id));
        $this->validateResponse($response, 200);

        $attributes = $package->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'packages',
                    'id'         => $package->id,
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.repositories.packages', 'missing'));
        $this->validateResponse($response, 404);
    }
}
