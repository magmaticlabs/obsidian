<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;

class BuildKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obsidian:buildkey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new SSH keypair to use during builds';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(RSA $rsa)
    {
        $storage = Storage::disk('local');
        $keypath = 'obsidian-build.key';

        if ($storage->exists($keypath)) {
            $this->warn('Build key already exists!');

            $confirm = $this->ask('Replace Key [y/n]', 'n');
            if ('y' !== $confirm) {
                return 0;
            }
        }

        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
        $keypair = $rsa->createKey();

        $storage->put($keypath, $keypair['privatekey'], 'private');
        $storage->put("{$keypath}.pub", $keypair['publickey']);

        return 0;
    }
}
