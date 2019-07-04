<?php

namespace Tests\Feature\API;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;
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
}
