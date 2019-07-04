<?php

namespace Tests\Feature\API\Tokens;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class DeleteTest extends ResourceTestCase
{
    use TestDeleteEndpoints;

    protected $resourceType = 'tokens';

    /**
     * @test
     */
    public function other_owner_404()
    {
        /** @var User $owner */
        $owner = $this->factory(User::class)->create();

        /** @var \Laravel\Passport\Token $resource */
        $resource = $owner->createToken('_test_')->token;

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));
        $this->validateResponse($response, 404);

        $resource->refresh();
        $this->assertTrue($resource->exists);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        return PassportToken::find($this->user->createToken('__TESTING__')->token->id);
    }
}
