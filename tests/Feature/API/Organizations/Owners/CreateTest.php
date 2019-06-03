<?php

namespace Tests\Feature\API\Organizations\Owners;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class CreateTest extends TestCase
{
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

        Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
    }

    // --

    public function testCreate()
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

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $response->assertJsonFragment([
            'data' => [
                [
                    'type'       => 'users',
                    'id'         => $user->id,
                ],
            ],
        ]);
    }

    public function testCreateNonMember()
    {
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
            ],
        ]);
    }

    public function testCreateMissing()
    {
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

    public function testNonExist()
    {
        $response = $this->post(route('api.organizations.owners.create', 'missing', []));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
