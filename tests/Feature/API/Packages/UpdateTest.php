<?php

namespace Tests\Feature\API\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;

final class UpdateTest extends PackageTest
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
     * @dataProvider invalidDataSource
     */
    public function testValidateSource($value)
    {
        $this->data['data']['attributes']['source'] = $value;

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/source']],
            ],
        ]);
    }

    /**
     * @dataProvider invalidDataRef
     */
    public function testValidateRef($value)
    {
        $this->data['data']['attributes']['ref'] = $value;

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/ref']],
            ],
        ]);
    }

    /**
     * @dataProvider invalidDataSchedule
     */
    public function testValidateSchedule($value)
    {
        $this->data['data']['attributes']['schedule'] = $value;

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/attributes/schedule']],
            ],
        ]);
    }

    /**
     * @dataProvider validDataSchedule
     */
    public function testValidateGoodScheduleName($value)
    {
        $this->data['data']['attributes']['schedule'] = $value;

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 200);
    }

    public function testNameDuplicateCausesError()
    {
        $this->factory(Package::class)->create([
            'name'          => 'duplicate',
            'repository_id' => $this->repository->id,
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

    public function testNameDuplicateAnotherRepoSuccess()
    {
        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->factory(Package::class)->create([
            'name'            => 'duplicate',
            'repository_id'   => $repository->id,
        ]);

        $this->data['data']['attributes']['name'] = 'duplicate';

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 200);
    }
}
