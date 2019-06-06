<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class UpdateTest extends TestCase
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
        $this->organization->promoteMember($this->user);

        $this->attributes = [
            'name'         => 'updated',
            'display_name' => '__UPDATED__',
            'description'  => 'It has been updated',
        ];

        $this->data = [
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $this->attributes,
            ],
        ];
    }

    /**
     * Demote the authenticated user
     */
    private function demote()
    {
        $this->organization->demoteMember($this->user);
    }

    // --

    public function testUpdate()
    {
        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        unset($this->attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        unset($this->data['data']['attributes']);

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
        $this->validateResponse($response, 200);

        $attributes = $this->organization->toArray();
        unset($attributes['id']);

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
        unset($this->data['data']['type']);

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testNonStringNameCausesError()
    {
        $this->data['data']['attributes']['name'] = [];

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
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

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }

    public function testUpdatePermissions()
    {
        $this->demote();

        $response = $this->patch(route('api.organizations.update', $this->organization->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->patch(route('api.organizations.update', 'missing'), $this->data);
        $this->validateResponse($response, 404);
    }
}
