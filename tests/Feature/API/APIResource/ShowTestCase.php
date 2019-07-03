<?php

namespace Tests\Feature\API\APIResource;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

abstract class ShowTestCase extends ResourceTestCase
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

    /**
     * @test
     */
    public function data_matches_model()
    {
        $response = $this->get($this->route('show', $this->model->id));
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

    /**
     * @test
     */
    public function non_exist()
    {
        $response = $this->get($this->route('show', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
