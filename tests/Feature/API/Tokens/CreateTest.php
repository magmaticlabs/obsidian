<?php

namespace Tests\Feature\API\Tokens;

final class CreateTest extends TokenTest
{
    use \Tests\Feature\API\ResourceTest\CreateTest;

    protected $required = [
        'name',
    ];

    protected $optional = [
        'scopes' => [],
    ];

    // --

    public function testHasAccessToken()
    {
        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 201);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertArrayHasKey('accessToken', $attr);
        $this->assertNotEmpty($attr['accessToken']);
    }

    /**
     * @dataProvider invalidDataName
     */
    public function testValidateName($value)
    {
        $this->data['data']['attributes']['name'] = $value;

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    /**
     * @dataProvider invalidDataScopes
     */
    public function testValidateScopes($value)
    {
        $this->data['data']['attributes']['scopes'] = $value;

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/scopes']],
            ],
        ]);
    }
}
