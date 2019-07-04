<?php

namespace Tests\Feature\API\Organizations;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class IndexTest extends ResourceTestCase
{
    use TestIndexEndpoints;

    protected $resourceType = 'organizations';

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        return $this->factory(Organization::class)->create();
    }
}
