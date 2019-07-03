<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\UpdateTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class UpdateTest extends UpdateTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'repositories';

    /**
     * {@inheritdoc}
     */
    protected $class = Repository::class;

    /**
     * Organization.
     *
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    public function validAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'         => 'testing',
                'display_name' => '__TESTING__',
                'description'  => 'This is a test organization',
            ]],
            'no-description' => [[
                'name'         => 'testing',
                'display_name' => '__TESTING__',
            ]],
            'no-display-name' => [[
                'name'        => 'testing',
                'description' => 'This is a test organization',
            ]],
            'no--name' => [[
                'display_name' => '__TESTING__',
                'description'  => 'This is a test organization',
            ]],
            'fancy-name' => [[
                'name' => 'this-is-a-fancy-name',
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function invalidAttributesProvider(): array
    {
        return [
            'nonstring-name' => [[
                'name' => [],
            ], 'name'],
            'tooshort-name' => [[
                'name' => 'no',
            ], 'name'],
            'invalid-name' => [[
                'name' => 'This is Illegal!',
            ], 'name'],
            'nonstring-display-name' => [[
                'display_name' => [],
            ], 'display_name'],
            'nonstring-description' => [[
                'description' => [],
            ], 'description'],
        ];
    }

    // --

    /**
     * @test
     */
    public function permissions()
    {
        $this->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type' => 'repositories',
                'id'   => $this->model->id,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function name_duplicate_causes_error()
    {
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->model->id,
                'attributes' => [
                    'name' => 'duplicate',
                ],
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    /**
     * @test
     */
    public function name_duplicate_another_org_success()
    {
        $organization = $this->factory(Organization::class)->create();

        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $organization->id,
        ]);

        $data = [
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->model->id,
                'attributes' => [
                    'name' => 'duplicate',
                ],
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 200);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel(int $times = 1)
    {
        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        if (1 === $times) {
            return $this->factory($this->class)->create([
                'organization_id' => $this->organization->id,
            ]);
        }

        return $this->factory($this->class)->times($times)->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    // --

    /*
    public function testPermissions()
    {
        $this->removeUser();

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 403);
    }

    public function testNameDuplicateCausesError()
    {
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->organization->id,
        ]);
        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateAnotherOrgSuccess()
    {
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->factory(Organization::class)->create()->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 200);
    }
    */
}
