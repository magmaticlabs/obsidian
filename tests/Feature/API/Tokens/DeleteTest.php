<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;

/**
 * @internal
 * @coversNothing
 */
final class DeleteTest extends TokenTestCase
{
    use \Tests\Feature\API\APIResource\DeleteTest;

    public function testDeleteOtherOwner404()
    {
        /** @var User $owner */
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->delete($this->getRoute('destroy', $token->id));
        $this->validateResponse($response, 404);

        $this->model->refresh();
        static::assertTrue($this->model->exists);
    }
}
