<?php

namespace Tests\Feature\API\Packages\Builds;

use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use Tests\Feature\API\Packages\PackageTest;

final class RelationshipTest extends PackageTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('builds', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, count($data['data']));

        // --

        $count = 5;

        $this->factory(Build::class)->times($count)->create([
            'package_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('builds', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testCorrectData()
    {
        $build = $this->factory(Build::class)->create([
            'package_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('builds', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $build->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'builds',
                    'id'         => $build->id,
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('builds', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
