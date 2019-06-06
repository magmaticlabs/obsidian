<?php

namespace Tests\Feature\API\Organizations\Owners;

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
        $response = $this->get(route('api.organizations.owners.index', $this->organization->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }

    public function testCorrectCount()
    {
        $members = 5;
        $owners = 2;
        foreach (factory(User::class)->times($members)->create() as $user) {
            $this->organization->addMember($user);
            if ($this->organization->owners()->count() < $owners) {
                $this->organization->promoteMember($user);
            }
        }

        $response = $this->get(route('api.organizations.owners.index', $this->organization->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($owners, count($data['data']));
    }

    public function testCorrectData()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);
        $this->organization->promoteMember($user);

        $response = $this->get(route('api.organizations.owners.index', $this->organization->id));
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get(route('api.organizations.owners.index', 'missing'));
        $this->validateResponse($response, 404);
    }
}
