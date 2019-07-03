<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\CreateTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class CreateTest extends CreateTestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);
    }

    /**
     * @test
     */
    public function permissions()
    {
        $this->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $this->getValidAttributes(),
            ],
            'relationships' => $this->getParentRelationship(),
        ];

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentRelationship(): array
    {
        return [
            'organization' => [
                'data' => [
                    'type' => 'organizations',
                    'id'   => $this->organization->id,
                ],
            ],
        ];
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

        $attributes = $this->getValidAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
            'relationships' => $this->getParentRelationship(),
        ];

        $response = $this->post($this->route('create'), $data);
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
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->factory(Organization::class)->create()->id,
        ]);

        $attributes = $this->getValidAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
            'relationships' => $this->getParentRelationship(),
        ];

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 201);
    }

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
                'name'         => 'testing',
                'display_name' => [],
            ], 'display_name'],
            'nonstring-description' => [[
                'name'        => 'testing',
                'description' => [],
            ], 'description'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function requiredAttributesProvider(): array
    {
        return [
            'required' => ['name'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function optionalAttributesProvider(): array
    {
        return [
            'description' => ['description', ''],
        ];
    }
}
