<?php

namespace MagmaticLabs\Obsidian\Domain\CommandHandlers;

use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\Support\Command;
use MagmaticLabs\Obsidian\Domain\Support\CommandHandler;

final class RepositoryHandler extends CommandHandler
{
    /**
     * Create a new repository.
     *
     * @param Command $command
     *
     * @command repository.create
     */
    public function handleCreate(Command $command)
    {
        Repository::create($command->getData());
    }

    /**
     * Update an existing repository.
     *
     * @param Command $command
     *
     * @command repository.update
     */
    public function handleUpdate(Command $command)
    {
        $repository = Repository::find($command->getObjectId());
        $repository->update($command->getData('attributes'));
    }

    /**
     * Destroy an existing repository.
     *
     * @param Command $command
     *
     * @command repository.destroy
     */
    public function handleDestroy(Command $command)
    {
        Repository::find($command->getObjectId())->delete();
    }
}
