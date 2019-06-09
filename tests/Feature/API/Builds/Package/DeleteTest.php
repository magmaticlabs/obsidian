<?php

namespace Tests\Feature\API\Builds\Package;

use Tests\Feature\API\Builds\BuildTest;

final class DeleteTest extends BuildTest
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('package.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
