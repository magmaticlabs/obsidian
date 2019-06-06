<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class CreateTest extends TestCase
{
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

        Passport::actingAs(factory(User::class)->create());

        $this->attributes = [
            'name'   => '__TESTING__',
            'scopes' => [],
        ];

        $this->data = [
            'data' => [
                'type'       => 'tokens',
                'attributes' => $this->attributes,
            ],
        ];
    }

    // --

    public function testCreate()
    {
        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $tokenid = basename($response->headers->get('Location'));

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $tokenid,
                'attributes' => $this->attributes,
            ],
        ]);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertArrayHasKey('accessToken', $attr);
        $this->assertNotEmpty($attr['accessToken']);
    }

    public function testMissingTypeCausesValidationError()
    {
        unset($this->data['data']['type']);

        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testWrongTypeCausesValidationError()
    {
        $this->data['data']['type'] = 'foobar';

        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testMissingNameCausesValidationError()
    {
        unset($this->data['data']['attributes']['name']);

        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testMissingScopesDefaultsToEmpty()
    {
        unset($this->data['data']['attributes']['scopes']);

        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertJson([
            'data' => [
                'attributes' => $this->attributes,
            ],
        ]);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertArrayHasKey('accessToken', $attr);
        $this->assertNotEmpty($attr['accessToken']);
    }

    public function testInvalidScopeCausesValidationError()
    {
        $this->data['data']['attributes']['scopes'] = ['__INVALID__'];

        $response = $this->post(route('api.tokens.create'), $this->data);
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

        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNonArrayScopesCausesError()
    {
        $this->data['data']['attributes']['scopes'] = 'foo';

        $response = $this->post(route('api.tokens.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/scopes']],
            ],
        ]);
    }
}
