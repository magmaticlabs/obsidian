<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function testDefaultEmpty()
    {
        Organization::query()->delete(); // Undo setup creation

        $response = $this->get(route('api.organizations.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDataMatchesShow()
    {
        $response = $this->get(route('api.organizations.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $compare = $this->get(route('api.organizations.show', $this->organization->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountMatches()
    {
        $response = $this->get(route('api.organizations.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($data['data']));

        // --

        factory(Organization::class)->create();

        $response = $this->get(route('api.organizations.index'));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(2, count($data['data']));
    }
}
