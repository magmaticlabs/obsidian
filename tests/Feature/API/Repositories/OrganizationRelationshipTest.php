<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\RelationshipTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class OrganizationRelationshipTest extends RelationshipTestCase
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
    protected $relationship = 'organization';

    /**
     * {@inheritdoc}
     */
    protected $relationship_type = 'organizations';

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
        return $parent->organization;
    }
}
