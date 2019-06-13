<?php

namespace Tests\Feature\API\Organizations\Members;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\Organizations\OrganizationTestCase;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends OrganizationTestCase
{
    /**
     * Fragment for the authenticated user.
     *
     * @var array
     */
    private $self;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

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

    // --

    public function testCreate()
    {
        $user = $this->factory(User::class)->create();
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->post($this->getRoute('members.create', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                $this->data['data'][0],
                $this->self,
            ],
        ]);

        static::assertTrue($this->model->hasMember($user));
        static::assertFalse($this->model->hasOwner($user));
    }

    public function testCreateDuplicate()
    {
        $user = $this->factory(User::class)->create();
        $this->model->addMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->post($this->getRoute('members.create', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                $this->data['data'][0],
                $this->self,
            ],
        ]);

        static::assertTrue($this->model->hasMember($user));
        static::assertFalse($this->model->hasOwner($user));
    }

    public function testUnknownUser()
    {
        $this->data['data'][0]['id'] = 'foobar';

        $response = $this->post($this->getRoute('members.create', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testPermissions()
    {
        $this->demote();

        $response = $this->post($this->getRoute('members.create', $this->model->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->post($this->getRoute('members.create', '__INVALID__'), $this->data);
        $this->validateResponse($response, 404);
    }
}
