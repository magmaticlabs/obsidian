<?php

namespace Tests\Feature\API\Organizations\Owners;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class CreateTest extends TestCase
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

    public function testCreate()
    {
        $this->setOwner();

        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $response = $this->post(route('api.organizations.owners.create', $this->organization->id), [
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
                    'id'         => $user->id,
                ],
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);
    }

    public function testCreateNonMember()
    {
        $this->setOwner();

        $user = factory(User::class)->create();

        $response = $this->post(route('api.organizations.owners.create', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
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

    public function testCreateDuplicate()
    {
        $this->setOwner();

        $user = factory(User::class)->create();

        $this->organization->addMember($user);
        $this->organization->promoteMember($user);

        $response = $this->post(route('api.organizations.owners.create', $this->organization->id), [
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
                    'id'         => $user->id,
                ],
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);
    }

    public function testCreateMissing()
    {
        $this->setOwner();

        $response = $this->post(route('api.organizations.owners.create', $this->organization->id), [
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

    public function testCreatePermissions()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $response = $this->post(route('api.organizations.owners.create', $this->organization->id), [
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

        $response = $this->post(route('api.organizations.owners.create', $this->organization->id), [
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
                    'id'         => $user->id,
                ],
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->post(route('api.organizations.owners.create', 'missing', []));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
