<?php

namespace Tests\Feature\API\Builds;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends BuildTestCase
{
    use \Tests\Feature\API\APIResource\CreateTest;

    public function testCreatePermissions()
    {
        $this->removeUser();

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 403);
    }
}
