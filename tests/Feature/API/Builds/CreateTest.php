<?php

namespace Tests\Feature\API\Builds;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends BuildTest
{
    use \Tests\Feature\API\ResourceTest\CreateTest;

    public function testCreatePermissions()
    {
        $this->removeUser();

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 403);
    }
}
