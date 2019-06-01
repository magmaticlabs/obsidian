<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {username} {email} {--administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $username = strtolower(trim($this->argument('username')));
        $email = trim($this->argument('email'));

        // Check if the user already exists
        if (User::getquery()->where('username', $username)->count() > 0) {
            $this->output->error('The specified user already exists!');

            return 1;
        }

        // Create user
        User::create([
            'username'      => $username,
            'email'         => $email,
            'password'      => Hash::make(Str::random(32)), // Random password
            'administrator' => $this->hasOption('administrator'),
        ]);

        return 0;
    }
}
