<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class ShowTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );
    }

    public function testShow()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.show', $token->id));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = array_diff($token->getHidden(), $token->toArray());

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.tokens.show', 'missing'));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }

    public function testOtherOwner404()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        /** @var User $owner */
        $owner = factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.show', $token->id));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
