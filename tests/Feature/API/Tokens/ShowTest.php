<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\APIResource\ShowTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class ShowTest extends ShowTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'tokens';

    /**
     * Test that attempting to view a token that is owned by another user 404s.
     *
     * @test
     */
    public function other_owner404()
    {
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get($this->route('show', $token->id));
        $this->validateResponse($response, 404);
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
