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
     */
    public function testCreate()
    {
        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertNotEmpty($user);
        static::assertSame('test@example.com', $user->email);
        static::assertNotEmpty($user->password);
        static::assertFalse((bool) $user->administrator);
    }

    /**
     * Test that creating an administrative user works.
     */
    public function testCreateAdministrator()
    {
        $cmd = $this->artisan('user:create testuser test@example.com --administrator');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertTrue((bool) $user->administrator);
    }

    /**
     * Test that creating a user with a password works correctly.
     */
    public function testCreateWithPassword()
    {
        $cmd = $this->artisan('user:create testuser test@example.com --password=passwd');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertTrue(Hash::check('passwd', $user->password));
    }

    /**
     * Test that creating a user with a duplicate username fails.
     */
    public function testDuplicateUsernameFails()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(1);

        $cmd->execute();

        static::assertSame(1, User::count());
    }

    /**
     * Test that creating a user with a duplicate email address fails.
     */
    public function testDuplicateEmailFails()
    {
        factory(User::class)->create(['email' => 'test@example.com']);

        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(1);

        $cmd->execute();

        static::assertSame(1, User::count());
    }

    /**
     * Test that the username sanitation is working correctly.
     */
    public function testUsernameIsLowerAndTrimmed()
    {
        $cmd = $this->artisan('user:create "  TeStUsEr  " test@example.com');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertNotEmpty($user);
        static::assertSame('testuser', $user->username);
    }

    /**
     * Test that the email address sanitation is working correctly.
     */
    public function testEmailCaseIsKeptAndTrimmed()
    {
        $cmd = $this->artisan('user:create testuser "  tEsT@eXaMpLe.com  "');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertNotEmpty($user);
        static::assertSame('tEsT@eXaMpLe.com', $user->email);
    }
}
