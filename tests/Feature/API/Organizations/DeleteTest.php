<?php

namespace Tests\Feature\API\Organizations;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends OrganizationTest
{
    use \Tests\Feature\API\ResourceTest\DeleteTest;

    public function testDeletePermissions()
    {
        $this->demote();

        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
