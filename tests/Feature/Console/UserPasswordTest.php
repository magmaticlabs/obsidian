<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Hash;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\TestCase;

class UserPasswordTest extends TestCase
{
    public function testSetPassword()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:passwd testuser --passwd=testing');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertTrue(Hash::check('testing', $user->password));
    }

    public function testAskPassword()
    {
        factory(User::class)->create(['username' => 'testuser']);

        $cmd = $this->artisan('user:passwd testuser');
        $cmd->expectsQuestion('Password', 'testing');
        $cmd->assertExitCode(0);

        $cmd->execute();

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertTrue(Hash::check('testing', $user->password));
    }

    public function testMissingUserErrors()
    {
        $cmd = $this->artisan('user:passwd testuser --passwd=testing');
        $cmd->assertExitCode(1);
    }
}
