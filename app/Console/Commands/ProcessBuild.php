<?php

namespace MagmaticLabs\Obsidian\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use MagmaticLabs\Obsidian\Domain\BuildProcessing\BuildProcessor;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\ProcessExecutor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

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
        /** @var Build $build */
        $build = Build::find($id = $this->argument('id'));

        if (empty($build)) {
            $this->output->error("Unable to locate a build with id '{$id}'");

            return 1;
        }

        if ($this->option('force')) {
            $build->update([
                'status'          => 'pending',
                'start_time'      => null,
                'completion_time' => null,
            ]);
        }

        if ('pending' !== $build->status) {
            $this->output->error('The specified build has already been processed');

            return 1;
        }

        $output = new class() extends Output {
            /**
             * @var OutputInterface
             */
            private $consoleOutput;

            /**
             * @var OutputInterface
             */
            private $fileOutput;

            public function setConsoleOutput(OutputInterface $output)
            {
                $this->consoleOutput = $output;
            }

            public function setFileOutput(OutputInterface $output)
            {
                $this->fileOutput = $output;
            }

            /**
             * {@inheritdoc}
             */
            protected function doWrite($message, $newline)
            {
                if (null !== $this->consoleOutput) {
                    if ($newline) {
                        $this->consoleOutput->writeln($message);
                    } else {
                        $this->consoleOutput->write($message);
                    }
                }

                if (null !== $this->fileOutput) {
                    if ($newline) {
                        $this->fileOutput->writeln($message);
                    } else {
                        $this->fileOutput->write($message);
                    }
                }
            }
        };

        $logDir = 'builds/logs';
        $logPath = storage_path(sprintf('app/%s/%s.log', $logDir, $build->id));

        if (!$storage->exists($logDir)) {
            $storage->makeDirectory($logDir);
        }

        $output->setConsoleOutput($this->getOutput()->isVerbose() ? new ConsoleOutput() : new NullOutput());
        $output->setFileOutput(new StreamOutput(fopen($logPath, 'w', false)));

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
