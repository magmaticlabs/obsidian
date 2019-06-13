<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use Tests\Feature\API\APIResource\UpdateTestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Http\Controllers\API\TokenController
 */
final class UpdateTest extends UpdateTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $type = 'tokens';

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
