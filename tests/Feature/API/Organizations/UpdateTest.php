<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    /**
     * Authenticated User
     *
     * @var User
     */
    private $user;

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

        $this->user = Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
    }

    /**
     * Set the authenticated user as the owner of the organization
     */
    public function setOwner()
    {
        $this->organization->addMember($this->user);
        $this->organization->promoteMember($this->user);
    }

    // --

    public function testUpdate()
    {
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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
        $this->setOwner();

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

    public function testUpdatePermissions()
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

        $response->assertStatus(403);
        $this->validateJSONAPI($response->getContent());

        $this->setOwner();

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
