<?php

namespace Tests\Feature\API\Repositories\Organization;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class CreateTest extends TestCase
{
    /**
     * Authenticated User
     *
     * @var User
     */
    private $user;

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

        $this->user = Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();

        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    // --

    public function testCreateNotAllowed()
    {
        $response = $this->post(route('api.repositories.organization.create', $this->repository->id), []);
        $this->validateResponse($response, 405);
    }
}
