<?php

namespace MagmaticLabs\Obsidian\Domain\ProcessExecutor;

use Illuminate\Contracts\Filesystem\Filesystem;

abstract class ProcessExecutor
{
    /**
     * Storage system
     *
     * @var Filesystem
     */
    protected $storage;

    /**
     * Path to log file
     *
     * @var string
     */
    protected $logfile;

    /**
     * Class constructor
     *
     * @param Filesystem $storage
     */
    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
        $this->logfile = '';
    }

    /**
     * Log file accessor
     *
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logfile;
    }

    /**
     * Log file mutator
     *
     * @param string $logfile
     */
    public function setLogFile(string $logfile): void
    {
        $this->logfile = $logfile;
    }

    /**
     * Send contents to the log file
     *
     * @param string $content
     */
    public function log(string $content): void
    {
        if (!empty($this->logfile)) {
            $this->storage->append($this->logfile, $content);
        }
    }

    /**
     * Execute a command
     *
     * @param string      $command
     * @param string|null $cwd
     *
     * @return string
     */
    abstract public function exec(string $command, ?string $cwd = null): string;
}
