<?php

namespace Tests\Feature\API\Repositories;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends RepositoryTestCase
{
    use \Tests\Feature\API\APIResource\DeleteTest;

    public function testDeletePermissions()
    {
        $this->removeUser();

        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
