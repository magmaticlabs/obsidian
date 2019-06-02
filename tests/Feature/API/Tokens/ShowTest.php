<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class ShowTest extends TestCase
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var \Laravel\Passport\Token
     */
    private $token;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );

        Passport::actingAs($this->user = factory(User::class)->create());

        $this->token = $this->user->createToken('_test_')->token;
    }

    // --

    public function testShow()
    {
        /** @var PassportToken $token */
        $token = PassportToken::find($this->token->id);

        $response = $this->get(route('api.tokens.show', $token->id));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = $token->toArray();
        foreach (array_merge($token->getHidden(), ['id']) as $key) {
            unset($attributes[$key]);
        }

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
        $response = $this->get(route('api.tokens.show', 'missing'));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }

    public function testOtherOwner404()
    {
        /** @var User $owner */
        $owner = factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.show', $token->id));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
