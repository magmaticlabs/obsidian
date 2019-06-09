<?php

namespace Tests\Feature\API\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;

final class UpdateTest extends RepositoryTest
{
    use \Tests\Feature\API\ResourceTest\UpdateTest;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->data['data']['id'] = $this->model->id;
        unset($this->data['relationships']);
    }

    // --

    public function testPermissions()
    {
        $this->removeUser();

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 403);
    }

    /**
     * @dataProvider invalidDataName
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
     * @dataProvider invalidDataDisplayName
     */
    public function testValidateDisplayName($value)
    {
        $this->data['data']['attributes']['display_name'] = $value;

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
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

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/description']],
            ],
        ]);
    }

    public function testNameDuplicateCausesError()
    {
        factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => $this->organization->id,
        ]);
        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/name']],
            ],
        ]);
    }

    public function testNameDuplicateAnotherOrgSuccess()
    {
        factory(Repository::class)->create([
            'name'            => 'duplicate',
            'organization_id' => factory(Organization::class)->create()->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 200);
    }
}
