<?php

namespace Tests\Feature\API;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\RootController
 */
final class RootTest extends TestCase
{
    /**
     * @test
     */
    public function available()
    {
        $response = $this->get(route('api.root'));
        $this->validateResponse($response, 200);
    }

    /**
     * @test
     */
    public function format()
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

    /**
     * @test
     */
    public function login_link()
    {
        $response = $this->get(route('api.root'));

        $response->assertJson([
            'links' => [
                'login' => route('api.auth.login'),
            ],
        ]);
    }

    /**
     * @test
     */
    public function no_login_link_when_logged_in()
    {
        $user = $this->factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.root'));
        $this->validateResponse($response, 200);

        $body = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('login', $body['links']);
    }

    /**
     * @test
     */
    public function session_link_when_logged_in()
    {
        $user = $this->factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.root'));
        $this->validateResponse($response, 200);

        $response->assertJson([
            'links' => [
                'session' => route('api.auth.session'),
            ],
        ]);
    }
}
