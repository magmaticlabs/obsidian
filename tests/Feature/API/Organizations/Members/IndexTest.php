<?php

namespace Tests\Feature\API\Organizations\Members;

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
        $response = $this->get(route('api.organizations.members.index', $this->organization->id));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testCorrectCount()
    {
        $members = 5;
        foreach (factory(User::class)->times($members)->create() as $user) {
            $this->organization->addMember($user);
        }

        $response = $this->get(route('api.organizations.members.index', $this->organization->id));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($members, count($data['data']));
    }

    public function testCorrectData()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $response = $this->get(route('api.organizations.members.index', $this->organization->id));

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $response->assertJsonFragment([
            'data' => [
                [
                    'type'       => 'users',
                    'id'         => $user->id,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.organizations.members.index', 'missing'));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
