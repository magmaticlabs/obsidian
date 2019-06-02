<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class DeleteTest extends TestCase
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

    public function testDelete()
    {
        $response = $this->delete(route('api.tokens.destroy', $this->token->id));

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    public function testDeleteActuallyWorks()
    {
        $this->assertEquals(1, $this->user->tokens()->count());

        $this->delete(route('api.tokens.destroy', $this->token->id));

        $this->assertEquals(0, $this->user->tokens()->count());
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.tokens.destroy', 'missing'));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }

    public function testOtherOwner404()
    {
        /** @var User $owner */
        $owner = factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.destroy', $token->id));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());

        $this->assertEquals(1, $this->user->tokens()->count());
    }
}
