<?php

namespace Tests\Feature\API\Packages\Builds;

use Tests\Feature\API\Packages\PackageTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends PackageTestCase
{
    public function testDestroyNotAllowed()
    {
        $response = $this->delete($this->getRoute('builds.create', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
