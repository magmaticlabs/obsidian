<?php

namespace Tests\Feature\API\Repositories;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    /**
     * Authenticated user
     *
     * @var User
     */
    private $user;

    /**
     * Organization to create the repository in
     *
     * @var Organization
     */
    private $organization;

    /**
     * Test repository
     *
     * @var Repository
     */
    private $repository;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = Passport::actingAs(factory(User::class)->create());

        $this->organization = factory(Organization::class)->create();
        $this->organization->addMember($this->user);

        $this->repository = factory(Repository::class)->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    // --

    public function testDefaultEmpty()
    {
        Repository::query()->delete(); // Undo setup creation

        $response = $this->get(route('api.repositories.index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDataMatchesShow()
    {
        $response = $this->get(route('api.repositories.index'));
        $this->validateResponse($response, 200);

        $compare = $this->get(route('api.repositories.show', $this->repository->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountMatches()
    {
        $response = $this->get(route('api.repositories.index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($data['data']));

        // --

        $count = 5;

        factory(Repository::class)->times($count)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get(route('api.repositories.index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count + 1, count($data['data']));
    }
}
