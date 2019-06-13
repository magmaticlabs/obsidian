<?php

namespace Tests\Feature\API\Organizations\Repositories;

use Tests\Feature\API\Organizations\OrganizationTest;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends OrganizationTest
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('repositories.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
