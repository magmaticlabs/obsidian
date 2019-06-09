<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;

final class DeleteTest extends TokenTest
{
    use \Tests\Feature\API\ResourceTest\DeleteTest;

    public function testDeleteOtherOwner404()
    {
        /** @var User $owner */
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->delete($this->getRoute('destroy', $token->id));
        $this->validateResponse($response, 404);

        $this->model->refresh();
        $this->assertTrue($this->model->exists);
    }
}
