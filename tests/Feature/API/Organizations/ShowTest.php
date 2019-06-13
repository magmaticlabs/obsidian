<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\ShowTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class ShowTest extends ShowTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'organizations';

    /**
     * {@inheritdoc}
     */
    protected $class = Organization::class;
}
