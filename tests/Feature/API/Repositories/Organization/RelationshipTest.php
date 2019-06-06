<?php

namespace Tests\Feature\API\Repositories\Organization;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
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

    public function testCorrectData()
    {
        $response = $this->get(route('api.repositories.organization', $this->repository->id));
        $this->validateResponse($response, 200);

        $attributes = $this->organization->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.repositories.organization', 'missing'));
        $this->validateResponse($response, 404);
    }
}
