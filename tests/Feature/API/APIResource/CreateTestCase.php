<?php

namespace Tests\Feature\API\APIResource;

abstract class CreateTestCase extends ResourceTestCase
{
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
     * Data Provider for required attributes.
     *
     * @return array
     */
    public function requiredAttributesProvider(): array
    {
        return [['']];
    }

    /**
     * Data Provider for optional attributes.
     *
     * @return array
     */
    public function optionalAttributesProvider(): array
    {
        return [['', '']];
    }

    /**
     * Required parent relationship.
     *
     * @return array
     */
    public function getParentRelationship(): array
    {
        return [];
    }

    /**
     * @dataProvider validAttributesProvider
     */
    public function testSucceedsWithValidAttributes(array $attributes)
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 201);

        $data = [
            'type'       => $this->type,
            'attributes' => $attributes,
        ];

        $response->assertJson([
            'data' => $data,
        ]);
    }

    public function testLocationHeaderIncluded()
    {
        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $this->getValidAttributes(),
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $location = $response->headers->get('Location');
        $resourceid = basename($location);
        static::assertSame($this->route('show', $resourceid), $location);
    }

    /**
     * @dataProvider invalidAttributesProvider
     */
    public function testCreateFailsWithInvalidAttributes(array $attributes, string $invalid)
    {
        if (empty($attributes)) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => sprintf('/data/attributes/%s', $invalid)]],
            ],
        ]);
    }

    public function testInclusionOfClientIDCausesValidationError()
    {
        $data = [
            'data' => [
                'type' => $this->type,
                'id'   => 'foobar',
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testMissingOrInvalidTypeCausesValidationError()
    {
        $data = [
            'data' => [],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post($this->route('create'), $data);
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

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    /**
     * @dataProvider requiredAttributesProvider
     */
    public function testMissingRequiredAttributeCausesValidationError(string $attribute)
    {
        if (empty($attribute)) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $attributes = $this->getValidAttributes();
        unset($attributes[$attribute]);

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => sprintf('/data/attributes/%s', $attribute)]],
            ],
        ]);
    }

    /**
     * @dataProvider optionalAttributesProvider
     *
     * @param mixed $value
     */
    public function testMissingOptionalAttributesSetToDefault(string $attribute, $value)
    {
        if (empty($attribute)) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $attributes = $this->getValidAttributes();
        unset($attributes[$attribute]);

        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
        ];

        if ($relationship = $this->getParentRelationship()) {
            $data['relationships'] = $relationship;
        }

        if (\is_string($value) && preg_match('/^%(.+)%$/', $value, $matches)) {
            $value = $attributes[$matches[1]];
        }

        $response = $this->post($this->route('create'), $data);
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
