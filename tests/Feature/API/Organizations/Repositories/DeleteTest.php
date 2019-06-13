<?php

namespace Tests\Feature\API\Organizations\Repositories;

use Tests\Feature\API\Organizations\OrganizationTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends OrganizationTestCase
{
    public function testDestroyNotAllowed()
    {
        $response = $this->delete($this->getRoute('repositories.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
