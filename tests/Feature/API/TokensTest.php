<?php

namespace Tests\Feature\API;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestCreateEndpoints;
use Tests\Feature\API\ResourceTests\TestDeleteEndpoints;
use Tests\Feature\API\ResourceTests\TestIndexEndpoints;
use Tests\Feature\API\ResourceTests\TestShowEndpoints;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class TokensTest extends ResourceTestCase
{
    use TestIndexEndpoints;
    use TestCreateEndpoints;
    use TestShowEndpoints;
    use TestUpdateEndpoints;
    use TestDeleteEndpoints;

    protected $resourceType = 'tokens';

    /**
     * @test
     */
    public function index_only_shows_mine()
    {
        $owner = $this->factory(User::class)->create();
        $owner->createToken('_test_')->token;

        $response = $this->get(route("api.{$this->resourceType}.index"));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    /**
     * @test
     */
    public function create_has_access_token()
    {
        $attributes = $this->getValidCreateAttributes();

        $data = [
            'data' => [
                'type'       => $this->resourceType,
                'attributes' => $attributes,
            ],
        ];

        $response = $this->post(route("api.{$this->resourceType}.create"), $data);
        $this->validateResponse($response, 201);

        $attr = json_decode($response->getContent(), true)['data']['attributes'];
        $this->assertNotEmpty($attr['accessToken']);
    }

    /**
     * @test
     */
    public function show_other_owner_404()
    {
        $owner = $this->factory(User::class)->create();
        $token = $owner->createToken('_test_')->token;

        $response = $this->get(route("api.{$this->resourceType}.show", $token->id));
        $this->validateResponse($response, 404);
    }

    /**
     * @test
     */
    public function delete_other_owner_404()
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

    //--

    /**
     * {@inheritdoc}
     */
    public function validCreateAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'   => '__TESTING__',
                'scopes' => [],
            ]],
            'no-scopes' => [[
                'name' => '__TESTING__',
            ]],
            'fancy-name' => [[
                'name' => 'ThIs is a %5up3r% fancy name!',
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function invalidCreateAttributesProvider(): array
    {
        return [
            'nonstring-name' => [[
                'name' => [],
            ], 'name'],
            'nonarray-scopes' => [[
                'name'   => '__TESTING__',
                'scopes' => 'foobar',
            ], 'scopes'],
            'invalid-scopes' => [[
                'name'   => '__TESTING__',
                'scopes' => ['__INVALID__'],
            ], 'scopes'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function requiredAttributesProvider(): array
    {
        return [
            'required' => ['name'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function optionalAttributesProvider(): array
    {
        return [
            'optional' => ['scopes', []],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validUpdateAttributesProvider(): array
    {
        return [
            'basic' => [[
                'name'   => '__TESTING__',
                'scopes' => [],
            ]],
            'no-scopes' => [[
                'name' => '__TESTING__',
            ]],
            'no-name' => [[
                'scopes' => [],
            ]],
            'fancy-name' => [[
                'name' => 'ThIs is a %5up3r% fancy name!',
            ]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function invalidUpdateAttributesProvider(): array
    {
        return [
            'nonstring-name' => [[
                'name' => [],
            ], 'name'],
            'nonarray-scopes' => [[
                'scopes' => 'foobar',
            ], 'scopes'],
            'invalid-scopes' => [[
                'scopes' => ['__INVALID__'],
            ], 'scopes'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource(): EloquentModel
    {
        return PassportToken::find($this->user->createToken('__TESTING__')->token->id);
    }
}
