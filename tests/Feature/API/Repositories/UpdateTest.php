<?php

namespace Tests\Feature\API\Repositories;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class UpdateTest extends ResourceTestCase
{
    use TestUpdateEndpoints;

    protected $resourceType = 'repositories';

    /**
     * @test
     */
    public function permissions()
    {
        /** @var Repository $resource */
        $resource = $this->createResource();
        $resource->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type' => 'repositories',
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
        /** @var Repository $resource */
        $resource = $this->createResource();

        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $resource->organization->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'repositories',
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
    public function name_duplicate_another_org_success()
    {
        /** @var Repository $resource */
        $resource = $this->createResource();

        $organization = $this->factory(Organization::class)->create();

        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $organization->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'repositories',
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
            'nonstring-display-name' => [[
                'display_name' => [],
            ], 'display_name'],
            'nonstring-description' => [[
                'description' => [],
            ], 'description'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        return $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);
    }
}
