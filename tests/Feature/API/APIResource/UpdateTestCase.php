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
    abstract public function validAttributesProvider(): array;

    /**
     * Data Provider for invalid attributes.
     *
     * @return array
     */
    abstract public function invalidAttributesProvider(): array;

    /**
     * @dataProvider validAttributesProvider
     */
    public function testSucceedsWithValidAttributes(array $attributes)
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
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
     */
    public function testCreateFailsWithInvalidAttributes(array $attributes, string $invalid)
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => sprintf('/data/attributes/%s', $invalid)]],
            ],
        ]);
    }

    public function testMissingOrInvalidTypeCausesValidationError()
    {
        $data = [
            'data' => [
                'id' => $this->model->id,
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
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

    public function testMissingOrInvalidIDCausesValidationError()
    {
        $data = [
            'data' => [],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
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

    public function testNoopWithNoAttributes()
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => [],
            ],
        ];

        $response = $this->patch($this->route('update', $this->model->id), $data);
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

    public function testRelationshipsCauseValidationError()
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
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/relationships']],
            ],
        ]);
    }

    public function testNonExist()
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
