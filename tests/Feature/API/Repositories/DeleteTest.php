<?php

namespace Tests\Feature\API\Repositories;

final class DeleteTest extends RepositoryTest
{
    use \Tests\Feature\API\ResourceTest\DeleteTest;

    public function testDeletePermissions()
    {
        $this->removeUser();

        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
