<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class CreateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );
    }

    public function testCreate()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

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
