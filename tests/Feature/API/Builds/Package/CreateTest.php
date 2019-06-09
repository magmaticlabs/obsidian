<?php

namespace Tests\Feature\API\Builds\Package;

use Tests\Feature\API\Builds\BuildTest;

final class CreateTest extends BuildTest
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('package.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
