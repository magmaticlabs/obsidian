<?php

namespace Tests\Feature\API\Organizations;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends OrganizationTestCase
{
    use \Tests\Feature\API\APIResource\DeleteTest;

    public function testDeletePermissions()
    {
        $this->demote();

        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 403);
    }
}
