<?php

namespace Tests\Feature\API;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;
use Tests\Feature\API\ResourceTests\TestRelationshipEndpoints;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class OrganizationsTest extends ResourceTestCase
{
    use TestIndexEndpoints;
    use TestCreateEndpoints;
    use TestShowEndpoints;
    use TestUpdateEndpoints;
    use TestDeleteEndpoints;
    use TestRelationshipEndpoints;

    protected $resourceType = 'organizations';

    /**
     * @test
     */
    public function create_name_duplicate_causes_error()
    {
        $this->factory(Organization::class)->create(['name' => 'duplicate']);

        $attributes = $this->getValidCreateAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    /**
     * @test
     */
    public function update_name_duplicate_causes_error()
    {
        $resource = $this->createResource();

        $this->factory(Organization::class)->create(['name' => 'duplicate']);

        $attributes = $this->getValidUpdateAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    /**
     * @test
     */
    public function update_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $this->getValidUpdateAttributes(),
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));
        $this->validateResponse($response, 403);
    }

    // --

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function create_relationship(string $relation)
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function delete_relationship(string $relation)
    {
        $this->expectNotToPerformAssertions();
    }

    // --

    /**
     * @test
     */
    public function create_member()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.members.create', $resource->id), $data);
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

        $this->assertTrue($resource->hasMember($user));
        $this->assertFalse($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function create_duplicate_member()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.members.create', $resource->id), $data);
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
    public function create_member_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.members.create', $resource->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function create_member_unknown_user()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => '__INVALID__',
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.members.create', $resource->id), $data);
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
    public function delete_member()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.members.destroy', $resource->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertFalse($resource->hasMember($user));
        $this->assertFalse($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_member_owner()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);
        $resource->promoteMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.members.destroy', $resource->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertFalse($resource->hasMember($user));
        $this->assertFalse($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_non_member()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.members.destroy', $resource->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertFalse($resource->hasMember($user));
        $this->assertFalse($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_member_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.members.destroy', $resource->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function delete_member_unknown_user()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => '__INVALID__',
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.members.destroy', $resource->id), $data);
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
    public function delete_member_self()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.members.destroy', $resource->id), $data);
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
    public function create_owner()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.owners.create', $resource->id), $data);
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

        $this->assertTrue($resource->hasMember($user));
        $this->assertTrue($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function create_duplicate_owner()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);
        $resource->promoteMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.owners.create', $resource->id), $data);
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
    public function create_owner_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.owners.create', $resource->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function create_owner_non_member()
    {
        /** @var Organization $model */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.owners.create', $resource->id), $data);
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
    public function create_owner_unknown_user()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => '__INVALID__',
                ],
            ],
        ];

        $response = $this->post(route('api.organizations.owners.create', $resource->id), $data);
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
    public function delete_owner()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);
        $resource->promoteMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.owners.destroy', $resource->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertTrue($resource->hasMember($user));
        $this->assertFalse($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_owner_non_owner()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.owners.destroy', $resource->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ]);

        $this->assertTrue($resource->hasMember($user));
        $this->assertFalse($resource->hasOwner($user));
    }

    /**
     * @test
     */
    public function delete_owner_non_member()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $user = $this->factory(User::class)->create();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.owners.destroy', $resource->id), $data);
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
    public function delete_owner_permissions()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();
        $resource->demoteMember($this->user);

        $user = $this->factory(User::class)->create();
        $resource->addMember($user);
        $resource->promoteMember($user);

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.owners.destroy', $resource->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function delete_owner_unknown_user()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => '__INVALID__',
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.owners.destroy', $resource->id), $data);
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
    public function delete_owner_self()
    {
        /** @var Organization $resource */
        $resource = $this->createResource();

        $data = [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
            ],
        ];

        $response = $this->delete(route('api.organizations.owners.destroy', $resource->id), $data);
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
     * {@inheritdoc}
     */
    public function validCreateAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'         => 'testing',
                'display_name' => '__TESTING__',
                'description'  => 'This is a test organization',
            ]],
            'no-description' => [[
                'name'         => 'testing',
                'display_name' => '__TESTING__',
            ]],
            'no-display-name' => [[
                'name'        => 'testing',
                'description' => 'This is a test organization',
            ]],
            'fancy-name' => [[
                'name' => 'this-is-a-fancy-name',
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function invalidCreateAttributesProvider(): array
    {
        return [
            'nonstring-name' => [[
                'name' => [],
            ], 'name'],
            'tooshort-name' => [[
                'name' => 'no',
            ], 'name'],
            'invalid-name' => [[
                'name' => 'This is Illegal!',
            ], 'name'],
            'nonstring-display-name' => [[
                'name'         => 'testing',
                'display_name' => [],
            ], 'display_name'],
            'nonstring-description' => [[
                'name'        => 'testing',
                'description' => [],
            ], 'description'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function requiredAttributesProvider(): array
    {
        return [
            'required' => ['name'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function optionalAttributesProvider(): array
    {
        return [
            'description'  => ['description', ''],
            'display_name' => ['display_name', '%name%'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validUpdateAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'         => 'testing',
                'display_name' => '__TESTING__',
                'description'  => 'This is a test organization',
            ]],
            'no-description' => [[
                'name'         => 'testing',
                'display_name' => '__TESTING__',
            ]],
            'no-display-name' => [[
                'name'        => 'testing',
                'description' => 'This is a test organization',
            ]],
            'no--name' => [[
                'display_name' => '__TESTING__',
                'description'  => 'This is a test organization',
            ]],
            'fancy-name' => [[
                'name' => 'this-is-a-fancy-name',
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function invalidUpdateAttributesProvider(): array
    {
        return [
            'nonstring-name' => [[
                'name' => [],
            ], 'name'],
            'tooshort-name' => [[
                'name' => 'no',
            ], 'name'],
            'invalid-name' => [[
                'name' => 'This is Illegal!',
            ], 'name'],
            'nonstring-display-name' => [[
                'display_name' => [],
            ], 'display_name'],
            'nonstring-description' => [[
                'description' => [],
            ], 'description'],
        ];
    }

    public function relationshipProvider(): array
    {
        return [
            'members'      => ['members', 'users', true],
            'owners'       => ['owners', 'users', true],
            'repositories' => ['repositories', 'repositories', true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);
        $organization->promoteMember($this->user);

        return $organization;
    }

    /**
     * {@inheritdoc}
     */
    protected function createRelationship(EloquentModel $resource, string $relation, int $times = 1)
    {
        /** @var Organization $organization */
        $organization = $resource;

        switch ($relation) {
            case 'members':
                $users = $this->factory(User::class)->times($times - 1)->create();
                foreach ($users as $user) {
                    $organization->addMember($user);
                }

                return $organization->members;
            case 'owners':
                $users = $this->factory(User::class)->times($times - 1)->create();
                foreach ($users as $user) {
                    $organization->addMember($user);
                    $organization->promoteMember($user);
                }

                return $organization->owners;
            case 'repositories':
                $this->factory(Repository::class)->times($times)->create([
                    'organization_id' => $organization->id,
                ]);

                return $organization->repositories;
        }

        return null;
    }
}
