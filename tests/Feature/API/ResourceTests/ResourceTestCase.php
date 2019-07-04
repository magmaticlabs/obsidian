<?php

namespace Tests\Feature\API\ResourceTests;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\APITestCase;

abstract class ResourceTestCase extends APITestCase
{
    /** @var User */
    protected $user;

    /** @var string */
    protected $resourceType = '__INVALID__';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->factory(User::class)->create();
        Passport::actingAs($this->user);
    }

    protected function createResource(): ?EloquentModel
    {
        return null;
    }

    protected function createRelated(string $relation, EloquentModel $resource): ?EloquentModel
    {
        return null;
    }
}
