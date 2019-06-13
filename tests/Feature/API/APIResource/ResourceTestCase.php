<?php

namespace Tests\Feature\API\APIResource;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

abstract class ResourceTestCase extends TestCase
{
    /**
     * Resource type.
     *
     * @var string
     */
    protected $type = '__INVALID__';

    /**
     * Resource model class.
     *
     * @var string
     */
    protected $class = '__INVALID__';

    /**
     * Authenticated user.
     *
     * @var User
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Passport::actingAs(factory(User::class)->create());

        (new ClientRepository())->createPersonalAccessClient(
            null,
            '__TESTING__',
            'http://localhost'
        );
    }

    /**
     * Get a route string.
     *
     * @param string $method
     * @param null   $arg
     *
     * @return string
     */
    protected function route(string $method, $arg = null): string
    {
        return route(sprintf('api.%s.%s', $this->type, $method), $arg);
    }

    /**
     * Create model instances.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    protected function createModel(int $times = 1)
    {
        return $this->factory($this->class)->times($times)->create();
    }

    /**
     * Specific parameters to pass into the factory.
     *
     * @return array
     */
    protected function factoryParameters(): array
    {
        return [];
    }
}
