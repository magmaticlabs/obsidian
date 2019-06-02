<?php

namespace Tests\Feature\API\organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class ShowTest extends TestCase
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

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $attributes = $this->organization->toArray();
        foreach (array_merge($this->organization->getHidden(), ['id']) as $key) {
            unset($attributes[$key]);
        }

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

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
