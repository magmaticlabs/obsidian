<?php

namespace Tests\Feature\API\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class CreateTest extends ResourceTestCase
{
    use TestCreateEndpoints;

    protected $resourceType = 'packages';

    /**
     * Organization.
     *
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);
    }

    /**
     * @test
     */
    public function permissions()
    {
        $this->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $this->getValidAttributes(),
            ],
            'relationships' => $this->getParentRelationship(),
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentRelationship(): array
    {
        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
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
    public function validAttributesProvider(): array
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
    public function invalidAttributesProvider(): array
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
}
