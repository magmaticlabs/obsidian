<?php

namespace Tests\Feature\API\APIResource;

abstract class CreateTestCase extends ResourceTestCase
{
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

        $response = $this->post($this->route('create'), $data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $location = $response->headers->get('Location');
        $resourceid = basename($location);
        static::assertSame($this->route('show', $resourceid), $location);

        $data = [
            'type'       => $this->type,
            'id'         => $resourceid,
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
        $data = [
            'data' => [
                'type'       => $this->type,
                'attributes' => $attributes,
            ],
        ];

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
