<?php

namespace Tests\Feature\API\Organizations\Repositories;

use Tests\Feature\API\Organizations\OrganizationTestCase;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends OrganizationTestCase
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('repositories.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
