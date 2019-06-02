<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
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

        Passport::actingAs(factory(User::class)->create());
    }

    // --

    public function testCreate()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->validateJSONAPI($response->getContent());
        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $attributes = [
            'name'         => 'testing',
            'display_name' => '__TESTING__',
            'description'  => 'This is a test org',
        ];

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testMissingTypeCausesValidationError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'foobar',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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

    public function testMissingDisplayNameDefaultsToName()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'description'  => 'This is a test org',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->validateJSONAPI($response->getContent());
        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $attributes = [
            'name'         => 'testing',
            'display_name' => 'testing',
            'description'  => 'This is a test org',
        ];

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testMissingDescriptionDefaultsToEmpty()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                ],
            ],
        ]);

        $response->assertStatus(201);
        $this->validateJSONAPI($response->getContent());
        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $attributes = [
            'name'         => 'testing',
            'display_name' => '__TESTING__',
            'description'  => '',
        ];

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonStringNameCausesError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => [],
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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

    public function testNameInvalidCharsCausesError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'This is illegal!',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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

    public function testNameTooShortCausesError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'no',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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

    public function testNameDuplicateCausesError()
    {
        factory(Organization::class)->create(['name' => 'duplicate']);

        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'duplicate',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test org',
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

    public function testNonStringDisplayNameCausesError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => [],
                    'description'  => 'This is a test org',
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/display_name']],
            ],
        ]);
    }

    public function testDisplayNameTooShortCausesError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => 'no',
                    'description'  => 'This is a test org',
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/display_name']],
            ],
        ]);
    }

    public function testNonStringDescriptionCausesError()
    {
        $response = $this->post(route('api.organizations.create'), [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                    'description'  => [],
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }
}
