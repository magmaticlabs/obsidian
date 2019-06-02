<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function testDefaultEmpty()
    {
        $this->user->tokens()->delete(); // Clean up setup step

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDataMatchesShow()
    {
        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $compare = $this->get(route('api.tokens.show', $this->token->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountMatches()
    {
        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($data['data']));

        // --

        $this->user->createToken('_test_')->token;

        $response = $this->get(route('api.tokens.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($data['data']));
    }

    public function testOnlyShowsMine()
    {
        $this->user->tokens()->delete(); // Clean up setup step

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
