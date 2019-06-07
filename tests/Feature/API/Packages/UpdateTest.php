<?php

namespace Tests\Feature\API\Packages;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
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
     * Repository to create the package in
     *
     * @var Repository
     */
    private $repository;

    /**
     * Test package
     *
     * @var Package
     */
    private $package;

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

        $this->package = factory(Package::class)->create([
            'repository_id' => $this->repository->id,
        ]);

        $this->attributes = [
            'name'     => 'updated',
            'source'   => 'git@github.com:updated/updated.git',
            'ref'      => 'updated',
            'schedule' => 'nightly',
        ];

        $this->data = [
            'data' => [
                'type'       => 'packages',
                'id'         => $this->package->id,
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
        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $this->package->id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        unset($this->data['data']['attributes']);

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 200);

        $attributes = $this->package->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $this->package->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testRelationshipFails()
    {
        $newrepo = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->data['relationships'] = [
            'repository' => [
                'data' => [
                    'type' => 'packages',
                    'id'   => $newrepo->id,
                ],
            ],
        ];

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
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

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateCausesError()
    {
        factory(Package::class)->create([
            'name'            => 'duplicate',
            'repository_id'   => $this->repository->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateCrossRepoSuccess()
    {
        factory(Package::class)->create([
            'name'            => 'duplicate',
            'repository_id'   => factory(Repository::class)->create([
                'organization_id' => $this->organization->id,
            ])->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $this->package->id,
                'attributes' => $this->data['data']['attributes'],
            ],
        ]);
    }

    public function testSourceInvalidCausesError()
    {
        $this->data['data']['attributes']['source'] = 'This is illegal!';

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/source']],
            ],
        ]);

        $this->data['data']['attributes']['source'] = 'https://github.com/foobar/foo.git';

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/source']],
            ],
        ]);
    }

    public function testRefInvalidCausesError()
    {
        $this->data['data']['attributes']['ref'] = [];

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/ref']],
            ],
        ]);
    }

    public function testScheduleInvalidCausesError()
    {
        $this->data['data']['attributes']['schedule'] = '__INVALID__';

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/schedule']],
            ],
        ]);

        $this->data['data']['attributes']['schedule'] = [];

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/schedule']],
            ],
        ]);
    }

    public function testUpdatePermissions()
    {
        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 200);

        $this->removeUser();

        $response = $this->patch(route('api.packages.update', $this->package->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testNonExist()
    {
        $response = $this->patch(route('api.packages.update', 'missing'), $this->data);
        $this->validateResponse($response, 404);
    }
}
