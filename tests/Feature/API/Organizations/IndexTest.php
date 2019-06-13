<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\IndexTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\OrganizationController
 */
final class IndexTest extends IndexTestCase
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
