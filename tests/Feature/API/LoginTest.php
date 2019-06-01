<?php

namespace Tests\Feature;

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

    public function testMissingUsername()
    {
        $response = $this->post(route('api.login'));

        $response->assertStatus(401);
    }

    public function testInvalidUsername()
    {
        $response = $this->attemptLogin('user', 'passwd');

        $response->assertStatus(403);
    }

    public function testInvalidPassword()
    {
        $user = factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'passwd');

        $response->assertStatus(403);
    }

    public function testValidLogin()
    {
        $user = factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');

        $response->assertStatus(200);
        $response->assertCookie(Passport::cookie());
    }

    public function testAuthCookieWorks()
    {
        $user = factory(User::class)->create();

        $response = $this->attemptLogin($user->username, 'secret');

        $response = $this->call('GET', route('api.session'), [], [
            Passport::cookie() => $response->headers->getCookies()[0]->getValue(),
        ]);

        $response->assertStatus(200);
    }
}
