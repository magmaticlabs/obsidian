<?php

namespace MagmaticLabs\Obsidian\Domain\CommandHandlers;

use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\CommandHandler;

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
        Build::create($command->getData());
    }
}
