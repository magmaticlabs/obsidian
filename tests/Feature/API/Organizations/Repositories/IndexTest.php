<?php

namespace Tests\Feature\API\Organizations\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\Organizations\OrganizationTest;

final class IndexTest extends OrganizationTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('repositories.index', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, count($data['data']));

        // --

        $count = 5;

        $this->factory(Repository::class)->times($count)->create([
            'organization_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('repositories.index', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testCorrectData()
    {
        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('repositories.index', $this->model->id));
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'repositories',
                    'id'         => $repository->id,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('repositories.index', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
