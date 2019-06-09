<?php

namespace Tests\Feature\API\Repositories\Packages;

use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use Tests\Feature\API\Repositories\RepositoryTest;

final class RelationshipTest extends RepositoryTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('packages', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, count($data['data']));

        // --

        $count = 5;

        factory(Package::class)->times($count)->create([
            'repository_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('packages', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testCorrectData()
    {
        $package = factory(Package::class)->create([
            'repository_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('packages', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $package->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'packages',
                    'id'         => $package->id,
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('packages', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
