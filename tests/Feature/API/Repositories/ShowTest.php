<?php

namespace Tests\Feature\API\Repositories;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class ShowTest extends TestCase
{
    /**
     * Authenticated user
     *
     * @var User
     */
    private $user;

    /**
     * Organization to create the repository in
     *
     * @var Organization
     */
    private $organization;

    /**
     * Test repository
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

        $this->user = Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    // --

    public function testShow()
    {
        $response = $this->get(route('api.repositories.show', $this->repository->id));
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
        $response = $this->get(route('api.repositories.show', 'missing'));
        $this->validateResponse($response, 404);
    }
}
