<?php

namespace Tests\Feature\API\Packages\Repository;

use Tests\Feature\API\Packages\PackageTest;

final class DeleteTest extends PackageTest
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('repository.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
