<?php

namespace Tests\Feature\API\Tokens;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use Tests\Feature\API\ResourceTests\ResourceTestCase;
use Tests\Feature\API\ResourceTests\TestUpdateEndpoints;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class UpdateTest extends ResourceTestCase
{
    use TestUpdateEndpoints;

    protected $resourceType = 'tokens';

    /**
     * {@inheritdoc}
     */
    public function validAttributesProvider(): array
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
    public function invalidAttributesProvider(): array
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
