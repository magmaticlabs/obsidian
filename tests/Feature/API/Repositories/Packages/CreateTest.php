<?php

namespace Tests\Feature\API\Repositories\Packages;

use Tests\Feature\API\Repositories\RepositoryTest;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends RepositoryTest
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('packages.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
