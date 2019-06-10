<?php

namespace Tests\Feature\API\Builds;

use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTest\ResourceTest;

abstract class BuildTest extends ResourceTest
{
    /**
     * Resource type
     *
     * @var string
     */
    protected $type = 'builds';

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
     * Package
     *
     * @var Package
     */
    protected $package;

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

        $this->package = $this->factory(Package::class)->create([
            'repository_id' => $this->repository->id,
        ]);

        $this->model = $this->factory(Build::class)->create([
            'package_id' => $this->package->id,
        ]);

        $this->data = [
            'data' => [
                'type' => 'builds',
            ],
            'relationships' => [
                'package' => [
                    'data' => [
                        'type' => 'packages',
                        'id'   => $this->package->id,
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
        return ['package_id' => $this->package->id];
    }
}
