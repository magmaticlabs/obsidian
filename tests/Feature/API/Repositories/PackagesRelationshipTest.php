<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\RelationshipTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class PackagesRelationshipTest extends RelationshipTestCase
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
     * {@inheritdoc}
     */
    protected $relationship = 'packages';

    /**
     * {@inheritdoc}
     */
    protected $relationship_type = 'packages';

    /**
     * {@inheritdoc}
     */
    protected $relationship_plurality = self::PLURAL;

    /**
     * {@inheritdoc}
     */
    protected function createModel(int $times = 1)
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $organization->addMember($this->user);

        if (1 === $times) {
            return $this->factory($this->class)->create([
                'organization_id' => $organization->id,
            ]);
        }

        return $this->factory($this->class)->times($times)->create([
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRelationshipModel(Model $parent, int $times = 1)
    {
        if (1 === $times) {
            return $this->factory(Package::class)->create([
                'repository_id' => $parent->id,
            ]);
        }

        return $this->factory(Package::class)->times($times)->create([
            'repository_id' => $parent->id,
        ]);
    }
}
