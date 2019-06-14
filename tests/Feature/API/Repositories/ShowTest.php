<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\ShowTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class ShowTest extends ShowTestCase
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
}
