<?php

namespace Tests\Feature\API\ResourceTest;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\ResourceTest\ResourceTest
 */
trait IndexTest
{
    public function testDataMatchesShow()
    {
        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $compare = $this->get($this->getRoute('show', $this->model->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountsMatches()
    {
        // Empty the collection
        $class = get_class($this->model);
        $class::query()->delete();

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, count($data['data']));

        // --

        $count = 5;

        factory(get_class($this->model))->times($count)->create();

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }
}