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
     *
     * @test
     */
    public function set_password()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:password testuser --password=testing');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertTrue(Hash::check('testing', $user->password));
    }

    /**
     * Test that setting a password via prompt works.
     *
     * @test
     */
    public function ask_password()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:password testuser');
        $cmd->expectsQuestion('Password', 'testing');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertTrue(Hash::check('testing', $user->password));
    }

    /**
     * Test that the password prompt responds to an empty password correctly.
     *
     * @test
     */
    public function ask_password_empty()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $this->expectException(RuntimeException::class);
        $cmd = $this->artisan('user:password testuser');
        $cmd->expectsQuestion('Password', '');
        $cmd->execute();
    }

    /**
     * Test that attempting to set the password for a user that doesn't exist fails.
     *
     * @test
     */
    public function missing_user_errors()
    {
        $cmd = $this->artisan('user:password testuser --password=testing');
        $cmd->assertExitCode(1);
    }
}
