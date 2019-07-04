<?php

namespace Tests\Feature\API\Organizations;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class ShowTest extends ResourceTestCase
{
    use TestShowEndpoints;

    protected $resourceType = 'organizations';

    protected function createResource(): EloquentModel
    {
        return $this->factory(Organization::class)->create();
    }
}
