<?php

namespace Tests\Feature\API\Organizations\Members;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\Organizations\OrganizationTest;

final class DeleteTest extends OrganizationTest
{
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

    public function testDestroy()
    {
        $user = $this->factory(User::class)->create();
        $this->model->addMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete($this->getRoute('members.destroy', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [$this->self],
        ]);

        $this->assertFalse($this->model->hasMember($user));
        $this->assertFalse($this->model->hasOwner($user));
    }

    public function testDestroyOwner()
    {
        $user = $this->factory(User::class)->create();
        $this->model->addMember($user);
        $this->model->promoteMember($user);
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete($this->getRoute('members.destroy', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [$this->self],
        ]);

        $this->assertFalse($this->model->hasMember($user));
        $this->assertFalse($this->model->hasOwner($user));
    }

    public function testDestroyNonMember()
    {
        $user = $this->factory(User::class)->create();
        $this->data['data'][0]['id'] = $user->id;

        $response = $this->delete($this->getRoute('members.destroy', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [$this->self],
        ]);

        $this->assertFalse($this->model->hasMember($user));
        $this->assertFalse($this->model->hasOwner($user));
    }

    public function testUnknownUser()
    {
        $this->data['data'][0]['id'] = 'foobar';

        $response = $this->delete($this->getRoute('members.destroy', $this->model->id), $this->data);
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

        $response = $this->delete($this->getRoute('members.destroy', $this->model->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testCantDestroySelf()
    {
        $this->data['data'][0]['id'] = $this->user->id;

        $response = $this->delete($this->getRoute('members.destroy', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->delete($this->getRoute('members.destroy', '__INVALID__'), $this->data);
        $this->validateResponse($response, 404);
    }
}
