<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var \Laravel\Passport\Token
     */
    private $token;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );

        Passport::actingAs($this->user = factory(User::class)->create());

        $this->token = $this->user->createToken('_test_')->token;
    }

    // --

    public function testUpdate()
    {
        /** @var PassportToken $token */
        $token = PassportToken::find($this->token->id);

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
                'attributes' => [
                    'name'   => '__TEST_UPDATE__',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = $token->toArray();
        foreach (array_merge($token->getHidden(), ['id']) as $key) {
            unset($attributes[$key]);
        }
        $attributes['name'] = '__TEST_UPDATE__';

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        /** @var PassportToken $token */
        $token = PassportToken::find($this->token->id);

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = $token->toArray();
        foreach (array_merge($token->getHidden(), ['id']) as $key) {
            unset($attributes[$key]);
        }

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testMissingTypeFails()
    {
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'id' => $this->token->id,
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

    public function testWrongTypeFails()
    {
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'type' => 'foobar',
                'id'   => $this->token->id,
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

    public function testMissingIdFails()
    {
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'type' => 'tokens',
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testWrongIdFails()
    {
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'type' => 'tokens',
                'id'   => 'foobar',
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testInvalidScopeCausesValidationError()
    {
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $this->token->id,
                'attributes' => [
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
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $this->token->id,
                'attributes' => [
                    'name'   => [],
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
        $response = $this->patch(route('api.tokens.update', $this->token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $this->token->id,
                'attributes' => [
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

    public function testNonExist()
    {
        $response = $this->patch(route('api.tokens.update', 'missing'), [
            'data' => [
                'type' => 'tokens',
                'id'   => 'foobar',
            ],
        ]);

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
