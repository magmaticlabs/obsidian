<?php

namespace Tests\Feature\API\APIResource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\APIResource\ResourceTestCase
 */
trait DeleteTest
{
    public function testDelete()
    {
        $response = $this->delete($this->getRoute('destroy', $this->model->id));
        $this->validateResponse($response, 204);
    }

    public function testDeleteActuallyWorks()
    {
        // @var \Tests\Feature\API\ResourceTest\ResourceTest $this
        $this->delete($this->getRoute('destroy', $this->model->id));
        $this->expectException(ModelNotFoundException::class);
        $this->model->refresh();
    }

    public function testNonExist()
    {
        $response = $this->delete($this->getRoute('destroy', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
