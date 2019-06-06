<?php

namespace Tests\Feature\API\Repositories;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class UpdateTest extends TestCase
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
     * Test repository
     *
     * @var Repository
     */
    private $repository;

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

        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->attributes = [
            'name'         => 'updated',
            'display_name' => '__UPDATED__',
            'description'  => 'It has been updated',
        ];

        $this->data = [
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->repository->id,
                'attributes' => $this->attributes,
            ],
        ];
    }

    /**
     * Remove the authenticated user from the organization
     */
    public function removeUser()
    {
        $this->organization->removeMember($this->user);
    }

    // --

    public function testUpdate()
    {
        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->repository->id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        unset($this->data['data']['attributes']);

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
        $this->validateResponse($response, 200);

        $attributes = $this->repository->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->repository->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testRelationshipFails()
    {
        $this->data['relationships'] = [
            'organization' => [
                'data' => [
                    'type' => 'organizations',
                    'id'   => $this->organization->id,
                ],
            ],
        ];

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/relationships']],
            ],
        ]);
    }

    public function testMissingTypeFails()
    {
        unset($this->data['data']['type']);

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
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
        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }

    public function testUpdatePermissions()
    {
        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
        $this->validateResponse($response, 200);

        $this->removeUser();

        $response = $this->patch(route('api.repositories.update', $this->repository->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->patch(route('api.repositories.update', 'missing'), $this->data);
        $this->validateResponse($response, 404);
    }
}
