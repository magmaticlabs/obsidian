<?php

namespace Tests\Feature\API\Organizations\Members;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
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

    public function testCreate()
    {
        $user = factory(User::class)->create();
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->post(route('api.organizations.members.create', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                $this->data['data'][0],
                $this->self,
            ],
        ]);
    }

    public function testCreateDuplicate()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->post(route('api.organizations.members.create', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                $this->data['data'][0],
                $this->self,
            ],
        ]);
    }

    public function testCreateMissing()
    {
        $this->data['data'][0]['id'] = 'foobar';

        $response = $this->post(route('api.organizations.members.create', $this->organization->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testCreatePermissions()
    {
        $user = factory(User::class)->create();
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->post(route('api.organizations.members.create', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                $this->data['data'][0],
                $this->self,
            ],
        ]);

        $this->demote();

        $response = $this->post(route('api.organizations.members.create', $this->organization->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->post(route('api.organizations.members.create', 'missing'), $this->data);
        $this->validateResponse($response, 404);
    }
}
