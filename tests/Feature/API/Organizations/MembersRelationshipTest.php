<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\APIResource\RelationshipTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class MembersRelationshipTest extends RelationshipTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'organizations';

    /**
     * {@inheritdoc}
     */
    protected $class = Organization::class;

    /**
     * {@inheritdoc}
     */
    protected $relationship = 'members';

    /**
     * {@inheritdoc}
     */
    protected $relationship_type = 'users';

    /**
     * {@inheritdoc}
     */
    protected $relationship_plurality = self::PLURAL;

    // --

    /**
     * @test
     */
    public function create_relationship()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post($this->route('members.create', $model->id), $data);
        $this->validateResponse($response, 200);

        $output = [
            [
                'type' => 'users',
                'id'   => $user->id,
            ],
            [
                'type' => 'users',
                'id'   => $this->user->id,
            ],
        ];

        $response->assertJson([
            'data' => $this->sortData($output, 'id'),
        ]);

        $this->assertTrue($model->hasMember($user));
        $this->assertFalse($model->hasOwner($user));
    }

    /**
     * @test
     */
    public function create_duplicate()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $user = $this->factory(User::class)->create();
        $model->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post($this->route('members.create', $model->id), $data);
        $this->validateResponse($response, 200);

        $output = [
            [
                'type' => 'users',
                'id'   => $user->id,
            ],
            [
                'type' => 'users',
                'id'   => $this->user->id,
            ],
        ];

        $response->assertJson([
            'data' => $this->sortData($output, 'id'),
        ]);
    }

    /**
     * @test
     */
    public function create_permissions()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post($this->route('members.create', $model->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function create_unknown_user()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => '__INVALID__',
                ],
            ],
        ];

        $response = $this->post($this->route('members.create', $model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                [
                    'source' => ['pointer' => '/data/0/id'],
                ],
            ],
        ]);
    }

    // --

    /**
     * @test
     */
    public function delete_relationship()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $user = $this->factory(User::class)->create();
        $model->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete($this->route('members.destroy', $model->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertFalse($model->hasMember($user));
        $this->assertFalse($model->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_owner()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $user = $this->factory(User::class)->create();
        $model->addMember($user);
        $model->promoteMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete($this->route('members.destroy', $model->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertFalse($model->hasMember($user));
        $this->assertFalse($model->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_non_member()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete($this->route('members.destroy', $model->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertFalse($model->hasMember($user));
        $this->assertFalse($model->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);

        $user = $this->factory(User::class)->create();
        $model->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete($this->route('members.destroy', $model->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function delete_unknown_user()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => '__INVALID__',
                ],
            ],
        ];

        $response = $this->delete($this->route('members.destroy', $model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                [
                    'source' => ['pointer' => '/data/0/id'],
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function delete_self()
    {
        /** @var Organization $model */
        $model = $this->createModel();
        $model->addMember($this->user);
        $model->promoteMember($this->user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ];

        $response = $this->delete($this->route('members.destroy', $model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                [
                    'source' => ['pointer' => '/data/0/id'],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRelationshipModel(Model $parent, int $times = 1)
    {
        /** @var Organization $organization */
        $organization = $parent;

        if (1 === $times) {
            $user = $this->factory(User::class)->create();
            $organization->addMember($user);

            return $user;
        }

        $users = $this->factory(User::class)->times($times)->create();
        foreach ($users as $user) {
            $organization->addMember($user);
        }

        return $users;
    }
}
