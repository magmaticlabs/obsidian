<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\ProcessExecutor;

class ProcessBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obsidian:build {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a build';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ProcessExecutor $executor, Filesystem $storage)
    {
        /* @var Build $build */
        $build = Build::find($id = $this->argument('id'));

        if (empty($build)) {
            $this->output->error("Unable to locate a build with id '$id'");

            return 1;
        }

        if ('pending' != $build->status) {
            $this->output->error('The specified build has already been processed');

            return 1;
        }

        // Process the build

        $build->preflight($executor, $storage);

        $success = $build->build($executor, $storage);

        if ($success) {
            $build->success();
        } else {
            $build->failure();
        }

        return 0;
    }
}
