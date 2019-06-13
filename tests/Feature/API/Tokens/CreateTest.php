<?php

namespace Tests\Feature\API\Tokens;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends TokenTestCase
{
    use \Tests\Feature\API\APIResource\CreateTest;

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
        static::assertArrayHasKey('accessToken', $attr);
        static::assertNotEmpty($attr['accessToken']);
    }

    /**
     * @dataProvider invalidDataName
     *
     * @param mixed $value
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
     *
     * @param mixed $value
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
