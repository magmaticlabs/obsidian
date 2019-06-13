<?php

namespace Tests\Feature\Console;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class UserCreateTest extends TestCase
{
    public function testCreate()
    {
        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertNotEmpty($user);
        static::assertSame('test@example.com', $user->email);
        static::assertFalse((bool) $user->administrator);
    }

    public function testCreateAdministrator()
    {
        $cmd = $this->artisan('user:create testuser test@example.com --administrator');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertNotEmpty($user);
        static::assertSame('test@example.com', $user->email);
        static::assertTrue((bool) $user->administrator);
    }

    public function testDuplicate()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:create testuser test@example.com');
        $cmd->assertExitCode(1);

        $cmd->execute();

        static::assertSame(1, User::count());
    }

    public function testUsernameIsLowerAndTrimmed()
    {
        $cmd = $this->artisan('user:create "  TeStUsEr  " test@example.com');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertNotEmpty($user);
        static::assertSame('testuser', $user->username);
    }

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
