<?php

namespace Tests\Feature\API\Builds;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\BuildController
 */
final class CreateTest extends ResourceTestCase
{
    use TestCreateEndpoints;

    protected $resourceType = 'builds';

    /**
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->factory(Organization::class)->create();
        $this->organization->addMember($this->user);
    }

    /**
     * @test
     */
    public function permissions()
    {
        $this->organization->removeMember($this->user);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => [],
            ],
            'relationships' => $this->getParentRelationship(),
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 403);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentRelationship(): array
    {
        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
        ]);

        return [
            'package' => [
                'data' => [
                    'type' => 'packages',
                    'id'   => $package->id,
                ],
            ],
        ];
    }
}
