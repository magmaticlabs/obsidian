<?php

namespace Tests\Feature\API\Organizations\Members;

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

    public function testDestroy()
    {
        $this->setOwner();

        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $response->assertJsonFragment([
            'data' => [
                [
                    'type'       => 'users',
                    'id'         => $this->user->id,
                ],
            ],
        ]);
    }

    public function testDestroyNonMember()
    {
        $this->setOwner();

        $user = factory(User::class)->create();

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $response->assertJsonFragment([
            'data' => [
                [
                    'type'       => 'users',
                    'id'         => $this->user->id,
                ],
            ],
        ]);
    }

    public function testDestroyMissing()
    {
        $this->setOwner();

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => 'foobar',
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testDestroyPermissions()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);

        $response->assertStatus(403);
        $this->validateJSONAPI($response->getContent());

        $this->setOwner();

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $response->assertJsonFragment([
            'data' => [
                [
                    'type'       => 'users',
                    'id'         => $this->user->id,
                ],
            ],
        ]);
    }

    public function testCantDestroySelf()
    {
        $this->setOwner();

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.organizations.members.destroy', 'missing', []));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
