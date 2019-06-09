<?php

namespace Tests\Feature\API\Builds;

final class CreateTest extends BuildTest
{
    use \Tests\Feature\API\ResourceTest\CreateTest;

    protected $required = [
        'ref',
    ];

    // --

    public function testCreatePermissions()
    {
        $this->removeUser();

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 403);
    }

    /**
     * @dataProvider invalidDataRef
     */
    public function testValidateRef($value)
    {
        $this->data['data']['attributes']['ref'] = $value;

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/ref']],
            ],
        ]);
    }
}
