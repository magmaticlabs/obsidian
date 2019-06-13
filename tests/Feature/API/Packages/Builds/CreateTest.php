<?php

namespace Tests\Feature\API\Packages\Builds;

use Tests\Feature\API\Packages\PackageTest;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends PackageTest
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('builds.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
