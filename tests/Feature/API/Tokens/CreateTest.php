<?php

namespace Tests\Feature\API\Tokens;

use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class CreateTest extends ResourceTestCase
{
    use TestCreateEndpoints;

    protected $resourceType = 'tokens';

    /**
     * @test
     */
    public function has_access_token()
    {
        $attributes = $this->getValidAttributes();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 201);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertNotEmpty($attr['accessToken']);
    }

    /**
     * {@inheritdoc}
     */
    public function validAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'   => '__TESTING__',
                'scopes' => [],
            ]],
            'no-scopes' => [[
                'name' => '__TESTING__',
            ]],
            'fancy-name' => [[
                'name' => 'ThIs is a %5up3r% fancy name!',
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
            'nonarray-scopes' => [[
                'name'   => '__TESTING__',
                'scopes' => 'foobar',
            ], 'scopes'],
            'invalid-scopes' => [[
                'name'   => '__TESTING__',
                'scopes' => ['__INVALID__'],
            ], 'scopes'],
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
            'optional' => ['scopes', []],
        ];
    }
}
