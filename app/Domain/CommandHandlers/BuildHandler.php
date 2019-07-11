<?php

namespace MagmaticLabs\Obsidian\Domain\CommandHandlers;

use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\CommandHandler;
use MagmaticLabs\Obsidian\Jobs\ProcessBuild;

final class BuildHandler extends CommandHandler
{
    /**
     * Create a new build.
     *
     * @param Command $command
     *
     * @command build.create
     */
    public function handleCreate(Command $command)
    {
        /** @var Build $build */
        $build = Build::create($command->getData());

        ProcessBuild::dispatch($build)->onConnection('database');
    }
}
