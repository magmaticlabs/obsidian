<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null, '__TESTING__', 'http://localhost'
        );
    }

    public function testUpdate()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
                'attributes' => [
                    'name'   => '__TEST_UPDATE__',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = array_diff($token->getHidden(), $token->toArray());
        $attributes['name'] = '__TEST_UPDATE__';

        $response->assertJson([
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNoAttributesNoOp()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type'       => 'tokens',
                'id'         => $token->id,
            ],
        ]);

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

    public function testMissingTypeFails()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'id' => $token->id,
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testWrongTypeFails()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type' => 'foobar',
                'id'   => $token->id,
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testMissingIdFails()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type' => 'tokens',
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testWrongIdFails()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $token = $user->createToken('_test_')->token;

        $response = $this->patch(route('api.tokens.update', $token->id), [
            'data' => [
                'type' => 'tokens',
                'id'   => 'foobar',
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testNonExist()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $response = $this->patch(route('api.tokens.update', 'missing'), [
            'data' => [
                'type' => 'tokens',
                'id'   => 'foobar',
            ],
        ]);

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
