<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\DeleteTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RepositoryController
 */
final class DeleteTest extends DeleteTestCase
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
     * Organization.
     *
     * @var Organization
     */
    private $organization;

    public function testDeletePermissions()
    {
        $this->organization->removeMember($this->user);

        $response = $this->delete($this->route('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel(int $times = 1)
    {
        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        if (1 === $times) {
            return $this->factory($this->class)->create([
                'organization_id' => $this->organization->id,
            ]);
        }

        return $this->factory($this->class)->times($times)->create([
            'organization_id' => $this->organization->id,
        ]);
    }
}
