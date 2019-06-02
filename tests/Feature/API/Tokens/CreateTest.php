<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class CreateTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );

        Passport::actingAs(factory(User::class)->create());
    }

    // --

    public function testCreate()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'name'   => '__TESTING__',
                    'scopes' => [],
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->validateJSONAPI($response->getContent());
        $response->assertHeader('Location');

        $tokenid = basename($response->headers->get('Location'));

        $attributes = [
            'name'    => '__TESTING__',
            'scopes'  => [],
            'revoked' => false,
        ];

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $tokenid,
                'attributes' => $attributes,
            ],
        ]);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertArrayHasKey('accessToken', $attr);
        $this->assertNotEmpty($attr['accessToken']);
    }

    public function testMissingTypeCausesValidationError()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'attributes' => [
                    'name'   => '__TESTING__',
                    'scopes' => [],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testWrongTypeCausesValidationError()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'foobar',
                'attributes' => [
                    'name'   => '__TESTING__',
                    'scopes' => [],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testMissingNameCausesValidationError()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'scopes' => [],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testMissingScopesDefaultsToEmpty()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'name' => '__TESTING__',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->validateJSONAPI($response->getContent());
        $response->assertHeader('Location');

        $tokenid = basename($response->headers->get('Location'));

        $attributes = [
            'name'    => '__TESTING__',
            'scopes'  => [],
            'revoked' => false,
        ];

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $tokenid,
                'attributes' => $attributes,
            ],
        ]);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertArrayHasKey('accessToken', $attr);
        $this->assertNotEmpty($attr['accessToken']);
    }

    public function testInvalidScopeCausesValidationError()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'name'   => '__TESTING__',
                    'scopes' => ['__INVALID__'],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/scopes']],
            ],
        ]);
    }

    public function testNonStringNameCausesError()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'name'   => [],
                    'scopes' => [],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNonArrayScopesCausesError()
    {
        $response = $this->post(route('api.tokens.create'), [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'name'   => '__TESTING__',
                    'scopes' => 'foo',
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/scopes']],
            ],
        ]);
    }
}
