<?php

namespace Tests\Feature\API\Packages\Builds;

use Tests\Feature\API\Packages\PackageTest;

final class DeleteTest extends PackageTest
{
    public function testDestroyNotAllowed()
    {
        $response = $this->delete($this->getRoute('builds.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
