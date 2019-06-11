<?php

namespace MagmaticLabs\Obsidian\Domain\ProcessExecutor;

use Symfony\Component\Console\Output\OutputInterface;

final class MockProcessExecutor extends ProcessExecutor
{
    /**
     * Responses to commands
     *
     * @var array
     */
    private $responses;

    /**
     * Actions to perform in response to commands
     *
     * @var array
     */
    private $actions;

    /**
     * History of commands
     *
     * @var array
     */
    private $commands;

    /**
     * Class constructor
     *
     * @param array $responses
     * @param array $actions
     */
    public function __construct(array $responses, array $actions = [])
    {
        $this->responses = $responses;
        $this->actions = $actions;
        $this->commands = [];
    }

    /**
     * {@inheritdoc}
     */
    public function exec(string $command, array $args, OutputInterface $output, ?string $cwd = null): string
    {
        $this->commands[] = $command;

        foreach ($this->actions as $pattern => $callback) {
            if (preg_match($pattern, $command)) {
                call_user_func($callback);
                break;
            }
        }

        foreach ($this->responses as $pattern => $response) {
            if (preg_match($pattern, $command)) {
                $output->write($response);

                return $response;
            }
        }

        // No matching command found, output nothing
        return '';
    }

    /**
     * Reset the command log
     */
    public function reset()
    {
        $this->commands = [];
    }

    /**
     * Commands accessor
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
