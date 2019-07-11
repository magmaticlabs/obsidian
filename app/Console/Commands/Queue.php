<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;

class Queue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obsidian:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the job queue';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->writeln('Waiting for jobs...');

        return $this->call('queue:work', [
            '--timeout' => '-1',
        ]);
    }
}
