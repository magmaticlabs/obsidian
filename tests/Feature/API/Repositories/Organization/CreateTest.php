<?php

namespace Tests\Feature\API\Repositories\Organization;

use Tests\Feature\API\Repositories\RepositoryTestCase;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends RepositoryTestCase
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('organization.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
