<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /**
     * Organization
     *
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
    }

    // --

    public function testUpdate()
    {
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'name'         => 'updated',
                    'display_name' => '__UPDATED__',
                    'description'  => 'It has been updated',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = $this->organization->toArray();
        foreach (array_merge($this->organization->getHidden(), ['id']) as $key) {
            unset($attributes[$key]);
        }
        $attributes['name'] = 'updated';
        $attributes['display_name'] = '__UPDATED__';
        $attributes['description'] = 'It has been updated';

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = $this->organization->toArray();
        foreach (array_merge($this->organization->getHidden(), ['id']) as $key) {
            unset($attributes[$key]);
        }

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testMissingTypeFails()
    {
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'id' => $this->organization->id,
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type' => 'foobar',
                'id'   => $this->organization->id,
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type' => 'organizations',
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type' => 'organizations',
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

    public function testNonStringNameCausesError()
    {
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'name' => [],
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'name' => 'This is illegal!',
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'name' => 'no',
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'name' => 'duplicate',
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'display_name' => [],
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'display_name' => 'no',
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
        $response = $this->patch(route('api.organizations.update', $this->organization->id), [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => [
                    'description' => [],
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

    public function testNonExist()
    {
        $response = $this->patch(route('api.organizations.update', 'missing'), [
            'data' => [
                'type' => 'organizations',
                'id'   => 'foobar',
            ],
        ]);

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
