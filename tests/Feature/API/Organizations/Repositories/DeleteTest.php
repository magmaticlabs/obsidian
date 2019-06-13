<?php

namespace Tests\Feature\API\Organizations\Repositories;

use Tests\Feature\API\Organizations\OrganizationTest;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends OrganizationTest
{
    public function testDestroyNotAllowed()
    {
        $response = $this->delete($this->getRoute('repositories.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
