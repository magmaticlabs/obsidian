<?php

namespace Tests\Feature\API\Packages\Repository;

use Tests\Feature\API\Packages\PackageTestCase;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends PackageTestCase
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('repository.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
