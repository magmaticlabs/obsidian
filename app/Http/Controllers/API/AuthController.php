<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Symfony\Component\HttpFoundation\Cookie;

final class AuthController extends Controller
{
    /**
     * Display information about the current session
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function session(Request $request): Response
    {
        $loggedin = auth()->check();

        return new Response([
            'meta'  => new \stdClass(),
            'data'  => [
                'authenticated' => $loggedin,
                'username'      => $loggedin ? $this->getUser()->username : null,
            ],
            'links' => [
                '_self' => route('api.session'),
            ],
        ], 200);
    }

    /**
     * Process a login attempt
     *
     * @param \Illuminate\Http\Request         $request
     * @param \Illuminate\Encryption\Encrypter $encrypter
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, Encrypter $encrypter): Response
    {
        $username = trim($request->getUser());
        if (empty($username)) {
            abort(401);
        }

        /** @var User $user */
        $user = User::query()->where('username', $username)->first();
        if (empty($user)) {
            abort(403);
        }

        if (!Hash::check($request->getPassword(), $user->password)) {
            abort(403);
        }

        $response = new Response([
            'meta'  => new \stdClass(),
            'data'  => 'ok',
            'links' => [
                'root'  => route('api.root'),
                '_self' => route('api.login'),
            ],
        ], 200);

        $response->withCookie($this->makeCookie($user, $encrypter));

        return $response;
    }

    /**
     * Create an authentication cookie
     *
     * @param \MagmaticLabs\Obsidian\Domain\Eloquent\User $user
     * @param \Illuminate\Encryption\Encrypter            $encrypter
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    private function makeCookie(User $user, Encrypter $encrypter): Cookie
    {
        /*
         * We should be able to use the Laravel\Passport\ApiTokenCookieFactory
         * class to create the token cookie, but it doesn't properly encrypt the
         * cookie value, and passport expects to decrypt the cookie value when
         * it checks it.
         *
         * This is mostly ripped from the factory class.
         */

        $config = Config::get('session');
        $expiration = Carbon::now()->addMinutes($config['lifetime']);

        $token = JWT::encode([
            'sub'    => $user->getKey(),
            'csrf'   => '', // Empty CSRF so we don't need to pass it in as a header
            'expiry' => $expiration->getTimestamp(),
        ], $encrypter->getKey());

        return new Cookie(
            Passport::cookie(),
            $encrypter->encrypt($token),
            $expiration,
            $config['path'],
            $config['domain'],
            $config['secure'],
            true,
            false,
            $config['same_site'] ?? null
        );
    }
}
