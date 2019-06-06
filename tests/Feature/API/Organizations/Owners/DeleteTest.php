<?php

namespace Tests\Feature\API\Organizations\Owners;

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
     * Data to send to API
     *
     * @var array
     */
    private $data;

    /**
     * Fragment for the authenticated user
     *
     * @var array
     */
    private $self;

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

        $this->data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => null,
                ],
            ],
        ];

        $this->self = [
            'type' => 'users',
            'id'   => $this->user->id,
        ];
    }

    /**
     * Demote the authenticated user
     */
    private function demote()
    {
        $this->organization->demoteMember($this->user);
    }

    // --

    public function testDestroy()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);
        $this->organization->promoteMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [$this->self],
        ]);
    }

    public function testDestroyNonOwner()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [$this->self],
        ]);
    }

    public function testDestroyNonMember()
    {
        $user = factory(User::class)->create();
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testDestroyMissing()
    {
        $this->data['data'][0]['id'] = 'foobar';

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 400);

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
        $this->organization->promoteMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [$this->self],
        ]);

        $this->demote();

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testCantDestroySelf()
    {
        $this->data['data'][0]['id'] = $this->user->id;

        $response = $this->delete(route('api.organizations.owners.destroy', $this->organization->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.organizations.owners.destroy', 'missing'), $this->data);
        $this->validateResponse($response, 404);
    }
}
