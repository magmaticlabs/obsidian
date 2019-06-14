<?php

namespace Tests\Feature\API\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\IndexTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class IndexTest extends IndexTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'packages';

    /**
     * {@inheritdoc}
     */
    protected $class = Package::class;

    /**
     * {@inheritdoc}
     */
    protected function createModel(int $times = 1)
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        if (1 === $times) {
            return $this->factory($this->class)->create([
                'repository_id' => $repository->id,
            ]);
        }

        return $this->factory($this->class)->times($times)->create([
            'repository_id' => $repository->id,
        ]);
    }
}
