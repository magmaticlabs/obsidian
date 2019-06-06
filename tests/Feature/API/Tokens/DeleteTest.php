<?php

namespace Tests\Feature\API\Tokens;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class DeleteTest extends TestCase
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
        $this->validateResponse($response, 204);
    }

    public function testDeleteActuallyWorks()
    {
        $this->delete(route('api.tokens.destroy', $this->token->id));
        $this->expectException(ModelNotFoundException::class);
        $this->token->refresh();
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.tokens.destroy', 'missing'));
        $this->validateResponse($response, 404);
    }

    public function testOtherOwner404()
    {
        /** @var User $owner */
        $owner = factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.destroy', $token->id));
        $this->validateResponse($response, 404);

        $this->token->refresh();
        $this->assertTrue($this->token->exists);
    }
}
