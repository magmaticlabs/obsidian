<?php

namespace Tests\Feature\API\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTest\ResourceTest;

abstract class PackageTest extends ResourceTest
{
    /**
     * Resource type
     *
     * @var string
     */
    protected $type = 'packages';

    /**
     * Organization
     *
     * @var Organization
     */
    protected $organization;

    /**
     * Repository
     *
     * @var Repository
     */
    protected $repository;

    /**
     * Model instance
     *
     * @var Repository
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        $this->repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->model = $this->factory(Package::class)->create([
            'repository_id' => $this->repository->id,
        ]);

        $this->data = [
            'data' => [
                'type'       => 'packages',
                'attributes' => [
                    'name'     => 'testing',
                    'source'   => 'git@github.com:testing/test.git',
                    'ref'      => 'testing',
                    'schedule' => 'hook',
                ],
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

    /**
     * Remove the authenticated user from the parent organization
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
        return ['repository_id' => $this->repository->id];
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

    public function invalidDataSource()
    {
        return [
            'non-string'  => [[]],
            'non-ssh'     => ['https://github.com/testing/test.git'],
            'bogus'       => ['foobar'],
        ];
    }

    public function invalidDataRef()
    {
        return [
            'non-string'  => [[]],
        ];
    }

    public function validDataSchedule()
    {
        return [
            'enum' => ['nightly', 'weekly', 'hook', 'none'],
        ];
    }

    public function invalidDataSchedule()
    {
        return [
            'non-string'  => [[]],
            'non-enum'    => ['foobar'],
        ];
    }
}
