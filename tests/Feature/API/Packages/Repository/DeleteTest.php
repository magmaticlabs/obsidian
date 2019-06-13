<?php

namespace Tests\Feature\API\Packages\Repository;

use Tests\Feature\API\Packages\PackageTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends PackageTestCase
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('repository.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
