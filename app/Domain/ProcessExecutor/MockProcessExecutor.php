<?php

namespace MagmaticLabs\Obsidian\Domain\ProcessExecutor;

use Illuminate\Contracts\Filesystem\Filesystem;

final class MockProcessExecutor extends ProcessExecutor
{
    /**
     * Actions to perform in response to commands
     *
     * @var array
     */
    private $actions;

    /**
     * Responses to commands
     *
     * @var array
     */
    private $responses;

    /**
     * Class constructor
     *
     * @param Filesystem $storage
     * @param array      $responses
     * @param array      $actions
     */
    public function __construct(Filesystem $storage, array $responses, array $actions = [])
    {
        parent::__construct($storage);

        $this->responses = $responses;
        $this->actions = $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function exec(string $command, ?string $cwd = null): string
    {
        foreach ($this->actions as $pattern => $callback) {
            if (preg_match($pattern, $command)) {
                call_user_func($callback);
            }
        }

        foreach ($this->responses as $pattern => $response) {
            if (preg_match($pattern, $command)) {
                $this->log($response);

                return $response;
            }
        }

        // No matching command found, output nothing
        return '';
    }
}
