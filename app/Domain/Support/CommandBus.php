<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

final class CommandBus
{
    /**
     * Registered callbacks for commands
     *
     * @var array
     */
    private $registrations;

    /**
     * Register a new command callback
     *
     * @param string   $command
     * @param callable $callback
     *
     * @return self
     */
    public function register(string $command, callable $callback, int $priority = 10): self
    {
        if (!isset($this->registrations[$command][$priority])) {
            $this->registrations[$priority] = [];
        }

        if (!isset($this->registrations[$command])) {
            $this->registrations[$priority][$command] = [];
        }

        if (!isset($this->registrations[$command][$priority])) {
            $this->registrations[$priority][$command] = [];
        }

        $this->registrations[$priority][$command][] = $callback;

        return $this;
    }

    /**
     * Dispatch the command to all registered callbacks
     *
     * @param Command $command
     */
    public function dispatch(Command $command): void
    {
        $type = $command->getType();

        ksort($this->registrations);

        foreach ($this->registrations as $priority => $commands) {
            foreach ($commands as $cmd => $callbacks) {
                if ('*' !== $cmd && $cmd !== $type) {
                    continue;
                }

                foreach ($callbacks as $callback) {
                    call_user_func($callback, $command);
                }
            }
        }
    }
}
