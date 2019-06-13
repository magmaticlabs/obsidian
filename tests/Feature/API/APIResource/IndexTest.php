<?php

namespace Tests\Feature\API\APIResource;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\APIResource\ResourceTestCase
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
        $class = \get_class($this->model);
        $class::query()->delete();

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(0, \count($data['data']));

        // --

        $count = 5;

        $this->factory(\get_class($this->model))->times($count)->create($this->factoryArgs());

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($count, \count($data['data']));
    }
}
