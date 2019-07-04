<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\TestResponse;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

abstract class APITestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null,
            '__TESTING__',
            'http://localhost'
        );
    }

    /**
     * Validate that a response has the specified status code, and conforms to schema.
     *
     * @param TestResponse $response
     * @param int          $status
     */
    final protected function validateResponse(TestResponse $response, int $status): void
    {
        if (500 === $response->status()) {
            $error = $response->json('errors.0');
            $this->fail("API test produced an {$error['title']}: {$error['detail']}");
        }

        $response->assertStatus($status);

        $content = $response->getContent();

        if (204 === $status) {
            $this->assertEmpty($content);
        } elseif (!empty($content)) {
            $this->assertJSONSchema($content, 'jsonapi');
        }
    }
}
