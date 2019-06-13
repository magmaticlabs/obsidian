<?php

namespace Tests\Feature\API\Tokens;

use Laravel\Passport\ClientRepository;
use MagmaticLabs\Obsidian\Domain\Eloquent\PassportToken;
use Tests\Feature\API\APIResource\ResourceTestCase;

abstract class TokenTestCase extends ResourceTestCase
{
    /**
     * Resource type.
     *
     * @var string
     */
    protected $type = 'tokens';

    /**
     * Model instance.
     *
     * @var PassportToken
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        (new ClientRepository())->createPersonalAccessClient(
            null,
            '__TESTING__',
            'http://localhost'
        );

        $this->model = PassportToken::find($this->user->createToken('__TESTING__')->token->id);

        $this->data = [
            'data' => [
                'type'       => 'tokens',
                'attributes' => [
                    'name'   => '__TESTING__',
                    'scopes' => [],
                ],
            ],
        ];
    }

    // --

    public function invalidDataName()
    {
        return [
            'non-string' => [[]],
        ];
    }

    public function invalidDataScopes()
    {
        return [
            'non-array' => ['foobar'],
            'invalid'   => [['__INVALID__']],
        ];
    }
}
