<?php

namespace Tests\Feature\API\Builds;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends BuildTestCase
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
