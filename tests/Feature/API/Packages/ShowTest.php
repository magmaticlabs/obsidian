<?php

namespace Tests\Feature\API\Packages;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
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
     * Repository to create the package in
     *
     * @var Repository
     */
    private $repository;

    /**
     * Test package
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
        $this->organization->addMember($this->user);

        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->package = factory(Package::class)->create([
            'repository_id' => $this->repository->id,
        ]);
    }

    // --

    public function testShow()
    {
        $response = $this->get(route('api.packages.show', $this->package->id));
        $this->validateResponse($response, 200);

        $attributes = $this->package->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $this->package->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.packages.show', 'missing'));
        $this->validateResponse($response, 404);
    }
}
