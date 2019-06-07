<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;

final class ShowTest extends TokenTest
{
    use \Tests\Feature\API\ResourceTest\ShowTest;

    public function testOtherOwner404()
    {
        /** @var User $owner */
        $owner = factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get($this->getRoute('show', $token->id));
        $this->validateResponse($response, 404);
    }
}
