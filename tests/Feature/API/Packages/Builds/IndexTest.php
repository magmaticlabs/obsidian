<?php

namespace Tests\Feature\API\Packages\Builds;

use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use Tests\Feature\API\Packages\PackageTest;

/**
 * @internal
 * @coversNothing
 */
final class IndexTest extends PackageTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('builds.index', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame(0, \count($data['data']));

        // --

        $count = 5;

        $this->factory(Build::class)->times($count)->create([
            'package_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('builds.index', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame($count, \count($data['data']));
    }

    public function testCorrectData()
    {
        $build = $this->factory(Build::class)->create([
            'package_id' => $this->model->id,
        ]);

        $response = $this->get($this->getRoute('builds.index', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $build->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type' => 'builds',
                    'id'   => $build->id,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('builds.index', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
