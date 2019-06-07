<?php

namespace Tests\Feature\API\ResourceTest;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

abstract class ResourceTest extends TestCase
{
    /**
     * Resource type
     *
     * @var string
     */
    protected $type = '__INVALID__';

    /**
     * Required attributes
     *
     * @var array
     */
    protected $required = [];

    /**
     * Optional attributes
     *
     * @var array
     */
    protected $optional = [];

    /**
     * Authenticated user
     *
     * @var User
     */
    protected $user;

    /**
     * Model instance
     *
     * @var \MagmaticLabs\Obsidian\Domain\Eloquent\Model
     */
    protected $model;

    /**
     * Data to send to API
     *
     * @var array
     */
    protected $data = [];

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = Passport::actingAs(factory(User::class)->create());
    }

    /**
     * Get a route string
     *
     * @param string $method
     * @param null   $arg
     *
     * @return string
     */
    protected function getRoute(string $method, $arg = null): string
    {
        return route(sprintf('api.%s.%s', $this->type, $method), $arg);
    }
}
