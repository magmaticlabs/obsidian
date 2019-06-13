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

    public function testCreate()
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

        static::assertTrue($model->hasMember($user));
        static::assertFalse($model->hasOwner($user));
    }

    public function testCreateDuplicate()
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

    public function testCreatePermissions()
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

    public function testCreateUnknownUser()
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

    public function testDelete()
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

        static::assertFalse($model->hasMember($user));
        static::assertFalse($model->hasOwner($user));
    }

    public function testDeleteOwner()
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

        static::assertFalse($model->hasMember($user));
        static::assertFalse($model->hasOwner($user));
    }

    public function testDeleteNonMember()
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

        static::assertFalse($model->hasMember($user));
        static::assertFalse($model->hasOwner($user));
    }

    public function testDeletePermissions()
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

    public function testDeleteUnknownUser()
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

    public function testDeleteSelf()
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
