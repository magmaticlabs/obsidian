<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use MagmaticLabs\Obsidian\Domain\BuildProcessing\BuildProcessor;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\ProcessExecutor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;

class ProcessBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obsidian:build {id} {--force}';

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

        if ($this->option('force')) {
            $build->update([
                'status'          => 'pending',
                'start_time'      => null,
                'completion_time' => null,
            ]);
        }

        if ('pending' != $build->status) {
            $this->output->error('The specified build has already been processed');

            return 1;
        }

        $output = $this->getOutput()->isVerbose() ? new ConsoleOutput() : new NullOutput();

        $processor = new BuildProcessor($executor, $storage, $output);

        try {
            $processor->preflight($build);

            $processor->process($build);

            $processor->success($build);
        } catch (\Exception $ex) {
            $processor->failure($build);
            $this->output->error('Build Failed: ' . $ex->getMessage());
        }

        $processor->cleanup($build);

        return 0;
    }
}
