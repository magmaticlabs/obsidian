<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
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

        Passport::actingAs(factory(User::class)->create());

        $this->attributes = [
            'name'         => 'testing',
            'display_name' => '__TESTING__',
            'description'  => 'This is a test org',
        ];

        $this->data = [
            'data' => [
                'type'       => 'organizations',
                'attributes' => $this->attributes,
            ],
        ];
    }

    // --

    public function testCreate()
    {
        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testMissingTypeCausesValidationError()
    {
        unset($this->data['data']['type']);

        $response = $this->post(route('api.organizations.create'), $this->data);
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

        $response = $this->post(route('api.organizations.create'), $this->data);
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

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testMissingDisplayNameDefaultsToName()
    {
        unset($this->data['data']['attributes']['display_name']);

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 201);

        $this->attributes['display_name'] = $this->attributes['name'];

        $response->assertJson([
            'data' => [
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testMissingDescriptionDefaultsToEmpty()
    {
        unset($this->data['data']['attributes']['description']);

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 201);

        $this->attributes['description'] = '';

        $response->assertJson([
            'data' => [
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testNonStringNameCausesError()
    {
        $this->data['data']['attributes']['name'] = [];

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameInvalidCharsCausesError()
    {
        $this->data['data']['attributes']['name'] = 'This is illegal!';

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameTooShortCausesError()
    {
        $this->data['data']['attributes']['name'] = 'no';

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateCausesError()
    {
        factory(Organization::class)->create(['name' => 'duplicate']);
        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNonStringDisplayNameCausesError()
    {
        $this->data['data']['attributes']['display_name'] = [];

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/display_name']],
            ],
        ]);
    }

    public function testDisplayNameTooShortCausesError()
    {
        $this->data['data']['attributes']['display_name'] = 'no';

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/display_name']],
            ],
        ]);
    }

    public function testNonStringDescriptionCausesError()
    {
        $this->data['data']['attributes']['description'] = [];

        $response = $this->post(route('api.organizations.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }
}
