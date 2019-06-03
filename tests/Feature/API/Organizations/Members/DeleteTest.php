<?php

namespace Tests\Feature\API\Organizations\Members;

use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class DeleteTest extends TestCase
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

    public function testDestroy()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDestroyNonMember()
    {
        $user = factory(User::class)->create();

        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->validateJSONAPI($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testDestroyMissing()
    {
        $response = $this->delete(route('api.organizations.members.destroy', $this->organization->id), [
            'data' => [
                [
                    'type' => 'users',
                    'id'   => 'foobar',
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->validateJSONAPI($response->getContent());

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/0/id']],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->delete(route('api.organizations.members.destroy', 'missing', []));

        $response->assertStatus(404);
        $this->validateJSONAPI($response->getContent());
    }
}
