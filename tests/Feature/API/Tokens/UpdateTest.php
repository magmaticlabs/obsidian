<?php

namespace Tests\Feature\API\Tokens;

/**
 * @internal
 * @coversNothing
 */
final class UpdateTest extends TokenTestCase
{
    use \Tests\Feature\API\APIResource\UpdateTest;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->data['data']['id'] = $this->model->id;
    }

    // --

    /**
     * @dataProvider invalidDataName
     *
     * @param mixed $value
     */
    public function testValidateName($value)
    {
        $this->data['data']['attributes']['name'] = $value;

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
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

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/scopes']],
            ],
        ]);
    }
}
