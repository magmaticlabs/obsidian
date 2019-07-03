<?php

namespace Tests;

use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Opis\JsonSchema\Schema as JSONSchema;
use Opis\JsonSchema\Validator;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Faker utility.
     *
     * @var \Faker\Generator
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
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     */
    final protected function factory(string $class, string $name = 'default')
    {
        $factory = EloquentFactory::construct($this->faker);

        return $factory->of($class, $name);
    }

    /**
     * Assert that the given string is valid JSON Schema.
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

    final protected function sortData(array $array, $key)
    {
        usort($array, function ($a, $b) use ($key) {
            $keys = explode('.', $key);
            foreach ($keys as $key) {
                $key = trim($key);
                if (empty($key)) {
                    continue;
                }

                $a = $a[$key];
                $b = $b[$key];
            }

            return strcmp((string) $a, (string) $b);
        });

        return $array;
    }
}
