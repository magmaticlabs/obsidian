<?php

namespace Tests\Feature\API\Packages;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
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
     * Repository to create the package in
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
            'name'     => 'testing',
            'source'   => 'git@github.com:testing/test.git',
            'ref'      => 'testing',
            'schedule' => 'hook',
        ];

        $this->data = [
            'data' => [
                'type'       => 'packages',
                'attributes' => $this->attributes,
            ],
            'relationships' => [
                'repository' => [
                    'data' => [
                        'type' => 'repositories',
                        'id'   => $this->repository->id,
                    ],
                ],
            ],
        ];
    }

    // --

    public function testCreate()
    {
        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $id,
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testCreatePermissions()
    {
        $this->organization->removeMember($this->user);

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testMissingTypeCausesValidationError()
    {
        unset($this->data['data']['type']);

        $response = $this->post(route('api.packages.create'), $this->data);
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

        $response = $this->post(route('api.packages.create'), $this->data);
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

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testMissingSourceCausesValidationError()
    {
        unset($this->data['data']['attributes']['source']);

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/source']],
            ],
        ]);
    }

    public function testMissingRefDefaultsToMaster()
    {
        unset($this->data['data']['attributes']['ref']);

        $response = $this->post(route('api.packages.create'), $this->data);

        $this->attributes['ref'] = 'master';

        $response->assertJson([
            'data' => [
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testMissingScheduleDefaultsToHook()
    {
        unset($this->data['data']['attributes']['schedule']);

        $response = $this->post(route('api.packages.create'), $this->data);

        $this->attributes['schedule'] = 'hook';

        $response->assertJson([
            'data' => [
                'attributes' => $this->attributes,
            ],
        ]);
    }

    public function testNonStringNameCausesError()
    {
        $this->data['data']['attributes']['name'] = [];

        $response = $this->post(route('api.packages.create'), $this->data);
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

        $response = $this->post(route('api.packages.create'), $this->data);
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

        $response = $this->post(route('api.packages.create'), $this->data);
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
            'name'          => 'duplicate',
            'repository_id' => $this->repository->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateAnotherRepoSuccess()
    {
        factory(Package::class)->create([
            'name'            => 'duplicate',
            'repository_id'   => factory(Repository::class)->create([
                'organization_id' => $this->organization->id,
            ])->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $id = basename($response->headers->get('Location'));

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $id,
                'attributes' => $this->data['data']['attributes'],
            ],
        ]);
    }

    public function testNonStringSourceCausesError()
    {
        $this->data['data']['attributes']['source'] = [];

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/source']],
            ],
        ]);
    }

    public function testSourceInvalidCausesError()
    {
        $this->data['data']['attributes']['source'] = 'https://github.com:invalid/wrong.git';

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/source']],
            ],
        ]);
    }

    public function testNonStringRefCausesError()
    {
        $this->data['data']['attributes']['ref'] = [];

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/ref']],
            ],
        ]);
    }

    public function testInvalidScheduleCausesError()
    {
        $this->data['data']['attributes']['schedule'] = [];

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/schedule']],
            ],
        ]);

        $this->data['data']['attributes']['schedule'] = '__INVALID__';

        $response = $this->post(route('api.packages.create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/schedule']],
            ],
        ]);
    }
}
