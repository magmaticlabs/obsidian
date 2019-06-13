<?php

namespace Tests\Feature\API\Repositories\Packages;

use Tests\Feature\API\Repositories\RepositoryTest;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends RepositoryTest
{
    public function testDestroyNotAllowed()
    {
        $response = $this->delete($this->getRoute('packages.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
