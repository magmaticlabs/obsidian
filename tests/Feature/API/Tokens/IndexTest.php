<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\APIResource\IndexTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class IndexTest extends IndexTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'tokens';

    /**
     * Test that only the authenticated user's tokens are displayed.
     *
     * @test
     */
    public function only_shows_mine()
    {
        $owner = $this->factory(User::class)->create();
        $owner->createToken('_test_')->token;

        $response = $this->get($this->route('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel(int $times = 1)
    {
        if (1 === $times) {
            return PassportToken::find($this->user->createToken('__TESTING__')->token->id);
        }

        $IDs = [];
        for ($i = 0; $i < $times; ++$i) {
            $IDs[] = $this->user->createToken('__TESTING__')->token->id;
        }

        return PassportToken::query()->whereIn('id', $IDs)->get();
    }
}
