<?php

namespace Tests\Feature;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private function attemptLogin($username, $password)
    {
        return $this->post(route('api.login'), [], [
            'authorization' => sprintf('Basic %s', base64_encode("$username:$password")),
        ]);
    }

    // --

    public function testValidLogin()
    {
        $user = factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');

        $response->assertStatus(200);
        $response->assertJson([
            'meta' => [
                'authenticated' => true,
                'username'      => $user->username,
            ],
        ]);
        $response->assertCookie(Passport::cookie());

        $this->validateJSONAPI($response->getContent());
    }

    public function testAuthCookieWorks()
    {
        $user = factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');

        $response = $this->call('GET', route('api.session'), [], [
            Passport::cookie() => $response->headers->getCookies()[0]->getValue(),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'meta' => [
                'authenticated' => true,
                'username'      => $user->username,
            ],
        ]);
    }

    public function testAuthTokenWorks()
    {
        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );

        $user = factory(User::class)->create();

        $token = (string) $user->createToken('_test_')->accessToken;

        $response = $this->get(route('api.session'), [
            'Authorization' => sprintf('Bearer %s', $token),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'meta' => [
                'authenticated' => true,
                'username'      => $user->username,
            ],
        ]);
    }

    public function testMissingUsername()
    {
        $response = $this->post(route('api.login'));

        $response->assertStatus(401);

        $this->validateJSONAPI($response->getContent());
    }

    public function testInvalidUsername()
    {
        $response = $this->attemptLogin('user', 'passwd');

        $response->assertStatus(403);

        $this->validateJSONAPI($response->getContent());
    }

    public function testInvalidPassword()
    {
        $user = factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'passwd');

        $response->assertStatus(403);

        $this->validateJSONAPI($response->getContent());
    }
}
