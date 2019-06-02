<?php

namespace Tests;

use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
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
     * Validate that a given string is validate JSON-API
     *
     * @param string $data
     */
    protected function validateJSONAPI(string $data): void
    {
        static $schema;

        if (null === $schema) {
            $schema = JSONSchema::fromJsonString(file_get_contents(base_path('tests/jsonapi_schema.json')));
        }

        $validator = new Validator();

        /** @var \Opis\JsonSchema\ValidationResult $result */
        $result = $validator->schemaValidation(json_decode($data), $schema);

        $this->assertTrue($result->isValid());
    }
}
