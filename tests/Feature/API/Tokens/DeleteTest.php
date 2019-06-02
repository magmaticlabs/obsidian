<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );
    }

    public function testDelete()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->delete(route('api.tokens.destroy', $token->id));

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    public function testDeleteActuallyWorks()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $this->assertEquals(1, $user->tokens()->count());

        $this->delete(route('api.tokens.destroy', $token->id));

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function testNonExist()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->delete(route('api.tokens.destroy', 'missing'));

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

        $response = $this->get(route('api.tokens.destroy', $token->id));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }

    // --
}
