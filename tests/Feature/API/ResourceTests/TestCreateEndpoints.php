<?php

namespace Tests\Feature\API\ResourceTests;

/**
 * @mixin \Tests\Feature\API\ResourceTests\ResourceTestCase
 */
trait TestCreateEndpoints
{
    public function validAttributesProvider(): array
    {
        return [
            'null' => [[]],
        ];
    }

    public function invalidAttributesProvider(): array
    {
        return [
            'null' => [[], ''],
        ];
    }

    public function requiredAttributesProvider(): array
    {
        return [['']];
    }

    public function optionalAttributesProvider(): array
    {
        return [['', '']];
    }

    public function getParentRelationship(): array
    {
        return [];
    }

    /**
     * @test
     * @dataProvider validAttributesProvider
     */
    public function create_succeeds_with_valid_attributes(array $attributes)
    {
        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 201);

        $data = [
            'type'       => $this->resourceType,
            'attributes' => $attributes,
        ];

        $response->assertJson([
            'data' => $data,
        ]);
    }

    /**
     * @test
     */
    public function create_location_header_included()
    {
        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $this->getValidAttributes(),
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $location = $response->headers->get('Location');
        $resourceid = basename($location);
        $this->assertSame(route("api.{$this->resourceType}.show", $resourceid), $location);
    }

    /**
     * @test
     * @dataProvider invalidAttributesProvider
     */
    public function create_fails_with_invalid_attributes(array $attributes, string $invalid)
    {
        if (empty($attributes)) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
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
    public function create_inclusion_of_client_id_causes_validation_error()
    {
        $data = [
            'data' => [
                'type' => $this->resourceType,
                'id'   => 'foobar',
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
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
    public function create_missing_or_invalid_type_causes_validation_error()
    {
        $data = [
            'data' => [],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);

        $data = [
            'data' => [
                'type' => '__INVALID__',
            ],
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider requiredAttributesProvider
     */
    public function create_missing_required_attribute_causes_validation_error(string $attribute)
    {
        if (empty($attribute)) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $attributes = $this->getValidAttributes();
        unset($attributes[$attribute]);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => sprintf('/data/attributes/%s', $attribute)]],
            ],
        ]);
    }

    /**
     * @test
     * @dataProvider optionalAttributesProvider
     *
     * @param mixed $value
     */
    public function create_missing_optional_attributes_set_to_default(string $attribute, $value)
    {
        if (empty($attribute)) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $attributes = $this->getValidAttributes();
        unset($attributes[$attribute]);

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        if (\is_string($value) && preg_match('/^%(.+)%$/', $value, $matches)) {
            $value = $attributes[$matches[1]];
        }

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 201);

        $response->assertJson([
            'data' => [
                'attributes' => [
                    $attribute => $value,
                ],
            ],
        ]);
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
