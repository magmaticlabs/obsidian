<?php

namespace Tests\Unit\Domain;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    /**
     * Organization instance
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

        $this->organization = factory(Organization::class)->create();
    }

    // --

    public function testAddRemoveMembers()
    {
        $this->assertEquals(0, $this->organization->members()->count());

        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(0, $this->organization->owners()->count());
        $this->assertEquals($user->getKey(), $this->organization->members()->first()->getKey());

        $this->organization->removeMember($user);

        $this->assertEquals(0, $this->organization->members()->count());
    }

    public function testPromoteDemote()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(0, $this->organization->owners()->count());

        $this->organization->promoteMember($user);

        // Owners are counted in members
        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(1, $this->organization->owners()->count());

        $this->organization->demoteMember($user);

        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(0, $this->organization->owners()->count());
    }

    public function testRedundantOperations()
    {
        $user = factory(User::class)->create();

        $this->organization->removeMember($user);

        $this->organization->addMember($user);
        $this->organization->addMember($user);

        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(0, $this->organization->owners()->count());

        $this->organization->promoteMember($user);
        $this->organization->promoteMember($user);

        // Owners are counted in members
        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(1, $this->organization->owners()->count());

        $this->organization->demoteMember($user);
        $this->organization->demoteMember($user);

        $this->assertEquals(1, $this->organization->members()->count());
        $this->assertEquals(0, $this->organization->owners()->count());
    }

    public function testInvalidPromote()
    {
        $user = factory(User::class)->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->organization->promoteMember($user);
    }
    
    public function testInvalidDemote()
    {
        $user = factory(User::class)->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->organization->demoteMember($user);
    }
}
