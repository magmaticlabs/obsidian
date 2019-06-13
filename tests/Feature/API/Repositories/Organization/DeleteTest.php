<?php

namespace Tests\Feature\API\Repositories\Organization;

use Tests\Feature\API\Repositories\RepositoryTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends RepositoryTestCase
{
    public function testDeleteNotAllowed()
    {
        $response = $this->delete($this->getRoute('organization.destroy', $this->model->id));
        $this->validateResponse($response, 405);
    }
}
