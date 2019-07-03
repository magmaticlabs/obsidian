<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Hash;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Console\Commands\UserCreate
 */
final class UserCreateTest extends TestCase
{
    /**
     * Test that a user is created successfully.
     *
     * @test
     */
    public function create()
    {
        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertNotEmpty($user);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNotEmpty($user->password);
        $this->assertFalse((bool) $user->administrator);
    }

    /**
     * Test that creating an administrative user works.
     *
     * @test
     */
    public function create_administrator()
    {
        $cmd = $this->artisan('user:create testuser test@example.com --administrator');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertTrue((bool) $user->administrator);
    }

    /**
     * Test that creating a user with a password works correctly.
     *
     * @test
     */
    public function create_with_password()
    {
        $cmd = $this->artisan('user:create testuser test@example.com --password=passwd');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertTrue(Hash::check('passwd', $user->password));
    }

    /**
     * Test that creating a user with a duplicate username fails.
     *
     * @test
     */
    public function duplicate_username_fails()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(1);

        $cmd->execute();

        $this->assertSame(1, User::count());
    }

    /**
     * Test that creating a user with a duplicate email address fails.
     *
     * @test
     */
    public function duplicate_email_fails()
    {
        factory(User::class)->create(['email' => 'test@example.com']);

        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(1);

        $cmd->execute();

        $this->assertSame(1, User::count());
    }

    /**
     * Test that the username sanitation is working correctly.
     *
     * @test
     */
    public function username_is_lower_and_trimmed()
    {
        $cmd = $this->artisan('user:create "  TeStUsEr  " test@example.com');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertNotEmpty($user);
        $this->assertSame('testuser', $user->username);
    }

    /**
     * Test that the email address sanitation is working correctly.
     *
     * @test
     */
    public function email_case_is_kept_and_trimmed()
    {
        $cmd = $this->artisan('user:create testuser "  tEsT@eXaMpLe.com  "');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertNotEmpty($user);
        $this->assertSame('tEsT@eXaMpLe.com', $user->email);
    }
}
