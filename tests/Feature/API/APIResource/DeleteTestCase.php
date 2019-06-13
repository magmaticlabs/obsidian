<?php

namespace Tests\Feature\API\APIResource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

abstract class DeleteTestCase extends ResourceTestCase
{
    /**
     * Model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = $this->createModel();
    }

    public function testSuccessful()
    {
        $response = $this->delete($this->route('destroy', $this->model->id));
        $this->validateResponse($response, 204);
    }

    public function testResourceDeleted()
    {
        $this->delete($this->route('destroy', $this->model->id));
        $this->expectException(ModelNotFoundException::class);
        $this->model->refresh();
    }

    public function testNonExist()
    {
        $response = $this->delete($this->route('destroy', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
