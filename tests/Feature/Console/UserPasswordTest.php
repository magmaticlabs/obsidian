<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Hash;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use RuntimeException;
use Tests\TestCase;

/**
 * @internal
 * @covers \MagmaticLabs\Obsidian\Console\Commands\UserPassword
 */
final class UserPasswordTest extends TestCase
{
    /**
     * Test that setting a password via arguments works.
     */
    public function testSetPassword()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:password testuser --password=testing');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertTrue(Hash::check('testing', $user->password));
    }

    /**
     * Test that setting a password via prompt works.
     */
    public function testAskPassword()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:password testuser');
        $cmd->expectsQuestion('Password', 'testing');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        static::assertTrue(Hash::check('testing', $user->password));
    }

    /**
     * Test that the password prompt responds to an empty password correctly.
     */
    public function testAskPasswordEmpty()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $this->expectException(RuntimeException::class);
        $cmd = $this->artisan('user:password testuser');
        $cmd->expectsQuestion('Password', '');
        $cmd->execute();
    }

    /**
     * Test that attempting to set the password for a user that doesn't exist fails.
     */
    public function testMissingUserErrors()
    {
        $cmd = $this->artisan('user:password testuser --password=testing');
        $cmd->assertExitCode(1);
    }
}
