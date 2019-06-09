<?php

namespace Tests\Feature\API\Packages\Repository;

use Tests\Feature\API\Packages\PackageTest;

final class CreateTest extends PackageTest
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('repository.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
