<?php

namespace MagmaticLabs\Obsidian\Domain\ProcessExecutor;

use Symfony\Component\Console\Output\OutputInterface;

abstract class ProcessExecutor
{
    /**
     * Execute a command
     *
     * @param string          $command
     * @param array           $args
     * @param OutputInterface $output
     * @param string|null     $cwd
     *
     * @return string
     */
    abstract public function exec(string $command, array $args, OutputInterface $output, ?string $cwd = null): string;
}
