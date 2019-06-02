<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );
    }

    // --

    public function testDefaultEmpty()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDataMatchesShow()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $compare = $this->get(route('api.tokens.show', $token->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountMatches()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $user->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($data['data']));

        // --

        $user->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($data['data']));
    }

    public function testOnlyShowsMine()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        /** @var User $owner */
        $owner = factory(User::class)->create();
        $owner->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }
}
