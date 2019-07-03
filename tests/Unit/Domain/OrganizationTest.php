<?php

namespace Tests\Unit\Domain;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class OrganizationTest extends TestCase
{
    /**
     * Organization instance.
     *
     * @var Organization
     */
    private $organization;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = factory(Organization::class)->create();
    }

    // --

    /**
     * @test
     */
    public function add_remove_members()
    {
        $this->assertSame(0, $this->organization->members()->count());

        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(0, $this->organization->owners()->count());
        $this->assertSame($user->getKey(), $this->organization->members()->first()->getKey());

        $this->organization->removeMember($user);

        $this->assertSame(0, $this->organization->members()->count());
    }

    /**
     * @test
     */
    public function promote_demote()
    {
        $user = factory(User::class)->create();
        $this->organization->addMember($user);

        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(0, $this->organization->owners()->count());

        $this->organization->promoteMember($user);

        // Owners are counted in members
        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(1, $this->organization->owners()->count());

        $this->organization->demoteMember($user);

        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(0, $this->organization->owners()->count());
    }

    /**
     * @test
     */
    public function redundant_operations()
    {
        $user = factory(User::class)->create();

        $this->organization->removeMember($user);

        $this->organization->addMember($user);
        $this->organization->addMember($user);

        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(0, $this->organization->owners()->count());

        $this->organization->promoteMember($user);
        $this->organization->promoteMember($user);

        // Owners are counted in members
        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(1, $this->organization->owners()->count());

        $this->organization->demoteMember($user);
        $this->organization->demoteMember($user);

        $this->assertSame(1, $this->organization->members()->count());
        $this->assertSame(0, $this->organization->owners()->count());
    }

    /**
     * @test
     */
    public function invalid_promote()
    {
        $user = factory(User::class)->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->organization->promoteMember($user);
    }

    /**
     * @test
     */
    public function invalid_demote()
    {
        $user = factory(User::class)->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->organization->demoteMember($user);
    }
}
