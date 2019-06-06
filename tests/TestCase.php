<?php

namespace Tests;

use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Opis\JsonSchema\Schema as JSONSchema;
use Opis\JsonSchema\Validator;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Faker utility
     *
     * @var \Faker\Factory
     */
    protected $faker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();
        $this->faker->seed(env('TEST_SEED', null));
    }

    final protected function validateResponse(TestResponse $response, int $status)
    {
        $response->assertStatus($status);

        $content = $response->getContent();
        if (!empty($content)) {
            $this->assertJSONSchema($content, 'jsonapi');
        }
    }

    /**
     * Validate that a given string is validate JSON-API
     *
     * @param string $data
     */
    final protected function validateJSONAPI(string $data): void
    {
        if (empty($data)) {
            return;
        }

        static $schema;

        if (null === $schema) {
            $schema = JSONSchema::fromJsonString(file_get_contents(base_path('tests/jsonapi_schema.json')));
        }

        $validator = new Validator();

        /** @var \Opis\JsonSchema\ValidationResult $result */
        $result = $validator->schemaValidation(json_decode($data), $schema);

        $this->assertTrue($result->isValid());
    }

    /**
     * Assert that the given string is valid JSON Schema
     *
     * @param string $data
     */
    final protected function assertJSONSchema(string $data, string $schema): void
    {
        /** @var \Opis\JsonSchema\Schema[] $schemas */
        static $schemas;

        if (!isset($schemas[$schema])) {
            $schemapath = base_path(sprintf('tests/schemas/%s.json', $schema));

            if (!file_exists($schemapath)) {
                throw new \InvalidArgumentException('Unknown schema');
            }

            $schemas[$schema] = JSONSchema::fromJsonString(file_get_contents($schemapath));
        }

        $validator = new Validator();

        /** @var \Opis\JsonSchema\ValidationResult $result */
        $result = $validator->schemaValidation(json_decode($data), $schemas[$schema]);

        $this->assertTrue($result->isValid(), 'Schema validation failed');
    }
}
