<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;

final class CreateTest extends OrganizationTest
{
    use \Tests\Feature\API\ResourceTest\CreateTest;

    protected $required = [
        'name',
    ];

    protected $optional = [
        'description' => '',
    ];

    // --

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
     * @dataProvider invalidDataDisplayName
     */
    public function testValidateDisplayName($value)
    {
        $this->data['data']['attributes']['display_name'] = $value;

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/display_name']],
            ],
        ]);
    }

    /**
     * @dataProvider invalidDataDescription
     */
    public function testValidateDescription($value)
    {
        $this->data['data']['attributes']['description'] = $value;

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }

    public function testMissingDisplayNameDefaultsToName()
    {
        unset($this->data['data']['attributes']['display_name']);

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 201);

        $this->data['data']['attributes']['display_name'] = $this->data['data']['attributes']['name'];

        $response->assertJson([
            'data' => [
                'attributes' => $this->data['data']['attributes'],
            ],
        ]);
    }

    public function testNameDuplicateCausesError()
    {
        factory(Organization::class)->create(['name' => 'duplicate']);
        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }
}
