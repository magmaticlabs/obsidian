<?php

namespace Tests\Feature\API\Tokens;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class IndexTest extends ResourceTestCase
{
    use TestIndexEndpoints;

    protected $resourceType = 'tokens';

    /**
     * @test
     */
    public function only_shows_mine()
    {
        $owner = $this->factory(User::class)->create();
        $owner->createToken('_test_')->token;

        $response = $this->get(route("api.{$this->resourceType}.index"));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        return PassportToken::find($this->user->createToken('__TESTING__')->token->id);
    }
}
