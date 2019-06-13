<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\APIResource\DeleteTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class DeleteTest extends DeleteTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'tokens';

    /**
     * Test that attempting to delete a token owned by another user results in a 404.
     */
    public function testOtherOwner404()
    {
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->delete($this->route('destroy', $token->id));
        $this->validateResponse($response, 404);

        $this->model->refresh();
        static::assertTrue($this->model->exists);
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
