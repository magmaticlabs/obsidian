<?php

namespace Tests\Feature\API\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\APIResource\DeleteTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\PackageController
 */
final class DeleteTest extends DeleteTestCase
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
     * Organization.
     *
     * @var Organization
     */
    private $organization;

    /**
     * @test
     */
    public function delete_permissions()
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

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
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
