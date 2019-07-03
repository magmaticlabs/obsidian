<?php

namespace Tests\Feature\API\APIResource;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

abstract class UpdateTestCase extends ResourceTestCase
{
    /**
     * Model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Determines if the resource is not allowed to be updated.
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
     * Data Provider for valid attributes.
     *
     * @return array
     */
    public function validAttributesProvider(): array
    {
        return [
            'null' => [[]],
        ];
    }

    /**
     * Data Provider for invalid attributes.
     *
     * @return array
     */
    public function invalidAttributesProvider(): array
    {
        return [
            'null' => [[], ''],
        ];
    }

    /**
     * @dataProvider validAttributesProvider
     *
     * @test
     */
    public function succeeds_with_valid_attributes(array $attributes)
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        if ($this->not_allowed) {
            $this->validateResponse($response, 403);

            return;
        }

        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    /**
     * @dataProvider invalidAttributesProvider
     *
     * @test
     */
    public function create_fails_with_invalid_attributes(array $attributes, string $invalid)
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        if ($this->not_allowed) {
            $this->validateResponse($response, 403);

            return;
        }

        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => sprintf('/data/attributes/%s', $invalid)]],
            ],
        ]);
    }

    /**
     * @test
     */
    public function missing_or_invalid_type_causes_validation_error()
    {
        $data = [
            'data' => [
                'id' => $this->model->id,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        if ($this->not_allowed) {
            $this->validateResponse($response, 403);

            return;
        }

        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);

        $data = [
            'data' => [
                'type' => '__INVALID__',
                'id'   => $this->model->id,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    /**
     * @test
     */
    public function missing_or_invalid_id_causes_validation_error()
    {
        $data = [
            'data' => [],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        if ($this->not_allowed) {
            $this->validateResponse($response, 403);

            return;
        }

        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);

        $data = [
            'data' => [
                'id' => '__INVALID__',
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    /**
     * @test
     */
    public function noop_with_no_attributes()
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => [],
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        if ($this->not_allowed) {
            $this->validateResponse($response, 403);

            return;
        }

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
    public function relationships_cause_validation_error()
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $this->getValidAttributes(),
            ],
            'relationships' => [],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        if ($this->not_allowed) {
            $this->validateResponse($response, 403);

            return;
        }

        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/relationships']],
            ],
        ]);
    }

    /**
     * @test
     */
    public function non_exist()
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $this->getValidAttributes(),
            ],
        ];

        $response = $this->patch($this->route('update', '__INVALID__'), $data);
        $this->validateResponse($response, 404);
    }

    /**
     * Helper function to acquire the first set of valid attributes from the provider.
     *
     * @return array
     */
    protected function getValidAttributes(): array
    {
        $provider = $this->validAttributesProvider();
        $values = array_values($provider);

        return $values[0][0] ?? null;
    }
}
