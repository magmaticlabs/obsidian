<?php

namespace Tests\Feature\API\Packages;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class DeleteTest extends TestCase
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

    /**
     * Remove the authenticated user from the organization
     */
    public function removeUser()
    {
        $this->organization->removeMember($this->user);
    }

    // --

    public function testDelete()
    {
        $response = $this->delete(route('api.packages.destroy', $this->package->id));
        $this->validateResponse($response, 204);
    }

    public function testDeleteActuallyWorks()
    {
        $this->delete(route('api.packages.destroy', $this->package->id));
        $this->expectException(ModelNotFoundException::class);
        $this->package->refresh();
    }

    public function testDeletePermissions()
    {
        $this->removeUser();

        $response = $this->delete(route('api.packages.destroy', $this->package->id));
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.packages.destroy', 'missing'));
        $this->validateResponse($response, 404);
    }
}
