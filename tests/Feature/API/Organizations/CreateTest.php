<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\CreateTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class CreateTest extends CreateTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'organizations';

    /**
     * @test
     */
    public function name_duplicate_causes_error()
    {
        $this->factory(Organization::class)->create(['name' => 'duplicate']);

        $attributes = $this->getValidAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

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
}
