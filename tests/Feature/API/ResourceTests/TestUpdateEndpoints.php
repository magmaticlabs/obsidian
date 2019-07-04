<?php

namespace Tests\Feature\API\ResourceTests;

/**
 * @mixin \Tests\Feature\API\ResourceTests\ResourceTestCase
 */
trait TestUpdateEndpoints
{
    public function validUpdateAttributesProvider(): array
    {
        return [
            'null' => [[]],
        ];
    }

    public function invalidUpdateAttributesProvider(): array
    {
        return [
            'null' => [[], ''],
        ];
    }

    /**
     * @test
     * @dataProvider validUpdateAttributesProvider
     */
    public function update_succeeds_with_valid_attributes(array $attributes)
    {
        $resource = $this->createResource();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider invalidUpdateAttributesProvider
     */
    public function update_fails_with_invalid_attributes(array $attributes, string $invalid)
    {
        $resource = $this->createResource();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
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
    public function update_missing_or_invalid_type_causes_validation_error()
    {
        $resource = $this->createResource();

        $data = [
            'data' => [
                'id' => $resource->id,
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);

        $data = [
            'data' => [
                'type' => '__INVALID__',
                'id'   => $resource->id,
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
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
    public function update_missing_or_invalid_id_causes_validation_error()
    {
        $resource = $this->createResource();

        $data = [
            'data' => [],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
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

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
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
    public function update_noop_with_no_attributes()
    {
        $resource = $this->createResource();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => [],
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
        $this->validateResponse($response, 200);

        $attributes = $resource->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    /**
     * @test
     */
    public function update_relationships_cause_validation_error()
    {
        $resource = $this->createResource();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $this->getValidUpdateAttributes(),
            ],
            'relationships' => [],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", $resource->id), $data);
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
    public function update_non_exist()
    {
        $resource = $this->createResource();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $this->getValidUpdateAttributes(),
            ],
        ];

        $response = $this->patch(route("api.{$this->resourceType}.update", '__INVALID__'), $data);
        $this->validateResponse($response, 404);
    }

    /**
     * Helper function to acquire the first set of valid attributes from the provider.
     *
     * @return array
     */
    protected function getValidUpdateAttributes(): array
    {
        $provider = $this->validUpdateAttributesProvider();
        $values = array_values($provider);

        return $values[0][0] ?? null;
    }
}
