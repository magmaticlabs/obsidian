<?php

namespace Tests\Feature;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
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

        $this->validateJSONAPI($response->getContent());
    }

    public function testLoginLink()
    {
        $response = $this->get(route('api.root'));

        $response->assertStatus(200);
        $response->assertJson([
            'links' => [
                'login' => route('api.login'),
            ],
        ]);
    }

    public function testNoLoginLinkWhenLoggedIn()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.root'));

        $response->assertStatus(200);

        $body = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('login', $body['links']);
    }

    public function testSessionLinkWhenLoggedIn()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.root'));

        $response->assertStatus(200);

        $response->assertJson([
            'links' => [
                'session' => route('api.session'),
            ],
        ]);
    }
}
