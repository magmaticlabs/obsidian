<?php

namespace Tests\Feature\API\Organizations\Repositories;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class RelationshipTest extends TestCase
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
        $response = $this->get(route('api.organizations.repositories', $this->organization->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testCorrectCount()
    {
        $count = 5;
        factory(Repository::class)->times($count)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get(route('api.organizations.repositories', $this->organization->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testCorrectData()
    {
        $repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get(route('api.organizations.repositories', $this->organization->id));
        $this->validateResponse($response, 200);

        $attributes = $repository->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'repositories',
                    'id'         => $repository->id,
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.organizations.repositories', 'missing'));
        $this->validateResponse($response, 404);
    }
}
