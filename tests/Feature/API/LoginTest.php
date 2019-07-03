<?php

namespace Tests\Feature\API;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\AuthController
 */
final class LoginTest extends APITestCase
{
    // --

    /**
     * @test
     */
    public function no_auth_reports_correctly()
    {
        $response = $this->get(route('api.auth.session'));
        $this->validateResponse($response, 200);

        $response->assertJson([
            'meta' => [
                'authenticated' => false,
                'username'      => null,
                'authtype'      => null,
            ],
        ]);
    }

    /**
     * @test
     */
    public function valid_login()
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

    /**
     * @test
     */
    public function auth_cookie_works()
    {
        $user = $this->factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');
        $this->validateResponse($response, 200);

        $response = $this->call('GET', route('api.auth.session'), [], [
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

    /**
     * @test
     */
    public function auth_token_works()
    {
        (new ClientRepository())->createPersonalAccessClient(
            null,
            '__TESTING__',
            'http://localhost'
        );

        $user = $this->factory(User::class)->create();

        $token = (string) $user->createToken('_test_')->accessToken;

        $response = $this->get(route('api.auth.session'), [
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

    /**
     * @test
     */
    public function missing_username()
    {
        $response = $this->post(route('api.auth.login'));
        $this->validateResponse($response, 401);
    }

    /**
     * @test
     */
    public function invalid_username()
    {
        $response = $this->attemptLogin('user', 'passwd');
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     */
    public function invalid_password()
    {
        $user = $this->factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'passwd');
        $this->validateResponse($response, 403);
    }

    private function attemptLogin($username, $password)
    {
        return $this->post(route('api.auth.login'), [], [
            'authorization' => sprintf('Basic %s', base64_encode("{$username}:{$password}")),
        ]);
    }
}
