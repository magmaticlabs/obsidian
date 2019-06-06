<?php

namespace Tests\Feature\API\Organizations;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class IndexTest extends TestCase
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
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDataMatchesShow()
    {
        $response = $this->get(route('api.organizations.index'));
        $this->validateResponse($response, 200);

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
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($data['data']));

        // --

        $count = 5;
        factory(Organization::class)->times($count)->create();

        $response = $this->get(route('api.organizations.index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count + 1, count($data['data']));
    }
}
