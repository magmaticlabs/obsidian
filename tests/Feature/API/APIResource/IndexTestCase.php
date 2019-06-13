<?php

namespace Tests\Feature\API\APIResource;

abstract class IndexTestCase extends ResourceTestCase
{
    public function testDataMatchesShow()
    {
        $model = $this->createModel();

        $response = $this->get($this->route('index'));
        $this->validateResponse($response, 200);

        $compare = $this->get($this->route('show', $model->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountsMatches()
    {
        $response = $this->get($this->route('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame(0, \count($data['data']));

        // --

        $count = 5;

        $this->createModel($count);

        $response = $this->get($this->route('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame($count, \count($data['data']));
    }
}
