<?php

namespace MagmaticLabs\Obsidian\Domain\ProcessExecutor;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final class SymfonyProcessExecutor extends ProcessExecutor
{
    /**
     * {@inheritdoc}
     */
    public function exec(string $command, array $args, OutputInterface $output, ?string $cwd = null): string
    {
        $input = Arr::flatten([$command, $args]);
        $return = '';

        $process = new Process($input, $cwd);
        $process->setTimeout(null);
        $process->mustRun(function ($type, $buffer) use ($output, &$return) {
            $output->write($buffer);
            $return .= $buffer;
        })->wait();

        return $return;
    }
}
