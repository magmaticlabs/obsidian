<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;

/**
 * @internal
 * @coversNothing
 */
final class ShowTest extends TokenTestCase
{
    use \Tests\Feature\API\APIResource\ShowTest;

    public function testOtherOwner404()
    {
        /** @var User $owner */
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get($this->getRoute('show', $token->id));
        $this->validateResponse($response, 404);
    }
}
