<?php

namespace Tests\Feature\API;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class RepositoriesTest extends ResourceTestCase
{
    use TestIndexEndpoints;
    use TestCreateEndpoints;
    use TestShowEndpoints;
    use TestUpdateEndpoints;
    use TestDeleteEndpoints;

    protected $resourceType = 'repositories';

    /**
     * @test
     */
    public function create_permissions()
    {
        $relationship = $this->getParentRelationship();

        /** @var Organization $organization */
        $organization = Organization::find($relationship['organization']['data']['id']);
        $organization->removeMember($this->user);

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

        /** @var Organization $organization */
        $organization = Organization::find($relationship['organization']['data']['id']);

        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $organization->id,
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
    public function create_name_duplicate_another_org_success()
    {
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->factory(Organization::class)->create()->id,
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
    public function update_name_duplicate_causes_error()
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
    public function update_name_duplicate_another_org_success()
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

    /**
     * @test
     */
    public function delete_permissions()
    {
        /** @var Repository $resource */
        $resource = $this->createResource();
        $resource->organization->removeMember($this->user);

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
            'description' => ['description', ''],
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

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        return $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getParentRelationship(): array
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        return [
            'organization' => [
                'data' => [
                    'type' => 'organizations',
                    'id'   => $organization->id,
                ],
            ],
        ];
    }
}
