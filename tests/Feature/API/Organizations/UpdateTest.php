<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\UpdateTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class UpdateTest extends UpdateTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'organizations';

    /**
     * {@inheritdoc}
     */
    protected $class = Organization::class;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Organization $organization */
        $organization = $this->model;

        $organization->addMember($this->user);
        $organization->promoteMember($this->user);
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

    public function testNameDuplicateCausesError()
    {
        $this->factory(Organization::class)->create(['name' => 'duplicate']);

        $attributes = $this->getValidAttributes();
        $attributes['name'] = 'duplicate';

        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
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

    public function testUpdatePermissions()
    {
        /** @var Organization $organization */
        $organization = $this->model;
        $organization->demoteMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $this->getValidAttributes(),
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 403);
    }
}
