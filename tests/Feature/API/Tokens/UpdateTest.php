<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class UpdateTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var PassportToken
     */
    private $token;

    /**
     * Data to send to API
     *
     * @var array
     */
    private $data;

    /**
     * Attributes to send to API
     *
     * @var array
     */
    private $attributes;

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

        $realtoken = $this->user->createToken('_test_')->token;
        $this->token = PassportToken::find($realtoken->id);

        $this->attributes = [
            'name'   => '__TEST_UPDATE__',
        ];

        $this->data = [
            'data' => [
                'type'       => 'tokens',
                'id'         => $this->token->id,
                'attributes' => $this->attributes,
            ],
        ];
    }

    // --

    public function testUpdate()
    {
        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $this->token->id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        unset($this->data['data']['attributes']);

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 200);

        $attributes = $this->token->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testMissingTypeFails()
    {
        unset($this->data['data']['type']);

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testWrongTypeFails()
    {
        $this->data['data']['type'] = 'foobar';

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testMissingIdFails()
    {
        unset($this->data['data']['id']);

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testWrongIdFails()
    {
        $this->data['data']['id'] = 'foobar';

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testInvalidScopeCausesValidationError()
    {
        $this->data['data']['attributes']['scopes'] = ['__INVALID__'];

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/scopes']],
            ],
        ]);
    }

    public function testNonStringNameCausesError()
    {
        $this->data['data']['attributes']['name'] = [];

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNonArrayScopesCausesError()
    {
        $this->data['data']['attributes']['scopes'] = 'foobar';

        $response = $this->patch(route('api.tokens.update', $this->token->id), $this->data);
        $this->validateResponse($response, 400);

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
        $this->validateResponse($response, 404);
    }
}
