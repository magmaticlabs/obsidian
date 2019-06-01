<?php

namespace Tests\Feature;

use Tests\TestCase;

class RootTest extends TestCase
{
    public function testAvailable()
    {
        $response = $this->get(route('api.root'));

        $response->assertStatus(200);
    }

    public function testFormat()
    {
        $response = $this->get(route('api.root'));

        $response->assertStatus(200);
        $response->assertJson([
            'meta'  => [],
            'data'  => null,
            'links' => [
                '_self' => route('api.root'),
            ],
        ]);
    }
}
