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
     * Determines if the resource is allowed to be deleted.
     *
     * @var bool
     */
    protected $not_allowed = false;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = $this->createModel();
    }

    /**
     * @test
     */
    public function successful()
    {
        $response = $this->delete($this->route('destroy', $this->model->id));

        if ($this->not_allowed) {
            $this->validateResponse($response, 403);
        } else {
            $this->validateResponse($response, 204);
        }
    }

    /**
     * @test
     */
    public function resource_deleted()
    {
        $response = $this->delete($this->route('destroy', $this->model->id));

        if ($this->not_allowed) {
            $this->validateResponse($response, 403);
        } else {
            $this->expectException(ModelNotFoundException::class);
            $this->model->refresh();
        }
    }

    /**
     * @test
     */
    public function non_exist()
    {
        $response = $this->delete($this->route('destroy', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
