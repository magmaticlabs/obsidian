<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;

/**
 * @internal
 * @coversNothing
 */
final class CreateTest extends RepositoryTestCase
{
    use \Tests\Feature\API\APIResource\CreateTest;

    protected $required = [
        'name',
    ];

    protected $optional = [
        'description' => '',
    ];

    // --

    public function testPermissions()
    {
        $this->removeUser();

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 403);
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
     * @dataProvider invalidDataDisplayName
     *
     * @param mixed $value
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
     *
     * @param mixed $value
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
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->organization->id,
        ]);
        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateAnotherOrgSuccess()
    {
        $this->factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->factory(Organization::class)->create()->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 201);
    }
}
