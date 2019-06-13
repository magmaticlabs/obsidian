<?php

namespace Tests\Feature\API\Builds\Package;

use Tests\Feature\API\Builds\BuildTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends BuildTestCase
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('package.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
