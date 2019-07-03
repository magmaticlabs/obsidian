<?php

namespace Tests\Feature\API\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\UpdateTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class UpdateTest extends UpdateTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'packages';

    /**
     * {@inheritdoc}
     */
    protected $class = Package::class;

    /**
     * Organization.
     *
     * @var Organization
     */
    private $organization;

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

    // --

    /**
     * @test
     */
    public function permissions()
    {
        $this->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type' => 'packages',
                'id'   => $this->model->id,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function name_duplicate_causes_error()
    {
        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $this->model->repository->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'packages',
                'id'         => $this->model->id,
                'attributes' => [
                    'name' => 'duplicate',
                ],
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
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
        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $repository->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'packages',
                'id'         => $this->model->id,
                'attributes' => [
                    'name' => 'duplicate',
                ],
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 200);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel(int $times = 1)
    {
        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        if (1 === $times) {
            return $this->factory($this->class)->create([
                'repository_id' => $repository->id,
            ]);
        }

        return $this->factory($this->class)->times($times)->create([
            'repository_id' => $repository->id,
        ]);
    }
}
