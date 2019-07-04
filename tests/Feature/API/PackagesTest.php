<?php

namespace Tests\Feature\API;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;
use Tests\Feature\API\ResourceTests\TestRelationshipEndpoints;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class PackagesTest extends ResourceTestCase
{
    use TestIndexEndpoints;
    use TestCreateEndpoints;
    use TestShowEndpoints;
    use TestUpdateEndpoints;
    use TestDeleteEndpoints;
    use TestRelationshipEndpoints;

    protected $resourceType = 'packages';

    /**
     * @test
     */
    public function create_permissions()
    {
        $relationship = $this->getParentRelationship();

        /** @var Repository $repository */
        $repository = Repository::find($relationship['repository']['data']['id']);
        $repository->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $this->getValidCreateAttributes(),
            ],
            'relationships' => $relationship,
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function create_name_duplicate_causes_error()
    {
        $relationship = $this->getParentRelationship();

        /** @var Repository $repository */
        $repository = Repository::find($relationship['repository']['data']['id']);
        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $repository->id,
        ]);

        $attributes = $this->getValidCreateAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
            'relationships' => $relationship,
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
    public function create_name_duplicate_another_repo_success()
    {
        $organization = $this->factory(Organization::class)->create();
        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $repository->id,
        ]);

        $attributes = $this->getValidCreateAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
            'relationships' => $this->getParentRelationship(),
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 201);
    }

    /**
     * @test
     */
    public function update_permissions()
    {
        /** @var Package $resource */
        $resource = $this->createResource();
        $resource->repository->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type' => 'packages',
                'id'   => $resource->id,
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function update_name_duplicate_causes_error()
    {
        /** @var Package $resource */
        $resource = $this->createResource();

        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $resource->repository->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'packages',
                'id'         => $resource->id,
                'attributes' => [
                    'name' => 'duplicate',
                ],
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
    public function update_name_duplicate_another_repo_success()
    {
        /** @var Package $resource */
        $resource = $this->createResource();

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $resource->repository->organization->id,
        ]);

        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $repository->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'packages',
                'id'         => $resource->id,
                'attributes' => [
                    'name' => 'duplicate',
                ],
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 200);
    }

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Package $resource */
        $resource = $this->createResource();
        $resource->repository->organization->removeMember($this->user);

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));
        $this->validateResponse($response, 403);
    }

    // --

    /**
     * {@inheritdoc}
     */
    public function validCreateAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ]],
            'no-ref' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'schedule' => 'nightly',
            ]],
            'no-schedule' => [[
                'name'   => 'testing',
                'source' => 'git@github.com:example/testing.git',
                'ref'    => 'master',
            ]],
            'fancy-name' => [[
                'name'     => 'this-is-a-fancy-name',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ]],
            'custom-source' => [[
                'name'     => 'testing',
                'source'   => 'foobar@myhost.org:/mount/data.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ]],
            'custom-ref' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'foobar',
                'schedule' => 'nightly',
            ]],
            'schedule-weekly' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'weekly',
            ]],
            'schedule-hook' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'hook',
            ]],
            'schedule-none' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'none',
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
                'name'     => [],
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ], 'name'],
            'tooshort-name' => [[
                'name'     => 'no',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ], 'name'],
            'invalid-name' => [[
                'name'     => 'This is Illegal!',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ], 'name'],
            'nonstring-source' => [[
                'name'     => 'testing',
                'source'   => [],
                'ref'      => 'master',
                'schedule' => 'nightly',
            ], 'source'],
            'https-source' => [[
                'name'     => 'testing',
                'source'   => 'https://github.com/example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ], 'source'],
            'localpath-source' => [[
                'name'     => 'testing',
                'source'   => '/path/to/repository.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ], 'source'],
            'nonstring-ref' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => [],
                'schedule' => 'nightly',
            ], 'ref'],
            'nonstring-schedule' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => [],
            ], 'schedule'],
            'unknown-schedule' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => '__INVALID__',
            ], 'schedule'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function requiredAttributesProvider(): array
    {
        return [
            'name'   => ['name'],
            'source' => ['source'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function optionalAttributesProvider(): array
    {
        return [
            'ref'      => ['ref', 'master'],
            'schedule' => ['schedule', 'hook'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validUpdateAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ]],
            'no-source' => [[
                'name'     => 'testing',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ]],
            'no-ref' => [[
                'name'     => 'testing',
                'source'   => 'git@github.com:example/testing.git',
                'schedule' => 'nightly',
            ]],
            'no-schedule' => [[
                'name'   => 'testing',
                'source' => 'git@github.com:example/testing.git',
                'ref'    => 'master',
            ]],
            'no-name' => [[
                'source'   => 'git@github.com:example/testing.git',
                'ref'      => 'master',
                'schedule' => 'nightly',
            ]],
            'fancy-name' => [[
                'name' => 'this-is-a-fancy-name',
            ]],
            'custom-source' => [[
                'source' => 'foobar@myhost.org:/mount/data.git',
            ]],
            'custom-ref' => [[
                'ref' => 'foobar',
            ]],
            'schedule-weekly' => [[
                'schedule' => 'weekly',
            ]],
            'schedule-hook' => [[
                'schedule' => 'hook',
            ]],
            'schedule-none' => [[
                'schedule' => 'none',
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
            'nonstring-source' => [[
                'source' => [],
            ], 'source'],
            'https-source' => [[
                'source' => 'https://github.com/example/testing.git',
            ], 'source'],
            'localpath-source' => [[
                'source' => '/path/to/repository.git',
            ], 'source'],
            'nonstring-ref' => [[
                'ref' => [],
            ], 'ref'],
            'nonstring-schedule' => [[
                'schedule' => [],
            ], 'schedule'],
            'unknown-schedule' => [[
                'schedule' => '__INVALID__',
            ], 'schedule'],
        ];
    }

    public function relationshipProvider(): array
    {
        return [
            'repository' => ['repository', 'repositories', false],
            'builds'     => ['builds', 'builds', true],
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

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        return $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getParentRelationship(): array
    {
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        return [
            'repository' => [
                'data' => [
                    'type' => 'repositories',
                    'id'   => $repository->id,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createRelationship(EloquentModel $resource, string $relation, int $times = 1)
    {
        /** @var Package $package */
        $package = $resource;

        switch ($relation) {
            case 'repository':
                return $package->repository;
            case 'builds':
                $this->factory(Build::class)->times($times)->create([
                    'package_id' => $package->id,
                ]);

                return $package->builds;
        }

        return null;
    }
}
