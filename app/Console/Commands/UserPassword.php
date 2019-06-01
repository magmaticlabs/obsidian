<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

class UserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:passwd {username} {--passwd=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the password for a user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $username = strtolower(trim($this->argument('username')));

        // Find the user
        $user = User::query()->where('username', $username)->first();
        if (empty($user)) {
            $this->output->error('Unknown user!');

            return 1;
        }

        // Grab password
        if ($this->hasOption('passwd') && !empty($this->option('passwd'))) {
            $password = $this->option('passwd');
        } else {
            $password = $this->output->askHidden('Password', function ($password) {
                if (empty($password)) {
                    throw new \RuntimeException('Password cannot be empty.');
                }

                return $password;
            });
        }

        // Save the password
        $user->update(['password' => Hash::make($password)]);

        return 0;
    }
}
