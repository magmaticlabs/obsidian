<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\DeleteTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class DeleteTest extends DeleteTestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Organization $organization */
        $organization = $this->model;

        $organization->addMember($this->user);
        $organization->promoteMember($this->user);
    }

    public function testDeletePermissions()
    {
        /** @var Organization $organization */
        $organization = $this->model;
        $organization->demoteMember($this->user);

        $response = $this->delete($this->route('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
