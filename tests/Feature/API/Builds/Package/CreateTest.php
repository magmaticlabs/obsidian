<?php

namespace Tests\Feature\API\Builds\Package;

use Tests\Feature\API\Builds\BuildTestCase;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends BuildTestCase
{
    public function testCreateNotAllowed()
    {
        $response = $this->post($this->getRoute('package.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
