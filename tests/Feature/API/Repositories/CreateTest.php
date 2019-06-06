<?php

namespace Tests\Feature\API\Repositories;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class CreateTest extends TestCase
{
    /**
     * Authenticated user
     *
     * @var User
     */
    private $user;

    /**
     * Organization to create the repository in
     *
     * @var Organization
     */
    private $organization;

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

        $this->user = Passport::actingAs(factory(User::class)->create());
        $this->organization = factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        $this->attributes = [
            'name'         => 'testing',
            'display_name' => '__TESTING__',
            'description'  => 'This is a test repository',
        ];

        $this->data = [
            'data' => [
                'type'       => 'repositories',
                'attributes' => $this->attributes,
            ],
            'relationships' => [
                'organization' => [
                    'data' => [
                        'type' => 'organizations',
                        'id'   => $this->organization->id,
                    ],
                ],
            ],
        ];
    }

    // --

    public function testCreate()
    {
        $response = $this->post(route('api.repositories.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $response->assertJson([
            'data' => [
                'type'       => 'repositories',
                'id'         => $id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testCreatePermissions()
    {
        $this->organization->removeMember($this->user);

        $response = $this->post(route('api.repositories.create'), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testMissingTypeCausesValidationError()
    {
        unset($this->data['data']['type']);

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);

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

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateCausesError()
    {
        factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->organization->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post(route('api.repositories.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateAnotherOrgSuccess()
    {
        factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => factory(Organization::class)->create()->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post(route('api.repositories.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $response->assertJson([
            'data' => [
                'type'       => 'repositories',
                'id'         => $id,
                'attributes' => $this->data['data']['attributes'],
            ],
        ]);
    }

    public function testNonStringDisplayNameCausesError()
    {
        $this->data['data']['attributes']['display_name'] = [];

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
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

        $response = $this->post(route('api.repositories.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }
}
