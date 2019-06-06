<?php

namespace Tests\Feature\API\Organizations;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class DeleteTest extends TestCase
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
        $this->organization->addMember($this->user);
        $this->organization->promoteMember($this->user);
    }

    /**
     * Demote the authenticated user
     */
    private function demote()
    {
        $this->organization->demoteMember($this->user);
    }

    // --

    public function testDelete()
    {
        $response = $this->delete(route('api.organizations.destroy', $this->organization->id));
        $this->validateResponse($response, 204);
    }

    public function testDeleteActuallyWorks()
    {
        $this->delete(route('api.organizations.destroy', $this->organization->id));
        $this->expectException(ModelNotFoundException::class);
        $this->organization->refresh();
    }

    public function testDeletePermissions()
    {
        $this->demote();

        $response = $this->delete(route('api.organizations.destroy', $this->organization->id));
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.organizations.destroy', 'missing'));
        $this->validateResponse($response, 404);
    }
}
