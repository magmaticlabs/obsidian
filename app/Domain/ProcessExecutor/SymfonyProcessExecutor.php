<?php

namespace MagmaticLabs\Obsidian\Domain\ProcessExecutor;

use Symfony\Component\Process\Process;

final class SymfonyProcessExecutor extends ProcessExecutor
{
    /**
     * {@inheritdoc}
     */
    public function exec(string $command, ?string $cwd = null): string
    {
        $process = new Process(["$command 2>&1"], $cwd, null, null, 60);
        $exitcode = $process->run();

        $output = $process->getOutput();

        $this->log(trim($output));

        if (0 != $exitcode) {
            throw new \RuntimeException('Error during execution');
        }

        return $output;
    }
}
