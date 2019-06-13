<?php

namespace Tests\Feature\API\Packages;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends PackageTestCase
{
    use \Tests\Feature\API\APIResource\DeleteTest;

    public function testDeletePermissions()
    {
        $this->removeUser();

        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
