<?php

namespace Tests\Feature\API\Builds;

final class DeleteTest extends BuildTest
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
