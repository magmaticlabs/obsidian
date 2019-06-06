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

    /**
     * Validate that a response has the specified status code, and conforms to schema
     *
     * @param TestResponse $response
     * @param int          $status
     */
    final protected function validateResponse(TestResponse $response, int $status): void
    {
        $response->assertStatus($status);

        $content = $response->getContent();

        if (204 === $status) {
            $this->assertEmpty($content);
        } elseif (!empty($content)) {
            $this->assertJSONSchema($content, 'jsonapi');
        }
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
