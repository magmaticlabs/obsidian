<?php

namespace Tests\Feature\API\ResourceTest;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\ResourceTest\ResourceTest
 */
trait ShowTest
{
    public function testShow()
    {
        $response = $this->get($this->getRoute('show', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $this->model->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('show', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
