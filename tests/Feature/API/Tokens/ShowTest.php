<?php

namespace Tests\Feature\API\Tokens;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class ShowTest extends ResourceTestCase
{
    use TestShowEndpoints;

    protected $resourceType = 'tokens';

    /**
     * @test
     */
    public function other_owner_404()
    {
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get(route("api.{$this->resourceType}.show", $token->id));
        $this->validateResponse($response, 404);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        return PassportToken::find($this->user->createToken('__TESTING__')->token->id);
    }
}
