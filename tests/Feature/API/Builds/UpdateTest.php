<?php

namespace Tests\Feature\API\Builds;

final class UpdateTest extends BuildTest
{
    public function testUpdateNotAllowed()
    {
        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 405);
    }
}
