<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\ResourceTestCase;

abstract class RepositoryTestCase extends ResourceTestCase
{
    /**
     * Resource type.
     *
     * @var string
     */
    protected $type = 'repositories';

    /**
     * Organization.
     *
     * @var Organization
     */
    protected $organization;

    /**
     * Model instance.
     *
     * @var Repository
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        $this->model = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->data = [
            'data' => [
                'type'       => 'repositories',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test repository',
                ],
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

    public function invalidDataName()
    {
        return [
            'non-string'  => [[]],
            'too-short'   => ['no'],
            'regex-break' => ['This is Illegal!'],
        ];
    }

    public function invalidDataDisplayName()
    {
        return [
            'non-string' => [[]],
            'too-short'  => ['no'],
        ];
    }

    public function invalidDataDescription()
    {
        return [
            'non-string' => [[]],
        ];
    }

    /**
     * Remove the authenticated user from the parent organization.
     */
    protected function removeUser()
    {
        $this->organization->removeMember($this->user);
    }

    /**
     * {@inheritdoc}
     */
    protected function factoryArgs(): array
    {
        return ['organization_id' => $this->organization->id];
    }
}