<?php

namespace Tests\Feature\API\Builds;

use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\RelationshipTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\BuildController
 */
final class PackageRelationshipTest extends RelationshipTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'builds';

    /**
     * {@inheritdoc}
     */
    protected $class = Build::class;

    /**
     * {@inheritdoc}
     */
    protected $relationship = 'package';

    /**
     * {@inheritdoc}
     */
    protected $relationship_type = 'packages';

    /**
     * {@inheritdoc}
     */
    protected $relationship_plurality = self::SINGULAR;

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

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        if (1 === $times) {
            return $this->factory($this->class)->create([
                'package_id' => $package->id,
            ]);
        }

        return $this->factory($this->class)->times($times)->create([
            'package_id' => $package->id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRelationshipModel(Model $parent, int $times = 1)
    {
        return $parent->package;
    }
}
