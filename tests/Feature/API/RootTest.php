<?php

namespace Tests\Feature\API;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class RootTest extends TestCase
{
    public function testAvailable()
    {
        $response = $this->get(route('api.root'));
        $this->validateResponse($response, 200);
    }

    public function testFormat()
    {
        $response = $this->get(route('api.root'));

        $response->assertJson([
            'meta'  => [],
            'data'  => null,
            'links' => [
                '_self' => route('api.root'),
            ],
        ]);
    }

    public function testLoginLink()
    {
        $response = $this->get(route('api.root'));

        $response->assertJson([
            'links' => [
                'login' => route('api.login'),
            ],
        ]);
    }

    public function testNoLoginLinkWhenLoggedIn()
    {
        $user = $this->factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.root'));
        $this->validateResponse($response, 200);

        $body = json_decode($response->getContent(), true);
        static::assertArrayNotHasKey('login', $body['links']);
    }

    public function testSessionLinkWhenLoggedIn()
    {
        $user = $this->factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.root'));
        $this->validateResponse($response, 200);

        $response->assertJson([
            'links' => [
                'session' => route('api.session'),
            ],
        ]);
    }
}
