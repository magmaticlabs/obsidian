<?php

namespace Tests\Feature\API\Organizations;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class UpdateTest extends ResourceTestCase
{
    use TestUpdateEndpoints;

    protected $resourceType = 'organizations';

    /**
     * @test
     */
    public function name_duplicate_causes_error()
    {
        $resource = $this->createResource();

        $this->factory(Organization::class)->create(['name' => 'duplicate']);

        $attributes = $this->getValidAttributes();
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
                'attributes' => $this->getValidAttributes(),
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 403);
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

    protected function createResource(): EloquentModel
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);
        $organization->promoteMember($this->user);

        return $organization;
    }
}
