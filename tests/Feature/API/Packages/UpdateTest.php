<?php

namespace Tests\Feature\API\Packages;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class UpdateTest extends ResourceTestCase
{
    use TestUpdateEndpoints;

    protected $resourceType = 'packages';

    /**
     * @test
     */
    public function permissions()
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
    public function name_duplicate_causes_error()
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
    public function name_duplicate_another_repo_success()
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

    // --

    /**
     * {@inheritdoc}
     */
    public function validAttributesProvider(): array
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
    public function invalidAttributesProvider(): array
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

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        return $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);
    }
}
