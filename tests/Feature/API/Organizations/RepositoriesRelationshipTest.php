<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\RelationshipTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class RepositoriesRelationshipTest extends RelationshipTestCase
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
    protected $relationship = 'repositories';

    /**
     * {@inheritdoc}
     */
    protected $relationship_type = 'repositories';

    /**
     * {@inheritdoc}
     */
    protected $relationship_plurality = self::PLURAL;

    /**
     * {@inheritdoc}
     */
    protected function createRelationshipModel(Model $parent, int $times = 1)
    {
        if (1 === $times) {
            return $this->factory(Repository::class)->create([
                'organization_id' => $parent->id,
            ]);
        }

        return $this->factory(Repository::class)->times($times)->create([
            'organization_id' => $parent->id,
        ]);
    }
}
