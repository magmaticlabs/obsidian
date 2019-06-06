<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class ShowTest extends TestCase
{
    /**
     * Organization
     *
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
    }

    // --

    public function testShow()
    {
        $response = $this->get(route('api.organizations.show', $this->organization->id));
        $this->validateResponse($response, 200);

        $attributes = $this->organization->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.organizations.show', 'missing'));
        $this->validateResponse($response, 404);
    }
}
