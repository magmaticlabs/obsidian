<?php

namespace Tests\Feature\API\Repositories\Organization;

use Tests\Feature\API\Repositories\RepositoryTest;

final class CreateTest extends RepositoryTest
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('organization.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
