<?php

namespace Tests\Feature\API\Organizations\Repositories;

use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use Tests\Feature\API\Organizations\OrganizationTest;

final class RelationshipTest extends OrganizationTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('repositories', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, count($data['data']));

        // --

        $count = 5;

        factory(Repository::class)->times($count)->create([
            'organization_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('repositories', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testCorrectData()
    {
        $repository = factory(Repository::class)->create([
            'organization_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('repositories', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $repository->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'repositories',
                    'id'         => $repository->id,
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('repositories', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
