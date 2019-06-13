<?php

namespace Tests\Feature\API\Builds;

/**
 * @internal
 * @coversNothing
 */
final class UpdateTest extends BuildTest
{
    public function testUpdateNotAllowed()
    {
        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 405);
    }
}
