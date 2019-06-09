<?php

namespace Tests\Feature\API\Packages;

final class DeleteTest extends PackageTest
{
    use \Tests\Feature\API\ResourceTest\DeleteTest;

    public function testDeletePermissions()
    {
        $this->removeUser();

        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
