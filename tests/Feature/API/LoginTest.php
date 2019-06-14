<?php

namespace Tests\Feature\API;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\AuthController
 */
final class LoginTest extends TestCase
{
    // --

    public function testNoAuthReportsCorrectly()
    {
        $response = $this->get(route('api.session'));
        $this->validateResponse($response, 200);

        $response->assertJson([
            'meta' => [
                'authenticated' => false,
                'username'      => null,
                'authtype'      => null,
            ],
        ]);
    }

    public function testValidLogin()
    {
        $user = $this->factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');
        $this->validateResponse($response, 200);

        $response->assertJson([
            'meta' => [
                'authenticated' => true,
                'username'      => $user->username,
            ],
        ]);

        $response->assertCookie(Passport::cookie());
    }

    public function testAuthCookieWorks()
    {
        $user = $this->factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');
        $this->validateResponse($response, 200);

        $response = $this->call('GET', route('api.session'), [], [
            Passport::cookie() => $response->headers->getCookies()[0]->getValue(),
        ]);

        $response->assertJson([
            'meta' => [
                'authenticated' => true,
                'username'      => $user->username,
                'authtype'      => 'cookie',
            ],
        ]);
    }

    public function testAuthTokenWorks()
    {
        (new ClientRepository())->createPersonalAccessClient(
            null,
            '__TESTING__',
            'http://localhost'
        );

        $user = $this->factory(User::class)->create();

        $token = (string) $user->createToken('_test_')->accessToken;

        $response = $this->get(route('api.session'), [
            'Authorization' => sprintf('Bearer %s', $token),
        ]);

        $this->validateResponse($response, 200);

        $response->assertJson([
            'meta' => [
                'authenticated' => true,
                'username'      => $user->username,
                'authtype'      => 'token',
            ],
        ]);
    }

    public function testMissingUsername()
    {
        $response = $this->post(route('api.login'));
        $this->validateResponse($response, 401);
    }

    public function testInvalidUsername()
    {
        $response = $this->attemptLogin('user', 'passwd');
        $this->validateResponse($response, 403);
    }

    public function testInvalidPassword()
    {
        $user = $this->factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'passwd');
        $this->validateResponse($response, 403);
    }

    private function attemptLogin($username, $password)
    {
        return $this->post(route('api.login'), [], [
            'authorization' => sprintf('Basic %s', base64_encode("{$username}:{$password}")),
        ]);
    }
}
