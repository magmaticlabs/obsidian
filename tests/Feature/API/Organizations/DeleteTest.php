<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class DeleteTest extends TestCase
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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
    }

    /**
     * Set the authenticated user as the owner of the organization
     */
    public function setOwner()
    {
        $this->organization->addMember($this->user);
        $this->organization->promoteMember($this->user);
    }

    // --

    public function testDelete()
    {
        $this->setOwner();

        $response = $this->delete(route('api.organizations.destroy', $this->organization->id));

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    public function testDeleteActuallyWorks()
    {
        $this->setOwner();

        $this->assertEquals(1, Organization::query()->count());

        $this->delete(route('api.organizations.destroy', $this->organization->id));

        $this->assertEquals(0, Organization::query()->count());
    }

    public function testDeletePermissions()
    {
        $response = $this->delete(route('api.organizations.destroy', $this->organization->id));

        $response->assertStatus(403);
        $this->validateJSONAPI($response->getContent());

        $this->setOwner();

        $response = $this->delete(route('api.organizations.destroy', $this->organization->id));

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.organizations.destroy', 'missing'));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
